<?php

namespace App\Http\Controllers;

use App\Models\Pterodactyl\Egg;
use App\Models\Pterodactyl\Location;
use App\Models\Pterodactyl\Nest;
use App\Models\Pterodactyl\Node;
use App\Models\Product;
use App\Models\Server;
use App\Models\User;
use App\Notifications\ServerCreationError;
use App\Settings\DiscordSettings;
use Carbon\Carbon;
use App\Settings\UserSettings;
use App\Settings\ServerSettings;
use App\Settings\PterodactylSettings;
use App\Classes\PterodactylClient;
use App\Enums\BillingPriority;
use App\Settings\GeneralSettings;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Client\Response;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request as FacadesRequest;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Support\Facades\Cache;
use App\Jobs\HandlePostServerCreationJob;
use App\Jobs\ReconcileServerCreationJob;

class ServerController extends Controller
{
    private const CREATE_PERMISSION = 'user.server.create';
    private const UPGRADE_PERMISSION = 'user.server.upgrade';
    private const BILLING_PERIODS = [
        'hourly' => 3600,
        'daily' => 86400,
        'weekly' => 604800,
        'monthly' => 2592000,
        'quarterly' => 7776000,
        'half-annually' => 15552000,
        'annually' => 31104000
    ];

    private PterodactylClient $pterodactyl;
    private PterodactylSettings $pteroSettings;
    private GeneralSettings $generalSettings;
    private ServerSettings $serverSettings;
    private UserSettings $userSettings;
    private DiscordSettings $discordSettings;

    public function __construct(
        PterodactylSettings $pteroSettings,
        GeneralSettings $generalSettings,
        ServerSettings $serverSettings,
        UserSettings $userSettings,
        DiscordSettings $discordSettings
    ) {
        $this->pteroSettings = $pteroSettings;
        $this->pterodactyl = new PterodactylClient($pteroSettings);
        $this->generalSettings = $generalSettings;
        $this->serverSettings = $serverSettings;
        $this->userSettings = $userSettings;
        $this->discordSettings = $discordSettings;
    }

    public function index(): \Illuminate\View\View
    {
        $servers = $this->getServersWithInfo();

        return view('servers.index')->with([
            'servers' => $servers,
            'credits_display_name' => $this->generalSettings->credits_display_name,
            'pterodactyl_url' => $this->pteroSettings->panel_url,
            'phpmyadmin_url' => $this->generalSettings->phpmyadmin_url
        ]);
    }

    public function create(): \Illuminate\View\View|RedirectResponse
    {
        $this->checkPermission(self::CREATE_PERMISSION);

        $validationResult = $this->validateServerCreation(app(Request::class));
        if ($validationResult) {
            return $validationResult;
        }

        return view('servers.create')->with([
            'productCount' => Product::where('disabled', false)->count(),
            'nodeCount' => Node::whereHas('products', function (Builder $builder) {
                $builder->where('disabled', false);
            })->count(),
            'nests' => Nest::whereHas('eggs', function (Builder $builder) {
                $builder->whereHas('products', function (Builder $builder) {
                    $builder->where('disabled', false);
                });
            })->get(),
            'locations' => Location::all(),
            'eggs' => Egg::whereHas('products', function (Builder $builder) {
                $builder->where('disabled', false);
            })->get(),
            'user' => Auth::user(),
            'server_creation_enabled' => $this->serverSettings->creation_enabled,
            'credits_display_name' => $this->generalSettings->credits_display_name,
            'location_description_enabled' => $this->serverSettings->location_description_enabled,
            'store_enabled' => $this->generalSettings->store_enabled
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $lock = Cache::lock('server_create_lock_' . Auth::id(), 10);
        if (!$lock->get()) {
            return redirect()->route('servers.index')
                ->with('error', __('Please wait a moment before creating another server.'));
        }

        $lockAcquired = true;
        try {
            $validationResult = $this->validateServerCreation($request);
            if ($validationResult) return $validationResult;

            $request->validate([
                'name' => 'required|max:191',
                'location' => 'required|exists:locations,id',
                'egg' => 'required|exists:eggs,id',
                'product' => 'required|exists:products,id',
                'egg_variables' => 'nullable|string',
                'billing_priority' => ['nullable', new Enum(BillingPriority::class)],
            ]);

            // NOTE: Potential cross-system consistency issue:
            // The server (local DB record and remote Pterodactyl resource) is created here
            // BEFORE we atomically decrement the user's credits. If the credit decrement
            // fails (e.g. insufficient credits), we attempt to delete the created local
            // server record below, but the remote Pterodactyl server may still exist and
            // become orphaned. Deleting the local model does not guarantee the remote
            // resource is removed unless `createServer`/`delete` also handle the remote API.
            //
            // Recommendations:
            //  - Prefer an atomic reserve/decrement of credits before calling `createServer()`;
            //    if `createServer()` later fails, refund the credits (increment back) reliably.
            //  - Alternatively, create a local 'pending' record, attempt remote creation, and
            //    only commit (finalize) the credits after the remote server is confirmed.
            //  - Ensure cleanup includes removal of the remote Pterodactyl server (call the
            //    Pterodactyl API explicitly) and add retry/alerting for failures.
            //
            // The existing cache lock prevents concurrent creates per user but does not
            // solve cross-system consistency with the panel — add compensation logic.

            // Reserve credits first (atomic decrement). This prevents races where another
            // operation spends the credits between validation and charging."
            // FIX APPLIED BELOW (& IN ReconcileServerCreationJob.php)
            $product = Product::findOrFail($request->input('product'));
            $price = $product->price;
            $userId = $request->user()->id;

            $decremented = User::where('id', $userId)
                ->where('credits', '>=', $price)
                ->decrement('credits', $price);

            if ($decremented === 0) {
                return redirect()->route('servers.index')
                    ->with('error', __('Not enough :credits to create server', ['credits' => $this->generalSettings->credits_display_name]));
            }

            // attempt provisioning
            $server = $this->createServer($request);

            if (!$server) {
                // Allocation or node failure - refund credits and return
                try {
                    User::where('id', $userId)->increment('credits', $price);
                } catch (\Throwable $e) {
                    Log::error('Failed to refund credits after server creation allocation failure', ['user_id' => $userId, 'price' => $price, 'error' => $e->getMessage()]);
                }

                return redirect()->route('servers.index')
                    ->with('error', __('Server creation failed'));
            }

            // If the Pterodactyl provisioning succeeded within createServer, set pterodactyl_id
            if ($server->pterodactyl_id) {
                Cache::forget('user_credits_left:' . $userId);
                HandlePostServerCreationJob::dispatch($userId, $server->id);

                try {
                    $lock->release();
                    $lockAcquired = false;
                } catch (\Throwable $e) {
                    Log::debug('Failed to release server creation lock: ' . $e->getMessage());
                }

                return redirect()->route('servers.index')
                    ->with('success', __('Server created'));
            }

            // Provisioning reported failure. Try to verify if the server actually exists on Pterodactyl
            try {
                $pteroAttrs = $this->pterodactyl->findServerByExternalId($server->id);
            } catch (\Throwable $e) {
                Log::error('Failed to verify Pterodactyl server after provisioning failure', ['server_id' => $server->id, 'error' => $e->getMessage()]);
                // Enqueue reconciliation job and inform user; do not refund here as job will handle it
                ReconcileServerCreationJob::dispatch($server->id, $price);

                return redirect()->route('servers.index')
                    ->with('error', __('Server creation failed and will be reconciled shortly'));
            }

            if ($pteroAttrs) {
                // Remote server exists — update local record and proceed as success
                $server->update([
                    'pterodactyl_id' => $pteroAttrs['id'],
                    'identifier' => $pteroAttrs['identifier'] ?? $server->identifier,
                ]);

                Cache::forget('user_credits_left:' . $userId);
                HandlePostServerCreationJob::dispatch($userId, $server->id);

                try {
                    $lock->release();
                    $lockAcquired = false;
                } catch (\Throwable $e) {
                    Log::debug('Failed to release server creation lock: ' . $e->getMessage());
                }

                return redirect()->route('servers.index')
                    ->with('success', __('Server created'));
            }

            // Remote server not found — attempt refund and delete local record
            try {
                User::where('id', $userId)->increment('credits', $price);

                // Clear cache and delete local server (deleting hook will ignore 404s on pterodactyl)
                Cache::forget('user_credits_left:' . $userId);
                $server->delete();

                return redirect()->route('servers.index')
                    ->with('error', __('Server creation failed'));
            } catch (\Throwable $e) {
                Log::error('Failed to refund after failed server creation and no remote server found', ['server_id' => $server->id, 'user_id' => $userId, 'error' => $e->getMessage()]);

                // Dispatch reconcile job to retry refund and cleanup later
                ReconcileServerCreationJob::dispatch($server->id, $price);

                return redirect()->route('servers.index')
                    ->with('error', __('Server creation failed and will be reconciled shortly'));
            }        } finally {
            if ($lockAcquired) {
                try {
                    $lock->release();
                } catch (\Throwable $e) {
                    Log::debug('Failed to release server creation lock: ' . $e->getMessage());
                }
            }
        }
    }

    private function validateServerCreation(Request $request): ?RedirectResponse
    {
        $user = Auth::user();

        if ($user->servers()->count() >= $user->server_limit) {
            return redirect()->route('servers.index')
                ->with('error', __('Server limit reached!'));
        }

        if ($request->has('product')) {
            $product = Product::findOrFail($request->input('product'));

            $validationResult = $this->validateProductRequirements($product, $request);
            if ($validationResult !== true) {
                return redirect()->route('servers.index')
                    ->with('error', $validationResult);
            }
        }

        if (!$this->validateUserRequirements()) {
            return redirect()->route('profile.index')
                ->with('error', __('User requirements not met'));
        }

        return null;
    }

    private function validateProductRequirements(Product $product, Request $request): string|bool
    {
        $location = $request->input('location');
        $availableNode = $this->findAvailableNode($location, $product);

        if (!$availableNode) {
            return __("The chosen location doesn't have the required memory or disk left to allocate this product.");
        }

        $user = Auth::user();
        $productCount = $user->servers()->where("product_id", $product->id)->count();
        if ($productCount >= $product->serverlimit && $product->serverlimit != 0) {
            return __('You can not create any more Servers with this product!');
        }

        // Determine effective minimum credits: if minimum_credits is not set, use product price.
        if (is_null($product->minimum_credits)) {
            $minCredits = $product->price;
        } else {
            $minCredits = $product->minimum_credits;
        }

        if ($user->credits < $minCredits) {
            return 'You do not have the required amount of ' . $this->generalSettings->credits_display_name . ' to use this product!';
        }

        return true;
    }

    private function validateUserRequirements(): bool
    {
        $user = Auth::user();

        if ($this->userSettings->force_email_verification && !$user->hasVerifiedEmail()) {
            return false;
        }

        if (!$this->serverSettings->creation_enabled && $user->cannot("admin.servers.bypass_creation_enabled")) {
            return false;
        }

        if ($this->userSettings->force_discord_verification && !$user->discordUser) {
            return false;
        }

        return true;
    }

    private function getServersWithInfo(): \Illuminate\Database\Eloquent\Collection
    {
        $servers = Auth::user()->servers;

        foreach ($servers as $server) {
            $serverInfo = $this->pterodactyl->getServerAttributes($server->pterodactyl_id);
            if (!$serverInfo) continue;

            $this->updateServerInfo($server, $serverInfo);
        }

        return $servers;
    }

    private function updateServerInfo(Server $server, array $serverInfo): void
    {
        try {
            if (!isset($serverInfo['relationships'])) {
                return;
            }

            $relationships = $serverInfo['relationships'];
            $locationAttrs = $relationships['location']['attributes'] ?? [];
            $eggAttrs = $relationships['egg']['attributes'] ?? [];
            $nestAttrs = $relationships['nest']['attributes'] ?? [];
            $nodeAttrs = $relationships['node']['attributes'] ?? [];

            $server->location = $locationAttrs['long'] ?? $locationAttrs['short'] ?? null;
            $server->egg = $eggAttrs['name'] ?? null;
            $server->nest = $nestAttrs['name'] ?? null;
            $server->node = $nodeAttrs['name'] ?? null;

            if (isset($serverInfo['name']) && $server->name !== $serverInfo['name']) {
                $server->name = $serverInfo['name'];
                $server->save();
            }

            if ($server->product_id) {
                $server->setRelation('product', Product::find($server->product_id));
            }
        } catch (Exception $e) {
            Log::error('Failed to update server info', [
                'server_id' => $server->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    private function createServer(Request $request): ?Server
    {
        $product = Product::findOrFail($request->input('product'));
        $egg = $product->eggs()->findOrFail($request->input('egg'));
        $node = $this->findAvailableNode($request->input('location'), $product);

        if (!$node) return null;

        $server = $request->user()->servers()->create([
            'name' => $request->input('name'),
            'product_id' => $product->id,
            'last_billed' => Carbon::now(),
            'billing_priority' => $request->input('billing_priority', $product->default_billing_priority),
        ]);

        $allocationId = $this->pterodactyl->getFreeAllocationId($node);
        if (!$allocationId) {
            Log::error('No AllocationID found.', [
                'server_id' => $server->id,
                'node_id' => $node->id,
            ]);
            // Cannot proceed without allocation - remove local record
            $server->delete();
            return null;
        }

        $response = $this->pterodactyl->createServer($server, $egg, $allocationId, $request->input('egg_variables'));
        if ($response->failed()) {
            // Creation failed from the API side. Do NOT delete the local record here.
            // The controller will attempt to verify if the server actually exists on
            // Pterodactyl (by searching for our external_id) and handle refunds/cleanup.
            Log::error('Failed to create server on Pterodactyl', [
                'server_id' => $server->id,
                'status' => $response->status(),
                'error' => $response->json()
            ]);

            return $server; // Return the local server so the caller can reconcile
        }

        $serverAttributes = $response->json()['attributes'];
        $server->update([
            'pterodactyl_id' => $serverAttributes['id'],
            'identifier' => $serverAttributes['identifier']
        ]);

        return $server;
    }

    private function handlePostCreation(User $user, Server $server): void
    {
        try {
            if ($this->discordSettings->role_for_active_clients &&
                $user->discordUser &&
                $user->servers->count() >= 1
            ) {
                $user->discordUser->addOrRemoveRole(
                    'add',
                    $this->discordSettings->role_id_for_active_clients
                );
            }
        } catch (Exception $e) {
            Log::debug('Discord role update failed: ' . $e->getMessage());
        }
    }

    public function destroy(Server $server): RedirectResponse
    {
        if ($server->user_id !== Auth::id()) {
            return back()->with('error', __('This is not your Server!'));
        }

        try {
            $serverInfo = $this->pterodactyl->getServerAttributes($server->pterodactyl_id);

            if (!$serverInfo) {
                throw new Exception("Server not found on Pterodactyl panel");
            }

            $this->handleServerDeletion($server);

            return redirect()->route('servers.index')
                ->with('success', __('Server removed'));
        } catch (Exception $e) {
            Log::error('Server deletion failed', [
                'server_id' => $server->id,
                'pterodactyl_id' => $server->pterodactyl_id,
                'error' => $e->getMessage()
            ]);

            return redirect()->route('servers.index')
                ->with('error', __('Server removal failed: ') . $e->getMessage());
        }
    }

    private function handleServerDeletion(Server $server): void
    {
        if ($this->discordSettings->role_for_active_clients) {
            $user = User::findOrFail($server->user_id);
            if ($user->discordUser && $user->servers->count() <= 1) {
                $user->discordUser->addOrRemoveRole(
                    'remove',
                    $this->discordSettings->role_id_for_active_clients
                );
            }
        }

        $server->delete();
        Cache::forget('user_credits_left:' . $server->user_id);
    }

    public function cancel(Server $server): RedirectResponse
    {
        if ($server->user_id !== Auth::id()) {
            return back()->with('error', __('This is not your Server!'));
        }

        try {
            $server->update(['canceled' => now()]);
            return redirect()->route('servers.index')
                ->with('success', __('Server canceled'));
        } catch (Exception $e) {
            return redirect()->route('servers.index')
                ->with('error', __('Server cancellation failed: ') . $e->getMessage());
        }
    }

    public function show(Server $server): \Illuminate\View\View
    {
        if ($server->user_id !== Auth::id()) {
            return back()->with('error', __('This is not your Server!'));
        }

        $serverAttributes = $this->pterodactyl->getServerAttributes($server->pterodactyl_id);
        $upgradeOptions = $this->getUpgradeOptions($server, $serverAttributes);
        return view('servers.settings')->with([
            'server' => $server,
            'serverAttributes' => $serverAttributes,
            'products' => $upgradeOptions,
            'server_enable_upgrade' => $this->serverSettings->enable_upgrade,
            'credits_display_name' => $this->generalSettings->credits_display_name,
            'location_description_enabled' => $this->serverSettings->location_description_enabled,
        ]);
    }

    private function getUpgradeOptions(Server $server, array $serverInfo): \Illuminate\Database\Eloquent\Collection
    {
        $currentProduct = Product::find($server->product_id);
        $nodeId = $serverInfo['relationships']['node']['attributes']['id'];
        $pteroNode = $this->pterodactyl->getNode($nodeId);
        $currentEgg = $serverInfo['egg'];

        //$currentProductEggs = $currentProduct->eggs->pluck('id')->toArray();

        return Product::orderBy('price', 'asc')
            ->with('nodes')->with('eggs')
            ->whereHas('nodes', function (Builder $builder) use ($nodeId) {
                $builder->where('id', $nodeId);
            })
            ->whereHas('eggs', function (Builder $builder) use ($currentEgg) {
                $builder->where('id', $currentEgg);
            })
            ->get()
            ->map(function ($product) use ($currentProduct, $pteroNode) {
                $product->eggs = $product->eggs->pluck('name')->toArray();

                $memoryDiff = $product->memory - $currentProduct->memory;
                $diskDiff = $product->disk - $currentProduct->disk;

                $maxMemory = ($pteroNode['memory'] * ($pteroNode['memory_overallocate'] + 100) / 100);
                $maxDisk = ($pteroNode['disk'] * ($pteroNode['disk_overallocate'] + 100) / 100);

                if ($memoryDiff > $maxMemory - $pteroNode['allocated_resources']['memory'] ||
                    $diskDiff > $maxDisk - $pteroNode['allocated_resources']['disk']) {
                    $product->doesNotFit = true;
                }

                return $product;
            });
    }

    public function upgrade(Server $server, Request $request): RedirectResponse
    {
        $this->checkPermission(self::UPGRADE_PERMISSION);

        if ($server->user_id !== Auth::id()) {
            return redirect()->route('servers.index')
                ->with('error', __('This is not your Server!'));
        }

        if (!$request->has('product_upgrade')) {
            return redirect()->route('servers.show', ['server' => $server->id])
                ->with('error', __('No product selected for upgrade'));
        }

        $user = Auth::user();
        $oldProduct = Product::find($server->product->id);
        $newProduct = Product::find($request->product_upgrade);

        if (!$newProduct) {
            return redirect()->route('servers.show', ['server' => $server->id])
                ->with('error', __('Selected product not found'));
        }

        if (!$this->validateUpgrade($server, $oldProduct, $newProduct)) {
            return redirect()->route('servers.show', ['server' => $server->id])
                ->with('error', __('Insufficient resources or credits for upgrade'));
        }

        try {
            $this->processUpgrade($server, $oldProduct, $newProduct, $user);
            return redirect()->route('servers.show', ['server' => $server->id])
                ->with('success', __('Server Successfully Upgraded'));
        } catch (Exception $e) {
            Log::error('Server upgrade failed', [
                'server_id' => $server->id,
                'old_product' => $oldProduct->id,
                'new_product' => $newProduct->id,
                'error' => $e->getMessage()
            ]);

            return redirect()->route('servers.show', ['server' => $server->id])
                ->with('error', __('Upgrade failed: ') . $e->getMessage());
        }
    }

    public function updateBillingPriority(Server $server, Request $request): RedirectResponse
    {
        $data = $request->validate([
            'billing_priority' => ['required', new Enum(BillingPriority::class)],
        ]);

        if ($server->user_id !== Auth::id()) {
            return redirect()->route('servers.index')
                ->with('error', __('This is not your Server!'));
        }

        $server->update($data);

        return redirect()->route('servers.show', ['server' => $server->id])
            ->with('success', __('Billing priority updated successfully'));
    }

    private function validateUpgrade(Server $server, Product $oldProduct, Product $newProduct): bool
    {
        $user = Auth::user();
        if (!$server->product) {
            return false;
        }

        $serverInfo = $this->pterodactyl->getServerAttributes($server->pterodactyl_id);
        if (!$serverInfo) {
            return false;
        }

        $nodeId = $serverInfo['relationships']['node']['attributes']['id'];
        $node = Node::findOrFail($nodeId);

        // Check node resources
        $requireMemory = $newProduct->memory - $oldProduct->memory;
        $requireDisk = $newProduct->disk - $oldProduct->disk;
        if (!$this->pterodactyl->checkNodeResources($node, $requireMemory, $requireDisk)) {
            return false;
        }

        // Check if user has enough credits after refund
        $refundAmount = $this->calculateRefund($server, $oldProduct);
        if ($user->credits < ($newProduct->price - $refundAmount)) {
            return false;
        }

        return true;
    }

    private function processUpgrade(Server $server, Product $oldProduct, Product $newProduct, User $user): void
    {
        $server->allocation = $this->pterodactyl->getServerAttributes($server->pterodactyl_id)['allocation'];

        $response = $this->pterodactyl->updateServer($server, $newProduct);
        if ($response->failed()) {
            throw new Exception("Failed to update server on Pterodactyl");
        }

        $restartResponse = $this->pterodactyl->powerAction($server, 'restart');
        if ($restartResponse->failed()) {
            throw new Exception('Could not restart the server: ' . $restartResponse->json()['errors'][0]['detail']);
        }

        // Calculate refund
        $refund = $this->calculateRefund($server, $oldProduct);
        if ($refund > 0) {
            $user->increment('credits', $refund);
        }

        // Update server
        unset($server->allocation);
        $server->update([
            'product_id' => $newProduct->id,
            'updated_at' => now(),
            'last_billed' => now(),
            'canceled' => null,
        ]);

        // Charge for new product
        $user->decrement('credits', $newProduct->price);
    }

    private function calculateRefund(Server $server, Product $oldProduct): float
    {
        $billingPeriod = $oldProduct->billing_period;
        $billingPeriodSeconds = self::BILLING_PERIODS[$billingPeriod];
        $timeUsed = now()->diffInSeconds($server->last_billed, true);

        return $oldProduct->price - ($oldProduct->price * ($timeUsed / $billingPeriodSeconds));
    }

    private function findAvailableNode(string $locationId, Product $product): ?Node
    {
        $nodes = Node::where('location_id', $locationId)
            ->whereHas('products', fn($q) => $q->where('product_id', $product->id))
            ->get();

        $availableNodes = $nodes->reject(function ($node) use ($product) {
            return !$this->pterodactyl->checkNodeResources($node, $product->memory, $product->disk);
        });

        return $availableNodes->isEmpty() ? null : $availableNodes->first();
    }

    public function validateDeploymentVariables(Request $request)
    {
        $variables = $request->input('variables');

        $errors = [];

        foreach ($variables as $variable) {
            $rules = $variable['rules'];
            $envVariable = $variable['env_variable'];
            $filledValue = $variable['filled_value'];

            $validator = Validator::make(
                [$envVariable => $filledValue],
                [$envVariable => $rules]
            );

            $validator->setAttributeNames([
                $envVariable => $variable['name'],
            ]);

            if ($validator->fails()) {
                $errors[$envVariable] = $validator->errors()->get($envVariable);
            }
        }

        if (!empty($errors)) {
            return response()->json([
                'errors' => $errors
            ], 422);
        }

        return response()->json([
            'success' => true,
            'variables' => $variables,
        ]);
    }
}

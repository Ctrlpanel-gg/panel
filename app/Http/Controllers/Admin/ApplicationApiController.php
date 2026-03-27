<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApplicationApi;
use App\Settings\LocaleSettings;
use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ApplicationApiController extends Controller
{
    const READ_PERMISSION = "admin.api.read";
    const WRITE_PERMISSION = "admin.api.write";
    /**
     * Display a listing of the resource.
     *
     * @return Application|Factory|View|Response
     */
    public function index(LocaleSettings $locale_settings)
    {
        $this->checkAnyPermission([self::READ_PERMISSION,self::WRITE_PERMISSION]);

        return view('admin.api.index', [
            'locale_datatables' => $locale_settings->datatables
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Application|Factory|View|Response
     */
    public function create()
    {
        $this->checkPermission(self::WRITE_PERMISSION);

        return view('admin.api.create', [
            'availableAbilities' => ApplicationApi::abilityOptions(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  Request  $request
     * @return RedirectResponse
     */
    public function store(Request $request)
    {
        $this->checkPermission(self::WRITE_PERMISSION);

        $request->validate([
            'memo' => 'nullable|string|max:60',
            'abilities' => 'required|array|min:1',
            'abilities.*' => 'required|string|in:' . implode(',', ApplicationApi::availableAbilities()),
            'expires_at' => 'nullable|date|after:now',
        ]);

        [, $plainTextToken] = ApplicationApi::issue(
            null,
            $request->input('memo'),
            $request->input('abilities', []),
            $request->filled('expires_at') ? Carbon::parse($request->input('expires_at')) : null,
        );

        return redirect()
            ->route('admin.api.index')
            ->with('success', __('API token created!'))
            ->with('plain_text_api_token', $plainTextToken);
    }

    /**
     * Display the specified resource.
     *
     * @param  ApplicationApi  $applicationApi
     * @return Response
     */
    public function show(ApplicationApi $applicationApi)
    {
        abort(404);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  ApplicationApi  $applicationApi
     * @return Application|Factory|View|Response
     */
    public function edit(ApplicationApi $applicationApi)
    {
        $this->checkPermission(self::WRITE_PERMISSION);
        return view('admin.api.edit', [
            'applicationApi' => $applicationApi,
            'availableAbilities' => ApplicationApi::abilityOptions(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  Request  $request
     * @param  ApplicationApi  $applicationApi
     * @return RedirectResponse
     */
    public function update(Request $request, ApplicationApi $applicationApi)
    {
        $this->checkPermission(self::WRITE_PERMISSION);

        $request->validate([
            'memo' => 'nullable|string|max:60',
            'abilities' => 'required|array|min:1',
            'abilities.*' => 'required|string|in:' . implode(',', ApplicationApi::availableAbilities()),
            'expires_at' => 'nullable|date|after:now',
            'revoked' => 'nullable|boolean',
            'rotate_token' => 'nullable|boolean',
        ]);

        $applicationApi->update([
            'memo' => $request->input('memo'),
            'abilities' => $request->input('abilities', []),
            'expires_at' => $request->filled('expires_at') ? Carbon::parse($request->input('expires_at')) : null,
            'revoked_at' => $request->boolean('revoked') ? now() : null,
        ]);

        $plainTextToken = null;
        if ($request->boolean('rotate_token')) {
            $plainTextToken = $applicationApi->rotate($applicationApi->expires_at);
        }

        $redirect = redirect()->route('admin.api.index')->with('success', __('API token updated!'));

        if ($plainTextToken) {
            $redirect->with('plain_text_api_token', $plainTextToken);
        }

        return $redirect;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  ApplicationApi  $applicationApi
     * @return RedirectResponse
     */
    public function destroy(ApplicationApi $applicationApi)
    {
        $this->checkPermission(self::WRITE_PERMISSION);

        $applicationApi->delete();

        return redirect()->back()->with('success', __('api key has been removed!'));
    }

    /**
     * @param  Request  $request
     * @return JsonResponse|mixed
     *
     * @throws Exception
     */
    public function dataTable(Request $request)
    {
        $this->checkAnyPermission([self::READ_PERMISSION, self::WRITE_PERMISSION]);

        $query = ApplicationApi::query();

        return datatables($query)
            ->addColumn('actions', function (ApplicationApi $apiKey) {
                if (! auth()->user()?->can(self::WRITE_PERMISSION)) {
                    return '';
                }

                return '
                <a data-content="'.__('Edit').'" data-toggle="popover" data-trigger="hover" data-placement="top"  href="'.route('admin.api.edit', $apiKey->id).'" class="btn btn-sm btn-info mr-1"><i class="fas fa-pen"></i></a>
                <form class="d-inline" onsubmit="return submitResult();" method="post" action="'.route('admin.api.destroy', $apiKey->id).'">
                            '.csrf_field().'
                            '.method_field('DELETE').'
                           <button data-content="'.__('Delete').'" data-toggle="popover" data-trigger="hover" data-placement="top" class="btn btn-sm btn-danger mr-1"><i class="fas fa-trash"></i></button>
                       </form>
                ';
            })
            ->editColumn('token', function (ApplicationApi $apiKey) {
                return "<code>{$apiKey->display_token_identifier}</code>";
            })
            ->editColumn('last_used', function (ApplicationApi $apiKey) {
                return $apiKey->last_used ? $apiKey->last_used->diffForHumans() : '';
            })
            ->addColumn('abilities', function (ApplicationApi $apiKey) {
                return e(implode(', ', $apiKey->abilities ?? []));
            })
            ->editColumn('expires_at', function (ApplicationApi $apiKey) {
                return $apiKey->expires_at ? $apiKey->expires_at->diffForHumans() : __('Never');
            })
            ->addColumn('status', function (ApplicationApi $apiKey) {
                $color = match ($apiKey->status_label) {
                    'Revoked' => 'danger',
                    'Expired' => 'warning',
                    default => 'success',
                };

                return '<span class="badge badge-' . $color . '">' . e(__($apiKey->status_label)) . '</span>';
            })
            ->rawColumns(['actions', 'token', 'status'])
            ->make();
    }
}

<?php

namespace App\Http\Controllers\Auth\TwoFactor;

use App\Http\Controllers\Controller;
use App\Services\TwoFactor\TwoFactorService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class TwoFactorExtensionController extends Controller
{
    protected $twoFactorService;

    public function __construct(TwoFactorService $twoFactorService)
    {
        $this->twoFactorService = $twoFactorService;
    }

    /**
     * Show the 2FA challenge view for a specific method.
     */
    public function showChallenge(Request $request, string $method)
    {
        $extension = $this->twoFactorService->getExtension($method);

        if (!$extension || !$this->twoFactorService->isMethodEnabled($request->user(), $method)) {
            return redirect()->route('login.2fa.challenge');
        }

        return view($extension->getChallengeView());
    }

    /**
     * Verify the 2FA challenge.
     */
    public function verify(Request $request, string $method)
    {
        $extension = $this->twoFactorService->getExtension($method);

        if (!$extension || !$this->twoFactorService->isMethodEnabled($request->user(), $method)) {
            abort(403);
        }

        if ($extension->verify($request)) {
            $this->twoFactorService->markVerified($request, $request->user());
            return redirect()->intended(route('home'));
        }

        throw ValidationException::withMessages([
            'code' => [__('The provided two-factor authentication code was invalid.')],
        ]);
    }

    /**
     * Start the setup process for a 2FA method.
     */
    public function setup(Request $request, string $method)
    {
        $extension = $this->twoFactorService->getExtension($method);

        if (!$extension || !$extension->isAvailable($request->user())) {
            abort(403);
        }

        return $extension->setup($request);
    }

    /**
     * Enable a 2FA method.
     */
    public function enable(Request $request, string $method)
    {
        $extension = $this->twoFactorService->getExtension($method);

        if (!$extension || !$extension->isAvailable($request->user())) {
            abort(403);
        }

        return $extension->enable($request);
    }

    /**
     * Disable a 2FA method.
     */
    public function disable(Request $request, string $method)
    {
        $extension = $this->twoFactorService->getExtension($method);

        if (!$extension || !$this->twoFactorService->isMethodEnabled($request->user(), $method)) {
            abort(403);
        }

        return $extension->disable($request);
    }

    /**
     * Method-specific custom actions (like showing recovery codes).
     */
    public function action(Request $request, string $method, string $action)
    {
        $extension = $this->twoFactorService->getExtension($method);

        if (!$extension || !method_exists($extension, $action)) {
            abort(404);
        }

        return $extension->{$action}($request);
    }
}

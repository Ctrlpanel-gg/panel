<?php

namespace App\Extensions\TwoFactor\Dummy;

use App\Classes\TwoFactorExtension;
use App\Models\User;
use App\Models\UserTwoFactorMethod;
use Illuminate\Http\Request;

class DummyExtension extends TwoFactorExtension
{
    public function getName(): string
    {
        return 'dummy';
    }

    public function getLabel(): string
    {
        return __('Dummy 2FA (Test)');
    }

    public function getIcon(): string
    {
        return 'fas fa-flask';
    }

    public function getDescription(): string
    {
        return __('A temporary method for testing modular 2FA.');
    }

    public function isAvailable(User $user): bool
    {
        return app()->environment('local');
    }

    public function getSettingsView(): string
    {
        return 'twofactor_dummy::profile_card';
    }

    public function getChallengeView(): string
    {
        return 'twofactor_dummy::auth.two-factor.dummy-challenge';
    }

    public function verify(Request $request): bool
    {
        return $request->input('code') === '123456';
    }

    public function setup(Request $request)
    {
        return response()->json(['message' => 'Dummy setup ready. Use code 123456 to enable.']);
    }

    public function enable(Request $request)
    {
        if ($request->input('code') !== '123456') {
             return response()->json(['errors' => ['code' => ['Use 123456']]], 422);
        }

        UserTwoFactorMethod::updateOrCreate(
            ['user_id' => $request->user()->id, 'method' => 'dummy'],
            ['is_enabled' => true]
        );

        return response()->json(['message' => 'Dummy 2FA enabled!']);
    }

    /**
     * NOTE: This is a dummy method for development only.
     * In a production-ready extension, this method SHOULD require
     * password or 2FA code verification before disabling.
     */
    public function disable(Request $request)
    {
        $request->user()->twoFactorMethods()->where('method', 'dummy')->delete();
        return response()->json(['message' => 'Dummy 2FA disabled.']);
    }
}

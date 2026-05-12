<?php

namespace App\Classes;

use App\Models\User;
use Illuminate\Http\Request;

abstract class TwoFactorExtension extends AbstractExtension
{
    /**
     * Get the unique identifier for this 2FA method.
     */
    abstract public function getName(): string;

    /**
     * Get the display label for this 2FA method.
     */
    abstract public function getLabel(): string;

    /**
     * Get the FontAwesome icon for this 2FA method.
     */
    abstract public function getIcon(): string;

    /**
     * Get a short description for this 2FA method.
     */
    abstract public function getDescription(): string;

    /**
     * Check if this 2FA method is available for the given user.
     */
    public function isAvailable(User $user): bool
    {
        return true;
    }

    /**
     * Get the view name for the settings card in the profile.
     */
    abstract public function getSettingsView(): string;

    /**
     * Get the view name for the login challenge.
     */
    abstract public function getChallengeView(): string;

    /**
     * Verify the 2FA challenge.
     */
    abstract public function verify(Request $request): bool;

    /**
     * Handle the setup logic (AJAX).
     */
    abstract public function setup(Request $request);

    /**
     * Handle the enable logic (AJAX).
     */
    abstract public function enable(Request $request);

    /**
     * Handle the disable logic (AJAX).
     */
    abstract public function disable(Request $request);

    /**
     * Get the list of allowed actions that can be called via the action route.
     *
     * @return array
     */
    public function getAllowedActions(): array
    {
        return [];
    }

    /**
     * Get the rate limit for a specific action.
     * 
     * @param string $action (setup, enable, disable, verify, action)
     * @return array ['attempts' => int, 'minutes' => int]
     */
    public function getRateLimit(string $action): array
    {
        $defaults = [
            'setup'   => ['attempts' => 3, 'minutes' => 1],
            'enable'  => ['attempts' => 3, 'minutes' => 1],
            'disable' => ['attempts' => 3, 'minutes' => 1],
            'verify'  => ['attempts' => 5, 'minutes' => 1],
            'action'  => ['attempts' => 3, 'minutes' => 5], // Default 3 per 5 mins for sensitive actions
        ];

        return $this->getConfig()['rate_limits'][$action] ?? $defaults[$action];
    }

    /**
     * Get method-specific routes configuration if any.
     */
    public static function getConfig(): array
    {
        return [];
    }
}

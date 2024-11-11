<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Auth;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
    /**
     * Check if user has permissions
     * Abort 403 if the user doesn't have the required permission
     *
     * @param string $permission
     * @return void
     */
    public function checkPermission(string $permission)
    {
        /** @var User $user */
        $user = Auth::user();

        if (!$user->can($permission)) {
            abort(403, __('User does not have the right permissions.'));
        }
    }

    public function checkAnyPermission(iterable $permission)
    {
        /** @var User $user */
        $user = Auth::user();

        if (!$user->canAny($permission)) {
            abort(403, __('User does not have the right permissions.'));
        }
    }

    /**
     * Check if user has permissions
     *
     * @param string $permission
     * @return bool
     */
    public function can(string $permission): bool
    {
        /** @var User $user */
        $user = Auth::user();

        return $user->can($permission);
    }
    public function canAny(iterable $permission): bool
    {
        /** @var User $user */
        $user = Auth::user();

        return $user->canAny($permission);
    }
}

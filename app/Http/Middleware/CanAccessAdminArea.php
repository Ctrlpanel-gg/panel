<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CanAccessAdminArea
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(403, __('Unauthorized access to the admin area.'));
        }

        if ($user->can('*') || $user->hasRole('Admin')) {
            return $next($request);
        }

        $canAccessAdminArea = $user->getAllPermissions()
            ->pluck('name')
            ->contains(fn (string $permission) => str_starts_with($permission, 'admin.') || str_starts_with($permission, 'settings.'));

        if (! $canAccessAdminArea) {
            abort(403, __('Unauthorized access to the admin area.'));
        }

        return $next($request);
    }
}

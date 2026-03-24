<?php

namespace App\Http\Middleware;

use App\Models\ApplicationApi;
use Closure;
use Illuminate\Http\Request;

class ApplicationApiScope
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string ...$abilities)
    {
        /** @var ApplicationApi|null $token */
        $token = $request->attributes->get('apiToken');

        if (! $token) {
            return response()->json(['message' => 'Unauthenticated API token context'], 401);
        }

        if (! $token->isActive()) {
            return response()->json(['message' => 'Expired or revoked Authorization token'], 401);
        }

        if (! $token->hasAnyAbility($abilities)) {
            return response()->json(['message' => 'The API token does not have the required scope'], 403);
        }

        return $next($request);
    }
}

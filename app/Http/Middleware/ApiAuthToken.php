<?php

namespace App\Http\Middleware;

use App\Models\ApplicationApi;
use Closure;
use Illuminate\Http\Request;

class ApiAuthToken
{
    /**
     * Handle an incoming request.
     *
     * @param  Request  $request
     * @param  Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (empty($request->bearerToken())) {
            return response()->json(['message' => 'Missing Authorization header'], 403);
        }

        $token = ApplicationApi::findToken($request->bearerToken());
        if (is_null($token)) {
            return response()->json(['message' => 'Invalid Authorization token'], 401);
        }

        if (! $token->isActive()) {
            return response()->json(['message' => 'Expired or revoked Authorization token'], 401);
        }

        $request->attributes->set('apiToken', $token);

        $response = $next($request);
        $token->updateLastUsed();

        return $response;
    }
}

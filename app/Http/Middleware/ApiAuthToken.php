<?php

namespace App\Http\Middleware;

use App\Models\ApplicationApi;
use App\Exceptions\ApiErrorCode;
use App\Services\ApiResponseService;
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
            return ApiResponseService::error(
                ApiErrorCode::MISSING_AUTHORIZATION_HEADER,
                'Missing Authorization header',
                403
            );
        }

        $token = ApplicationApi::find($request->bearerToken());

        if (is_null($token)) {
            return ApiResponseService::error(
                ApiErrorCode::INVALID_TOKEN,
                'Invalid Authorization token',
                401
            );
        }

        if (!$token->isValid()) {
            if (!$token->is_active) {
                return ApiResponseService::error(
                    ApiErrorCode::TOKEN_INACTIVE,
                    'Token is inactive',
                    401
                );
            }

            return ApiResponseService::error(
                ApiErrorCode::TOKEN_EXPIRED,
                'Token has expired',
                401
            );
        }

        $token->updateLastUsed();

        $request->attributes->set('api_token', $token);

        return $next($request);
    }
}

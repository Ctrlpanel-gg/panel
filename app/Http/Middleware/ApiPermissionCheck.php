<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Exceptions\ApiErrorCode;
use App\Services\ApiResponseService;
use Closure;
use Illuminate\Http\Request;

class ApiPermissionCheck
{
    public function handle(Request $request, Closure $next, string $permission)
    {
        /** @var \App\Models\ApplicationApi|null $token */
        $token = $request->attributes->get('api_token');

        if ($token && !$token->hasPermission($permission)) {
            return ApiResponseService::error(
                ApiErrorCode::INSUFFICIENT_PERMISSIONS,
                'Insufficient permissions to access this resource.',
                403
            );
        }

        return $next($request);
    }
}

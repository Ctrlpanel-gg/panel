<?php

namespace App\Http\Middleware;

use App\Models\ApplicationApi;
use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiAuditTrail
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (! in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            return $response;
        }

        if ($response->getStatusCode() >= 400) {
            return $response;
        }

        /** @var ApplicationApi|null $token */
        $token = $request->attributes->get('apiToken');
        if (! $token) {
            return $response;
        }

        $activity = activity('api')->withProperties([
            'api_token_id' => $token->id,
            'api_token_owner_user_id' => $token->owner_user_id,
            'route' => $request->route()?->getName(),
            'path' => $request->path(),
            'method' => $request->method(),
            'ip' => $request->ip(),
        ]);

        foreach (['user', 'server', 'product', 'voucher', 'role', 'notification'] as $parameter) {
            $model = $request->route($parameter);

            if ($model instanceof Model) {
                $activity->performedOn($model);
                break;
            }
        }

        $activity->log(sprintf(
            'API %s %s',
            $request->method(),
            $request->route()?->getName() ?? $request->path()
        ));

        return $response;
    }
}

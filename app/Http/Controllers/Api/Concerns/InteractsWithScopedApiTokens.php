<?php

namespace App\Http\Controllers\Api\Concerns;

use App\Models\ApplicationApi;
use App\Models\Server;
use App\Models\User;
use Illuminate\Http\Request;

trait InteractsWithScopedApiTokens
{
    protected function currentApiToken(Request $request): ?ApplicationApi
    {
        return $request->attributes->get('apiToken');
    }

    protected function ownerScopedUserId(Request $request): ?int
    {
        return $this->currentApiToken($request)?->owner_user_id;
    }

    protected function restrictUsersToTokenOwner(Request $request, $query)
    {
        $ownerUserId = $this->ownerScopedUserId($request);

        if ($ownerUserId !== null) {
            $query->whereKey($ownerUserId);
        }

        return $query;
    }

    protected function restrictServersToTokenOwner(Request $request, $query)
    {
        $ownerUserId = $this->ownerScopedUserId($request);

        if ($ownerUserId !== null) {
            $query->where('user_id', $ownerUserId);
        }

        return $query;
    }

    protected function ensureCanAccessUser(Request $request, User $user): void
    {
        $ownerUserId = $this->ownerScopedUserId($request);

        if ($ownerUserId !== null && $ownerUserId !== $user->id) {
            abort(404);
        }
    }

    protected function ensureCanAccessServer(Request $request, Server $server): void
    {
        $ownerUserId = $this->ownerScopedUserId($request);

        if ($ownerUserId !== null && $ownerUserId !== $server->user_id) {
            abort(404);
        }
    }

    protected function ensureTargetsOnlyTokenOwner(Request $request, array $userIds): void
    {
        $ownerUserId = $this->ownerScopedUserId($request);

        if ($ownerUserId === null) {
            return;
        }

        $normalizedIds = array_values(array_unique(array_map('intval', $userIds)));

        if ($normalizedIds !== [$ownerUserId]) {
            abort(404);
        }
    }

    protected function ensureGlobalToken(Request $request): void
    {
        if ($this->ownerScopedUserId($request) !== null) {
            abort(403, 'This API token cannot access global resources.');
        }
    }
}

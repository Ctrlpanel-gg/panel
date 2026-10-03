<?php

namespace App\Services;

use Carbon\CarbonImmutable;

class DiscordAccountAgeService
{
    private const EPOCH_MS = 1420070400000;

    public function createdAt(string $discordId): ?CarbonImmutable
    {
        if (!ctype_digit($discordId)) {
            return null;
        }

        $ms = ((int) $discordId >> 22) + self::EPOCH_MS;

        return CarbonImmutable::createFromTimestampMsUTC($ms);
    }

    public function meetsMinimumAge(string $discordId, int $minimumDays): bool
    {
        $createdAt = $this->createdAt($discordId);

        return $createdAt !== null
            && $createdAt->lte(now()->subDays($minimumDays));
    }
}

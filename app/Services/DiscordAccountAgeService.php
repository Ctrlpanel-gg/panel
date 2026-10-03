<?php

namespace App\Services;

class DiscordAccountAgeService
{
    private const EPOCH_MS = 1420070400000;
    private const MS_PER_DAY = 86400000;

    public function ageInDays(string $discordId): ?int
    {
        if (!ctype_digit($discordId)) {
            return null;
        }
        $createdAtMs = ((int) $discordId >> 22) + self::EPOCH_MS;
        return (int) floor((microtime(true) * 1000 - $createdAtMs) / self::MS_PER_DAY);
    }

    public function meetsMinimumAge(string $discordId, int $minimumDays): bool
    {
        $ageInDays = $this->ageInDays($discordId);
        return $ageInDays === null || $ageInDays >= $minimumDays;
    }
}

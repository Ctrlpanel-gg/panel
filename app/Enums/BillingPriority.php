<?php

namespace App\Enums;

enum BillingPriority: int
{
    case HIGH = 1;
    case MEDIUM = 2;
    case LOW = 3;

    public function label(): string
    {
        return match($this) {
            self::HIGH => __('High'),
            self::MEDIUM => __('Medium'),
            self::LOW => __('Low'),
        };
    }

    public function description(): string
    {
        return match($this) {
            self::HIGH => __('Renewed first when there are limited credits'),
            self::MEDIUM => __('Default priority for most servers'),
            self::LOW => __('Renewed last, ideal for test servers'),
        };
    }

    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(function ($priority) {
            return [$priority->value => $priority->label()];
        })->toArray();
    }

    public static function fromValue(int $value): ?self
    {
        return self::tryFrom($value);
    }
}
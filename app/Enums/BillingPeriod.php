<?php

namespace App\Enums;

enum BillingPeriod: int
{
    case HOURLY = 1;
    case DAILY = 2;
    case WEEKLY = 3;
    case MONTHLY = 4;
    case QUARTERLY = 5;
    case HALF_ANNUALLY = 6;
    case ANNUALLY = 7;

    public function label(): string
    {
        return match($this) {
            self::HOURLY => __('Hourly'),
            self::DAILY => __('Daily'),
            self::WEEKLY => __('Weekly'),
            self::MONTHLY => __('Monthly'),
            self::QUARTERLY => __('Quarterly'),
            self::HALF_ANNUALLY => __('Half Annually'),
            self::ANNUALLY => __('Annually'),
        };
    }

    public function description(): string
    {
        return match($this) {
            self::HOURLY => __('Charge the server every hour.'),
            self::DAILY => __('Charge the server every day.'),
            self::WEEKLY => __('Charge the server every week.'),
            self::MONTHLY => __('Charge the server every month.'),
            self::QUARTERLY => __('Charge the server every quarter.'),
            self::HALF_ANNUALLY => __('Charge the server every half year.'),
            self::ANNUALLY => __('Charge the server every year.'),
        };
    }

    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(function ($period) {
            return [$period->value => $period->label()];
        })->toArray();
    }

    public static function fromValue(int $value): ?self
    {
        return self::tryFrom($value);
    }
}
<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Support\Facades\DB;

class EggBelongsToProduct implements DataAwareRule, ValidationRule
{
    /**
     * All of the data under validation.
     *
     * @var array<string, mixed>
     */
    protected $data = [];

    /**
     * Set the data under validation.
     *
     * @param  array<string, mixed>  $data
     */
    public function setData(array $data): void
    {
        $this->data = $data;
    }

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $exists = DB::table('egg_product')
            ->where('product_id', $this->data['product_id'])
            ->where('egg_id', $value)
            ->exists();

        if (!$exists) {
            $fail("The egg with ID {$value} does not belong to the product with ID {$this->data['product_id']}.");
        }
    }

    /**
     * Get the validation rules documentation.
     *
     * @return array<string, mixed>
     */
    public static function docs(): array
    {
        return [
            'description' => 'The egg must belong to the specified product.',
        ];
    }
}

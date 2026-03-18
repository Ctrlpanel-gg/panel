<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class ValidateEggVariables implements DataAwareRule, ValidationRule
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
        $egg = DB::table('eggs')->where('id', $this->data['egg_id'])->first();

        if (!$egg) {
            $fail("The selected egg is invalid.");

            return;
        }

        $environment = collect(json_decode($egg->environment, true));

        $environment = $environment->filter(function ($item) {
            return str_contains($item['rules'], 'required') && empty($item['default_value']);
        });

        if (!$environment->isEmpty()) {
            $eggVariables = collect($this->data['egg_variables'] ?? []);

            foreach ($environment as $envVariable) {
                $this->validateVariableRules($envVariable, $eggVariables->get($envVariable['env_variable']), $fail);
            }

            return;
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
            'description' => 'Each egg has its own variable rules.',
        ];
    }

    /**
     * Validate the rules for each environment variable.
     */
    private function validateVariableRules(array $envVar, $value, Closure $fail): void
    {
        $validator = Validator::make(
            [$envVar['env_variable'] => $value],
            [$envVar['env_variable'] => $envVar['rules']],
        );

        $validator->setAttributeNames([
            $envVar['env_variable'] => $envVar['env_variable'],
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->get($envVar['env_variable']) as $error) {
                $fail($error);
            }
        }
    }
}

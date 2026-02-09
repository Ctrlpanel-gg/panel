<?php

namespace App\Http\Requests\Api\Products;

use App\Constants\MysqlLimits;
use App\Enums\BillingPriority;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:30',
            'description' => 'nullable|string|max:191',
            'price' => ['required', 'numeric',
                'max:' . MysqlLimits::CREDITS_MAX,
                'min:' . MysqlLimits::CREDITS_MIN,
            ],
            'minimum_credits' => ['nullable', 'numeric',
                'max:' . MysqlLimits::CREDITS_MAX,
                'min:' . MysqlLimits::CREDITS_MIN,
            ],
            'memory' => 'required|numeric|max:1000000|min:5',
            'cpu' => 'required|numeric|max:1000000|min:0',
            'swap' => 'required|numeric|max:1000000|min:0',
            'disk' => 'required|numeric|max:1000000|min:5',
            'io' => 'required|numeric|max:1000000|min:0',
            'serverlimit' => 'required|numeric|max:1000000|min:0',
            'databases' => 'required|numeric|max:1000000|min:0',
            'backups' => 'required|numeric|max:1000000|min:0',
            'allocations' => 'required|numeric|max:1000000|min:0',
            'nodes' => 'sometimes|array',
            'nodes.*' => 'integer|exists:nodes,id',
            'eggs' => 'sometimes|array',
            'eggs.*' => 'integer|exists:eggs,id',
            'disabled' => 'sometimes|boolean',
            'oom_killer' => 'sometimes|boolean',
            'default_billing_priority' => ['sometimes', Rule::enum(BillingPriority::class)],
            'billing_period' => 'required|in:hourly,daily,weekly,monthly,quarterly,half-annually,annually'
        ];
    }
}

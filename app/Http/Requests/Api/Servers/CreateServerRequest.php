<?php

namespace App\Http\Requests\Api\Servers;

use App\Enums\BillingPriority;
use App\Rules\EggBelongsToProduct;
use App\Rules\ValidateEggVariables;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateServerRequest extends FormRequest
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
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:255',
            'user_id' => 'required|integer|exists:users,id',
            'product_id' => 'required|string|exists:products,id',
            'egg_id' => ['required', 'integer', 'exists:eggs,id', new EggBelongsToProduct, new ValidateEggVariables],
            'location_id' => 'required|integer|exists:locations,id',
            'egg_variables' => 'nullable|array',
            'billing_priority' => ['nullable', Rule::enum(BillingPriority::class)],
        ];
    }
}

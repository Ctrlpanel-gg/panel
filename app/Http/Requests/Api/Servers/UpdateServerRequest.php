<?php

namespace App\Http\Requests\Api\Servers;

use App\Enums\BillingPriority;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateServerRequest extends FormRequest
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
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string|max:255',
            'user_id' => 'required|integer|exists:users,id',
            'billing_priority' => ['nullable', Rule::enum(BillingPriority::class)],
        ];
    }
}

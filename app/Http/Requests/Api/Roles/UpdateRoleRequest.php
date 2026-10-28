<?php

namespace App\Http\Requests\Api\Roles;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @bodyParam name string The name of the role. Example: Moderator
 * @bodyParam color string The hex color of the role. Example: #00FF00
 * @bodyParam power integer The power level of the role. Example: 50
 * @bodyParam permissions string[] The permissions assigned to the role. Example: [ "admin.roles.read" ]
 */
class UpdateRoleRequest extends FormRequest
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
            'name' => 'sometimes|string|max:191',
            'color' => ['sometimes', 'regex:/^#([a-f0-9]{6}|[a-f0-9]{3})$/i'],
            'power' => 'sometimes|integer',
            'permissions' => 'sometimes|array',
            'permissions.*' => 'string|exists:permissions,name',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'permissions.*.exists' => 'The permission ":input" does not exist.',
        ];
    }
}

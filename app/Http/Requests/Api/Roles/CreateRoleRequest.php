<?php

namespace App\Http\Requests\Api\Roles;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @bodyParam name string required The name of the role. Example: Moderator
 * @bodyParam color string required The hex color of the role. Example: #00FF00
 * @bodyParam power integer required The power level of the role. Example: 50
 * @bodyParam permissions string[] The permissions assigned to the role. Example: [ "admin.roles.read" ]
 */
class CreateRoleRequest extends FormRequest
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
            'name' => 'required|string|max:191',
            'color' => ['required', 'regex:/^#([a-f0-9]{6}|[a-f0-9]{3})$/i'],
            'power' => 'required|integer',
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

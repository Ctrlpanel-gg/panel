<?php

namespace App\Http\Requests\Api\Users;

use App\Constants\MysqlLimits;
use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
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
            'name' => 'required|string|min:4|max:30',
            'email' => 'required|string|email',
            'password' => 'sometimes|string|min:8|max:191',
            'credits' => ['sometimes', 'numeric',
                'min:' . MysqlLimits::CREDITS_MIN,
                'max:' . MysqlLimits::CREDITS_MAX,
            ],
            'server_limit' => ['sometimes', 'numeric',
                'min:' . MysqlLimits::SERVER_LIMIT_MIN,
                'max:' . MysqlLimits::SERVER_LIMIT_MAX,
            ],
            'role_id' => 'sometimes|integer|exists:roles,id',
        ];
    }
}

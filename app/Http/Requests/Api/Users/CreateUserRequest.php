<?php

namespace App\Http\Requests\Api\Users;

use App\Constants\MysqlLimits;
use App\Settings\UserSettings;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Http\FormRequest;

class CreateUserRequest extends FormRequest
{
    private UserSettings $userSettings;

    public function __construct()
    {
        $this->userSettings = app(UserSettings::class);
        parent::__construct();
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->userSettings->creation_enabled;
    }

    protected function failedAuthorization()
    {
        throw new AuthorizationException(
            'The creation of new users has been temporarily disabled. Please try again later.'
        );
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|min:4|max:30|alpha_num|unique:users',
            'email' => 'required|string|email|unique:users|email',
            'password' => 'required|string|min:8|max:191',
            'credits' => ['sometimes', 'numeric',
                'min:' . MysqlLimits::CREDITS_MIN,
                'max:' . MysqlLimits::CREDITS_MAX,
            ],
            'server_limit' => ['sometimes', 'numeric',
                'min:' . MysqlLimits::SERVER_LIMIT_MIN,
                'max:' . MysqlLimits::SERVER_LIMIT_MAX,
            ],
            'role_id' => 'required|integer|exists:roles,id',
            'referral_code' => 'sometimes|nullable|string|exists:users,referral_code',
        ];
    }
}

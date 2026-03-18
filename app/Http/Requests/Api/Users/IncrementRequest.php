<?php

namespace App\Http\Requests\Api\Users;

use App\Constants\MysqlLimits;
use App\Models\User;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class IncrementRequest extends FormRequest
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
            'credits' => ['sometimes', 'numeric',
                'min:' . MysqlLimits::CREDITS_MIN,
                'max:' . MysqlLimits::CREDITS_MAX,
            ],
            'server_limit' => ['sometimes', 'numeric',
                'min:' . MysqlLimits::SERVER_LIMIT_MIN,
                'max:' . MysqlLimits::SERVER_LIMIT_MAX,
            ],
        ];
    }

    public function withValidator(Validator $validator)
    {
        $validator->after(function ($validator) {
            $user = $this->route('user');
            
            $newCredits = $user->credits + ($this->input('credits') * 1000);
            $newServerLimit = $user->server_limit + $this->input('server_limit');

            if ($this->has('credits') && ($newCredits > MysqlLimits::CREDITS_MAX)) {
                $validator->errors()->add('credits', "Adding {$this->input('credits')} credits would result in {$newCredits}, exceeding the maximum limit of " . MysqlLimits::CREDITS_MAX . ".");
            }

            if ($this->has('server_limit') && ($newServerLimit > MysqlLimits::SERVER_LIMIT_MAX)) {
                $validator->errors()->add('server_limit', "Adding {$this->input('server_limit')} server limit would result in {$newServerLimit}, exceeding the maximum limit of " . MysqlLimits::SERVER_LIMIT_MAX . ".");
            }
        });
    }
}

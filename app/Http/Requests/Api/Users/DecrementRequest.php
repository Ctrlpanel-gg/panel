<?php

namespace App\Http\Requests\Api\Users;

use App\Constants\MysqlLimits;
use App\Models\User;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class DecrementRequest extends FormRequest
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
            'credits' => ['sometimes', 'numeric', 'min:1'],
            'server_limit' => ['sometimes', 'numeric', 'min:1']
        ];
    }

    public function withValidator(Validator $validator)
    {
        $validator->after(function ($validator) {
            $user = $this->route('user');

            $newCredits = $user->credits - $this->input('credits') * 1000;
            $newServerLimit = $user->server_limit - $this->input('server_limit');
            
            if ($this->has('credits') && ($newCredits < MysqlLimits::CREDITS_MIN)) {
                $maxRemovable = floor($user->credits / 1000);
                $validator->errors()->add('credits', 
                    "Cannot remove {$this->input('credits')} credits. User has {$maxRemovable} credits available."
                );
            }
            
            if ($this->has('server_limit') && ($newServerLimit < MysqlLimits::SERVER_LIMIT_MIN)) {
                $maxRemovable = $user->server_limit;
                $validator->errors()->add('server_limit', 
                    "Cannot remove {$this->input('server_limit')}. User has {$maxRemovable} server limit available."
                );
            }
        });
    }
}

<?php

namespace App\Http\Requests\Api\Vouchers;

use App\Constants\MysqlLimits;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateVoucherRequest extends FormRequest
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
            'memo' => 'sometimes|nullable|string|max:191',
            'uses' => 'required|numeric|max:2147483647|min:1',
            'code' => ['required', 'string', 'alpha_dash', 'min:4', 'max:36',
                Rule::unique('vouchers')->ignore($this->route('voucher')?->id),
            ],
            'credits' => ['required', 'numeric', 
                'min:' . MysqlLimits::CREDITS_MIN,
                'max:' . MysqlLimits::CREDITS_MAX,
            ],
            'expires_at' => 'sometimes|nullable|multiple_date_format:d-m-Y H:i:s,d-m-Y|after:now|before:10 years',
        ];
    }
}

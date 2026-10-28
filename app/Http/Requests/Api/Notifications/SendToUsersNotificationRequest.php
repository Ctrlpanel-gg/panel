<?php

namespace App\Http\Requests\Api\Notifications;

use Illuminate\Foundation\Http\FormRequest;

class SendToUsersNotificationRequest extends FormRequest
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
            'via' => 'required|in:mail,database,both',
            'users' => 'required|array',
            'users.*' => 'integer|exists:users,id',
            'title' => 'required|string|min:1',
            'content' => 'required|string|min:1',
        ];
    }

    public function bodyParameters()
    {
        return [
            'via' => [
                'description' => 'The notification channel (mail, database, both).',
                'example' => 'both',
            ],
            'users' => [
                'description' => 'List of user IDs to notify.',
                'example' => [1, 2],
            ],
            'title' => [
                'description' => 'The title of the notification.',
                'example' => 'System Update',
            ],
            'content' => [
                'description' => 'The content of the notification.',
                'example' => 'A new update is available.',
            ],
        ];
    }
}

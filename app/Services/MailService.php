<?php

namespace App\Services;

use App\Mail\TestMail;
use App\Settings\MailTemplatesSettings;
use Illuminate\Support\Facades\Mail;

class MailService
{
    public function sendTestMail($user, MailTemplatesSettings $mailTemplatesSettings): void
    {
        // Fetch the mail template from the database
        $template = [
            'subject' => $mailTemplatesSettings->mail_welcome_subject,
            'body' => $mailTemplatesSettings->mail_welcome_body,
        ];

        // Prepare the variables to replace in the template
        $variables = [
            'panel' => env('APP_NAME'),
            'user' => $user->name,
        ];

        // Send the mail
        Mail::to($user->email)->send(new TestMail($template, $variables));
    }
}

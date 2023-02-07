<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class MailSettings extends Settings
{
    public ?string $mail_host;

    public ?int $mail_port;

    public ?string $mail_username;

    public ?string $mail_password;

    public ?string $mail_encryption;

    public ?string $mail_from_address;

    public ?string $mail_from_name;

    public ?string $mail_mailer;

    public ?bool $mail_enabled;
    
    public static function group(): string
    {
        return 'mail';
    }

    public static function encrypted(): array
    {
        return [
            'mail_password'
        ];
    }

    public function setConfig()
    {
        try {
            config()->set('mail.mailers.smtp.host', $this->mail_host);
            config()->set('mail.mailers.smtp.port', $this->mail_port);
            config()->set('mail.mailers.smtp.encryption', $this->mail_encryption);
            config()->set('mail.mailers.smtp.username', $this->mail_username);
            config()->set('mail.mailers.smtp.password', $this->mail_password);
            config()->set('mail.from.address', $this->mail_from_address);
            config()->set('mail.from.name', $this->mail_from_name);
            config()->set('mail.mailers.smtp.transport', $this->mail_mailer);
        } catch (\Exception) {

        }
    }
}
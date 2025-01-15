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

    public static function group(): string
    {
        return 'mail';
    }
    /*

    public static function encrypted(): array
    {
        return [
            'mail_password',
        ];
    }
*/
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
        } catch (\Exception) {
        }
    }

    /**
     * Summary of validations array
     * @return array<string, string>
     */
    public static function getValidations()
    {
        return [
            'mail_host' => 'nullable|string',
            'mail_port' => 'nullable|int',
            'mail_username' => 'nullable|string',
            'mail_password' => 'nullable|string',
            'mail_encryption' => 'nullable|string',
            'mail_from_address' => 'nullable|string',
            'mail_from_name' => 'nullable|string',
            'mail_mailer' => 'nullable|string',
        ];
    }

    /**
     * Summary of optionTypes
     * Only used for the settings page
     * @return array<array<'type'|'label'|'description'|'options', string|bool|float|int|array<string, string>>>
     */
    public static function getOptionInputData()
    {
        return [
            'category_icon' => 'fas fa-envelope',
            'position' => 4,
            'mail_host' => [
                'label' => 'Mail Host',
                'type' => 'string',
                'description' => 'The host of your mail server.',
            ],
            'mail_port' => [
                'label' => 'Mail Port',
                'type' => 'number',
                'description' => 'The port of your mail server.',
            ],
            'mail_username' => [
                'label' => 'Mail Username',
                'type' => 'string',
                'description' => 'The username of your mail server.',
            ],
            'mail_password' => [
                'label' => 'Mail Password',
                'type' => 'password',
                'description' => 'The password of your mail server.',
            ],
            'mail_encryption' => [
                'label' => 'Mail Encryption',
                'type' => 'select',
                'options' => [
                    'null' => 'None',
                    'tls' => 'TLS',
                    'ssl' => 'SSL'
                ],
                'description' => 'The encryption of your mail server.',
            ],
            'mail_from_address' => [
                'label' => 'Mail From Address',
                'type' => 'string',
                'description' => 'The from address of your mail server.',
            ],
            'mail_from_name' => [
                'label' => 'Mail From Name',
                'type' => 'string',
                'description' => 'The from name of your mail server.',
            ],
            'mail_mailer' => [
                'label' => 'Mail Mailer',
                'type' => 'string',
                'description' => 'The mailer of your mail server.',
            ],
        ];
    }
}

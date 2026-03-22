<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        foreach ($this->encryptedSettings() as [$group, $name]) {
            DB::table('settings')
                ->where('group', $group)
                ->where('name', $name)
                ->get(['id', 'payload'])
                ->each(function (object $setting): void {
                    $value = json_decode($setting->payload, true);

                    if (! is_string($value)) {
                        return;
                    }

                    if ($value === '') {
                        DB::table('settings')
                            ->where('id', $setting->id)
                            ->update(['payload' => json_encode(null)]);

                        return;
                    }

                    if ($this->isEncrypted($value)) {
                        return;
                    }

                    DB::table('settings')
                        ->where('id', $setting->id)
                        ->update(['payload' => json_encode(Crypt::encrypt($value))]);
                });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        foreach ($this->encryptedSettings() as [$group, $name]) {
            DB::table('settings')
                ->where('group', $group)
                ->where('name', $name)
                ->get(['id', 'payload'])
                ->each(function (object $setting): void {
                    $value = json_decode($setting->payload, true);

                    if (! is_string($value) || ! $this->isEncrypted($value)) {
                        return;
                    }

                    DB::table('settings')
                        ->where('id', $setting->id)
                        ->update(['payload' => json_encode(Crypt::decrypt($value))]);
                });
        }
    }

    private function encryptedSettings(): array
    {
        return [
            ['discord', 'bot_token'],
            ['discord', 'client_secret'],
            ['general', 'recaptcha_secret_key'],
            ['mail', 'mail_password'],
            ['pterodactyl', 'admin_token'],
            ['pterodactyl', 'user_token'],
        ];
    }

    private function isEncrypted(string $value): bool
    {
        try {
            Crypt::decrypt($value);

            return true;
        } catch (\Throwable) {
            return false;
        }
    }
};

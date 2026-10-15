<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\LaravelSettings\Migrations\SettingsMigration;

class RenameStripeEndpointSecretToPublishableKey extends SettingsMigration
{
    public function up(): void
    {
        $this->renameSetting('stripe.endpoint_secret', 'stripe.publishable_key');
        $this->renameSetting('stripe.test_endpoint_secret', 'stripe.test_publishable_key');
    }

    public function down(): void
    {
        $this->renameSetting('stripe.publishable_key', 'stripe.endpoint_secret');
        $this->renameSetting('stripe.test_publishable_key', 'stripe.test_endpoint_secret');
    }

    protected function renameSetting(string $oldName, string $newName): void
    {
        $oldGroup = explode('.', $oldName)[0];
        $oldKey = explode('.', $oldName)[1];
        
        $newGroup = explode('.', $newName)[0];
        $newKey = explode('.', $newName)[1];

        $setting = DB::table('settings')
            ->where('group', $oldGroup)
            ->where('name', $oldKey)
            ->first();

        if ($setting) {
            DB::table('settings')
                ->where('id', $setting->id)
                ->update([
                    'group' => $newGroup,
                    'name' => $newKey,
                ]);
        }
    }
}

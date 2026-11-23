<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            TermsSeeder::class,
            GeneralPermissionsSeeder::class,
        ]);

        if (class_exists(\App\Helpers\ExtensionHelper::class)) {
            $extensionSeeders = \App\Helpers\ExtensionHelper::getAllExtensionSeeders();
            sort($extensionSeeders);

            foreach ($extensionSeeders as $seeder) {
                try {
                    $this->call($seeder);
                } catch (\Throwable $exception) {
                    $this->command->error("Extension seeder failed: {$seeder} ({$exception->getMessage()})");

                    throw $exception;
                }
            }
        }
    }
}

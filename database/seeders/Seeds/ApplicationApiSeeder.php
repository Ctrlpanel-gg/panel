<?php

namespace Database\Seeders\Seeds;

use App\Models\ApplicationApi;
use Illuminate\Database\Seeder;

class ApplicationApiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        [, $plainTextToken] = ApplicationApi::issue(null, 'admin', ['*']);

        $this->command?->info("Created example application API token: {$plainTextToken}");
    }
}

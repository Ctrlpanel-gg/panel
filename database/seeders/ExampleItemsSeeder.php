<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class ExampleItemsSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            ProductSeeder::class,
            PaypalProductSeeder::class,
            ApplicationApiSeeder::class,
            UsefulLinksSeeder::class
        ]);

    }
}

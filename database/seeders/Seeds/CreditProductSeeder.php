<?php

namespace Database\Seeders\Seeds;

use App\Models\CreditProduct;
use Illuminate\Database\Seeder;

class CreditProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        CreditProduct::create([
            'type' => 'Credits',
            'display' => '350',
            'description' => 'Adds 350 credits to your account',
            'quantity' => '350',
            'currency_code' => 'EUR',
            'price' => 2.00,
            'disabled' => false,
        ]);

        CreditProduct::create([
            'type' => 'Credits',
            'display' => '875 + 125',
            'description' => 'Adds 1000 credits to your account',
            'quantity' => '1000',
            'currency_code' => 'EUR',
            'price' => 5.00,
            'disabled' => false,
        ]);

        CreditProduct::create([
            'type' => 'Credits',
            'display' => '1750 + 250',
            'description' => 'Adds 2000 credits to your account',
            'quantity' => '2000',
            'currency_code' => 'EUR',
            'price' => 10.00,
            'disabled' => false,
        ]);

        CreditProduct::create([
            'type' => 'Credits',
            'display' => '3500 + 500',
            'description' => 'Adds 4000 credits to your account',
            'quantity' => '4000',
            'currency_code' => 'EUR',
            'price' => 20.00,
            'disabled' => false,
        ]);
    }
}

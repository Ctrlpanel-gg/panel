<?php

namespace Database\Seeders;

use App\Models\UsefulLink;
use Illuminate\Database\Seeder;

class UsefulLinksSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        UsefulLink::create([
            'icon' => 'fas fa-egg',
            'title' => 'Pterodactyl Panel',
            'link' => env('PTERODACTYL_URL', 'http://localhost'),
            'message' => 'Use your servers on our pterodactyl panel <small>(You can use the same login details)</small>'
        ]);
        UsefulLink::create([
            'icon' => 'fas fa-database',
            'title' => 'phpMyAdmin',
            'link' => env('PHPMYADMIN_URL', 'http://localhost'),
            'message' => 'View your database online using phpMyAdmin'
        ]);
        UsefulLink::create([
            'icon' => 'fab fa-discord',
            'title' => 'Discord',
            'link' => env('DISCORD_INVITE_URL'),
            'message' => 'Need a helping hand? Want to chat? Got any questions? Join our discord!'
        ]);
        UsefulLink::create([
            'icon' => 'fas fa-link',
            'title' => 'Useful Links',
            'link' => '_blank',
            'message' => 'Want to make your own links that show here? Use the command <code>php artisan create:usefullink</code></br> Delete links via database (for now)'
        ]);
    }
}

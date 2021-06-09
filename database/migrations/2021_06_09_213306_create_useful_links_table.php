<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateUsefulLinksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('useful_links', function (Blueprint $table) {
            $table->id();
            $table->string('icon')->default('');
            $table->string('title')->default('Default Title');
            $table->string('link')->default('https://bitsec.dev');
            $table->string('message')->default('Default Message');
            $table->timestamps();
        });

        \App\Models\UsefulLink::create([
            'icon' => 'fas fa-egg',
            'title' => 'Pterodactyl Panel',
            'link' => env('PTERODACTYL_URL' , 'http://localhost'),
            'message' => 'Use your servers on our pterodactyl panel <small>(You can use the same login details)</small>'
        ]);
        \App\Models\UsefulLink::create([
            'icon' => 'fas fa-database',
            'title' => 'phpMyAdmin',
            'link' => env('PHPMYADMIN_URL' , 'http://localhost'),
            'message' => 'View your database online using phpMyAdmin'
        ]);
        \App\Models\UsefulLink::create([
            'icon' => 'fab fa-discord',
            'title' => 'Discord',
            'link' => env('DISCORD_INVITE_URL'),
            'message' => 'Need a helping hand? Want to chat? Got any questions? Join our discord!'
        ]);
        \App\Models\UsefulLink::create([
            'icon' => 'fas fa-link',
            'title' => 'Useful Links',
            'link' => '_blank',
            'message' => 'Want to make your own links that show here? Use the command <code>php artisan create:usefullink</code></br> Delete links via database (for now)'
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('useful_links');
    }
}

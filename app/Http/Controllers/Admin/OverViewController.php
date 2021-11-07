<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Server;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

class OverViewController extends Controller
{
    public const TTL = 86400;

    public function index()
    {
        $userCount = Cache::remember('user:count', self::TTL, function () {
            return User::query()->count();
        });

        $creditCount = Cache::remember('credit:count', self::TTL, function () {
            return User::query()->sum('credits');
        });

        $paymentCount = Cache::remember('payment:count', self::TTL, function () {
            return Payment::query()->count();
        });

        $serverCount = Cache::remember('server:count', self::TTL, function () {
            return Server::query()->count();
        });

        return view('admin.overview.index', [
            'serverCount'  => $serverCount,
            'userCount'    => $userCount,
            'paymentCount' => $paymentCount,
            'creditCount'  => number_format($creditCount, 2, '.', ''),
        ]);
    }
}

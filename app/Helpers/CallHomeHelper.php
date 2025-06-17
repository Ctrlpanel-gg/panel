<?php

/*
 *
 *  All this does is tracking the total number of installations of cpgg for us to know how many people use it.
 *  It is not used for any other purpose and does not collect any personal data.
 *  It is a one-time call per installation.
 *
 */

namespace App\Helpers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CallHomeHelper
{
    /**
     *
     * @return void
     */
    public static function callHomeOnce(): void
    {
        $flagFile = storage_path('app/callhome_sent.flag');
        if (file_exists($flagFile)) {
            return;
        }

        try {
            $url = parse_url(config('app.url'), PHP_URL_HOST);
            $urlHash = md5($url);
            Http::async()->post('https://utils.ctrlpanel.gg/callhome.php', [
                'url' => $urlHash,
            ]);
            file_put_contents($flagFile, now());
        } catch (\Exception $e) {
            Log::error('CallHome fehlgeschlagen: ' . $e->getMessage());
        }
    }
}




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
use Illuminate\Support\Str;

class CallHomeHelper
{
    /**
     *
     * @return void
     */
    public static function callHomeOnce(): void
    {
        $flagFile = storage_path('app/callhome_sent.flag');
        $handle = @fopen($flagFile, 'c+');
        if ($handle === false) {
            return;
        }

        try {
            if (! flock($handle, LOCK_EX)) {
                fclose($handle);
                return;
            }

            $existingContents = stream_get_contents($handle);
            if ($existingContents !== false && trim($existingContents) !== '') {
                flock($handle, LOCK_UN);
                fclose($handle);
                return;
            }

            $installationId = (string) Str::uuid();
            $response = Http::timeout(5)->connectTimeout(5)->post('https://utils.ctrlpanel.gg/callhome.php', [
                'id' => $installationId,
            ]);

            ftruncate($handle, 0);
            rewind($handle);
            fwrite($handle, $installationId . PHP_EOL . $response->body());
            fflush($handle);
            flock($handle, LOCK_UN);
            fclose($handle);

            Log::info('CallHome: request sent');
        } catch (\Exception $e) {
            Log::error('CallHome fail: ' . $e->getMessage());
            if (is_resource($handle)) {
                flock($handle, LOCK_UN);
                fclose($handle);
            }
        }
    }
}



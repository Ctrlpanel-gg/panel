<?php

use DevCoder\DotEnv;
use Illuminate\Encryption\Encrypter;

(new DotEnv(dirname(__FILE__, 5) . '/.env'))->load();

/**
 * Encrypt the given value
 * @param mixed $value The variable to be encrypted
 * @param bool $serialize If the encryption should be serialized
 * @return string Returns the encrypted variable.
 */
function encryptSettingsValue(mixed $value, $serialize = true): string
{
    $appKey = getenv('APP_KEY');
    $appKey = base64_decode(Str::after($appKey, 'base64:'));
    $encrypter = new Encrypter($appKey, 'AES-256-CBC');
    $encryptedKey = $encrypter->encrypt($value, $serialize);

    return $encryptedKey;
}

/**
 * Decrypt the given value
 * @param mixed $payload The payload to be decrypted
 * @param bool $unserialize If the encryption should be unserialized
 * @return mixed Returns the decrypted variable on success, throws otherwise.
 */

function decryptSettingsValue(mixed $payload, $unserialize = true)
{
    $appKey = getenv('APP_KEY');
    $appKey = base64_decode(Str::after($appKey, 'base64:'));
    $encrypter = new Encrypter($appKey, 'AES-256-CBC');
    $decryptedKey = $encrypter->decrypt($payload, $unserialize);

    return $decryptedKey;
}

function determineIfRunningInDocker(): bool
{
    return file_exists('/.dockerenv');
}

?>

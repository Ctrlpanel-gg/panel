<?php
require '../../vendor/autoload.php';

use Illuminate\Encryption\Encrypter;
use Illuminate\Support\Str;

$required_extensions = ['openssl', 'gd', 'mysql', 'PDO', 'mbstring', 'tokenizer', 'bcmath', 'xml', 'curl', 'zip', 'intl'];

$requirements = [
    'minPhp' => '8.1',
    'maxPhp' => '8.2', // This version is not supported
    'mysql' => '5.7.22',
];

/**
 * Check if the minimum PHP version is present
 * @return string 'OK' on success and 'not OK' on failure.
 */
function checkPhpVersion(): string
{
    global $requirements;
    if (version_compare(phpversion(), $requirements['minPhp'], '>=') && version_compare(phpversion(), $requirements['maxPhp'], '<=')) {
        return 'OK';
    }

    return 'not OK';
}

/**
 * Check if the environment file is writable
 * @return bool Returns true on writable and false on not writable.
 */
function checkWriteable(): bool
{
    return is_writable('../../.env');
}

/**
 * Check if the server runs using HTTPS
 * @return bool Returns true on HTTPS or false on HTTP.
 */
function checkHTTPS(): bool
{
    return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || $_SERVER['SERVER_PORT'] == 443;
}

/**
 * Check if MySQL is installed and runs the correct version using a shell command
 * @return mixed|string 'OK' if required version is met, returns MySQL version if not met.
 */
function getMySQLVersion(): mixed
{
    global $requirements;

    $output = shell_exec('mysql -V') ?? '';
    preg_match('@[0-9]+\.[0-9]+\.[0-9]+@', $output, $version);

    $versionoutput = $version[0] ?? '0';

    return intval($versionoutput) > intval($requirements['mysql']) ? 'OK' : $versionoutput;
}

/**
 * Check if zip is installed using a shell command
 * @return string 'OK' on success and 'not OK' on failure.
 */
function getZipVersion(): string
{
    $output = shell_exec('zip  -v') ?? '';
    preg_match('@[0-9]+\.[0-9]+\.[0-9]+@', $output, $version);

    $versionoutput = $version[0] ?? 0;

    return $versionoutput != 0 ? 'OK' : 'not OK';
}

/**
 * Check if git is installed using a shell command
 * @return string 'OK' on success and 'not OK' on failure.
 */
function getGitVersion(): string
{
    $output = shell_exec('git  --version') ?? '';
    preg_match('@[0-9]+\.[0-9]+\.[0-9]+@', $output, $version);

    $versionoutput = $version[0] ?? 0;

    return $versionoutput != 0 ? 'OK' : 'not OK';
}

/**
 * Check if tar is installed using a shell command
 * @return string 'OK' on success and 'not OK' on failure.
 */
function getTarVersion(): string
{
    $output = shell_exec('tar  --version') ?? '';
    preg_match('@[0-9]+\.[0-9]+@', $output, $version);

    $versionoutput = $version[0] ?? 0;

    return $versionoutput != 0 ? 'OK' : 'not OK';
}

/**
 * Check all extensions to see if they have loaded or not
 * @return array Returns an array of extensions that failed to load.
 */
function checkExtensions(): array
{
    global $required_extensions;

    $not_ok = [];
    $extentions = get_loaded_extensions();

    foreach ($required_extensions as $ext) {
        if (!preg_grep('/^(?=.*' . $ext . ').*$/', $extentions)) {
            array_push($not_ok, $ext);
        }
    }

    return $not_ok;
}

/**
 * Sets the environment variable into the env file
 * @param string $envKey The environment key to set or modify
 * @param string $envValue The environment variable to set
 * @return bool true on success or false on failure.
 */
function setenv(string $envKey, $envValue)
{
    $str = "{$envKey}={$envValue}";
    return putenv($str);
}


/**
 * Encrypt the given value
 * @param mixed $value The variable to be encrypted
 * @param bool $serialize If the encryption should be serialized
 * @return string Returns the encrypted variable.
 */
function encryptSettingsValue(mixed $value, $serialize = true): string
{
    $appKey = getEnvironmentValue('APP_KEY');
    $appKey = base64_decode(Str::after($appKey, 'base64:'));
    $encrypter = new Encrypter($appKey);
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
    $appKey = getEnvironmentValue('APP_KEY');
    $appKey = base64_decode(Str::after($appKey, 'base64:'));
    $encrypter = new Encrypter($appKey);
    $decryptedKey = $encrypter->decrypt($payload, $unserialize);

    return $decryptedKey;
}

/**
 * Run a shell command
 * @param string $command The command string to run
 * @param array|null $descriptors [optional]<p>
 * An indexed array where the key represents the descriptor number and the value represents how PHP will pass that descriptor to the child process. 0 is stdin, 1 is stdout, while 2 is stderr.
 * Default descriptors when null are 0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']
 * </p>
 * @param string|null $cwd [optional] <p>
 * The initial working dir for the command. This must be an
 * absolute directory path, or null
 * if you want to use the default value (the working dir of the current
 * PHP process)
 * </p>
 * @param array|null $options [optional] <p>
 * Allows you to specify additional options.
 * @link https://www.php.net/manual/en/function.proc-open.php proc_open
 * </p>
 * @return false|string|null Returns the result from the command.
 */
function run_console(string $command, array $descriptors = null, string $cwd = null, array $options = null)
{
    $path = dirname(__FILE__, 3);
    $descriptors = $descriptors ?? [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']];
    $handle = proc_open("cd '$path' && bash -c 'exec -a ServerCPP $command'", $descriptors, $pipes, $cwd, null, $options);

    return stream_get_contents($pipes[1]);
}

/**
 * Log to installer.log in the install folder
 * @param string $log_msg the message to log
 * @return void No output.
 */
function wh_log(string $log_msg)
{
    $log_filename = 'logs';
    if (!file_exists($log_filename)) {
        // create directory/folder uploads.
        mkdir($log_filename, 0777, true);
    }
    $log_file_data = $log_filename . '/installer.log';
    // if you don't add `FILE_APPEND`, the file will be erased each time you add a log
    file_put_contents($log_file_data, '[' . date('h:i:s') . '] ' . $log_msg . "\n", FILE_APPEND);
}

/**
 * Generate a random string
 * @param int $length The length of the random string
 * @return string The randomly generated string.
 */
function generateRandomString(int $length = 8): string
{
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }

    return $randomString;
}

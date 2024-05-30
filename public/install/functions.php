<?php
require '../../vendor/autoload.php';
require 'dotenv.php';

use DevCoder\DotEnv;
use Illuminate\Encryption\Encrypter;
use Illuminate\Support\Str;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

if (!file_exists('../../.env')) {
    echo run_console('cp .env.example .env');
}

(new DotEnv(dirname(__FILE__, 3) . '/.env'))->load();

$required_extensions = ['openssl', 'gd', 'mysql', 'PDO', 'mbstring', 'tokenizer', 'bcmath', 'xml', 'curl', 'zip', 'intl'];

$requirements = [
    'minPhp' => '8.1',
    'maxPhp' => '8.4', // This version is not supported
    'mysql' => '5.7.22',
];

/**
 * Check if the minimum PHP version is present
 * @return string 'OK' on success and 'not OK' on failure.
 */
function checkPhpVersion(): string
{
    global $requirements;

    wh_log('php version: ' . phpversion(), 'debug');
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
    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443;
    wh_log('https:', 'debug', (array)$isHttps);
    return $isHttps;
}

/**
 * Check if MySQL is installed and runs the correct version using a shell command
 * @return mixed|string 'OK' if required version is met, returns MySQL version if not met.
 */
function getMySQLVersion(): mixed
{
    global $requirements;

    wh_log('attempting to get mysql version', 'debug');

    $output = shell_exec('mysql -V') ?? '';
    preg_match('@[0-9]+\.[0-9]+\.[0-9]+@', $output, $version);

    $versionoutput = $version[0] ?? '0';
    wh_log('mysql version: ' . $versionoutput, 'debug');

    return intval($versionoutput) > intval($requirements['mysql']) ? 'OK' : $versionoutput;
}

/**
 * Check if zip is installed using a shell command
 * @return string 'OK' on success and 'not OK' on failure.
 */
function getZipVersion(): string
{
    wh_log('attempting to get zip version', 'debug');
    $output = shell_exec('zip  -v') ?? '';
    preg_match('@[0-9]+\.[0-9]+\.[0-9]+@', $output, $version);

    $versionoutput = $version[0] ?? 0;
    wh_log('zip version: ' . $versionoutput, 'debug');

    return $versionoutput != 0 ? 'OK' : 'not OK';
}

/**
 * Check if git is installed using a shell command
 * @return string 'OK' on success and 'not OK' on failure.
 */
function getGitVersion(): string
{
    wh_log('attempting to get git version', 'debug');
    $output = shell_exec('git  --version') ?? '';
    preg_match('@[0-9]+\.[0-9]+\.[0-9]+@', $output, $version);

    $versionoutput = $version[0] ?? 0;
    wh_log('git version: ' . $versionoutput, 'debug');

    return $versionoutput != 0 ? 'OK' : 'not OK';
}

/**
 * Check if tar is installed using a shell command
 * @return string 'OK' on success and 'not OK' on failure.
 */
function getTarVersion(): string
{
    wh_log('attempting to get tar version', 'debug');
    $output = shell_exec('tar  --version') ?? '';
    preg_match('@[0-9]+\.[0-9]+@', $output, $version);

    $versionoutput = $version[0] ?? 0;
    wh_log('tar version: ' . $versionoutput, 'debug');

    return $versionoutput != 0 ? 'OK' : 'not OK';
}

/**
 * Check all extensions to see if they have loaded or not
 * @return array Returns an array of extensions that failed to load.
 */
function checkExtensions(): array
{
    global $required_extensions;

    wh_log('checking extensions', 'debug');

    $not_ok = [];
    $extentions = get_loaded_extensions();

    foreach ($required_extensions as $ext) {
        if (!preg_grep('/^(?=.*' . $ext . ').*$/', $extentions)) {
            array_push($not_ok, $ext);
        }
    }

    wh_log('loaded extensions:', 'debug', $extentions);
    wh_log('failed extensions:', 'debug', $not_ok);
    return $not_ok;
}

function removeQuotes($string)
{
    return str_replace('"', "", $string);
}

/**
 * Sets the environment variable into the env file
 * @param string $envKey The environment key to set or modify
 * @param string $envValue The environment variable to set
 * @return bool true on success or false on failure.
 */
function setenv($envKey, $envValue)
{
    $envFile = dirname(__FILE__, 3) . '/.env';
    $str = file_get_contents($envFile);

    $str .= "\n"; // In case the searched variable is in the last line without \n
    $keyPosition = strpos($str, "{$envKey}=");
    $endOfLinePosition = strpos($str, PHP_EOL, $keyPosition);
    $oldLine = substr($str, $keyPosition, $endOfLinePosition - $keyPosition);
    $str = str_replace($oldLine, "{$envKey}={$envValue}", $str);
    $str = substr($str, 0, -1);

    $fp = fopen($envFile, 'w');
    fwrite($fp, $str);
    fclose($fp);
}

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
    wh_log('running command: ' . $command, 'debug');

    $path = dirname(__FILE__, 3);
    $descriptors = $descriptors ?? [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']];
    if (file_exists('/.dockerenv')) {
        $handle = proc_open("cd '$path' && bash -c '$command'", $descriptors, $pipes, $cwd, null, $options);
    } else {
        $handle = proc_open("cd '$path' && bash -c 'exec -a ServerCPP $command'", $descriptors, $pipes, $cwd, null, $options);
    }

    $output = stream_get_contents($pipes[1]);
    $exit_code = proc_close($handle);

    if ($exit_code > 0) {
        wh_log('command result: ' . $output, 'error');
        throw new Exception("There was an error after running command `$command`", $exit_code);
    } else {
        return $output;
    }
}

/**
 * Log to the default laravel.log file
 * @param string $message The message to log
 * @param string $level The log level to use (debug, info, warning, error, critical)
 * @param array $context [optional] The context to log extra information
 * @return void
 */
function wh_log(string $message, string $level = 'info', array $context = []): void
{
    $formatter = new LineFormatter(null, null, true, true);
    $stream = new StreamHandler(dirname(__FILE__, 3) . '/storage/logs/installer.log', Logger::DEBUG);
    $stream->setFormatter($formatter);

    $log = new Logger('ControlPanel');
    $log->pushHandler($stream);

    switch (strtolower($level)) {
        case 'debug': // Only log debug messages if APP_DEBUG is true
            if (getenv('APP_DEBUG') === false) return;
            $log->debug($message, $context);
            break;
        case 'info':
            $log->info($message, $context);
            break;
        case 'warning':
            $log->warning($message, $context);
            break;
        case 'error':
            $log->error($message, $context);
            break;
        case 'critical':
            $log->critical($message, $context);
            break;
    }
    // Prevent memory leaks by resetting the logger
    $log->reset();
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

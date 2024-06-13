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

function checkPhpVersion()
{
    global $requirements;
    if (version_compare(phpversion(), $requirements['minPhp'], '>=') && version_compare(phpversion(), $requirements['maxPhp'], '<=')) {
        return 'OK';
    }

    return 'not OK';
}
function checkWriteable()
{
    return is_writable('../../.env');
}
function checkHTTPS()
{
    return (! empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || $_SERVER['SERVER_PORT'] == 443;
}

function getMySQLVersion()
{
    global $requirements;

    $output = shell_exec('mysql -V');
    preg_match('@[0-9]+\.[0-9]+\.[0-9]+@', $output, $version);

    $versionoutput = $version[0] ?? '0';

    return intval($versionoutput) > intval($requirements['mysql']) ? 'OK' : $versionoutput;
}

function getZipVersion()
{
    $output = shell_exec('zip  -v');
    preg_match('@[0-9]+\.[0-9]+\.[0-9]+@', $output, $version);

    $versionoutput = $version[0] ?? 0;

    return $versionoutput != 0 ? 'OK' : 'not OK';
}

function getGitVersion()
{
    $output = shell_exec('git  --version');
    preg_match('@[0-9]+\.[0-9]+\.[0-9]+@', $output, $version);

    $versionoutput = $version[0] ?? 0;

    return $versionoutput != 0 ? 'OK' : 'not OK';
}

function getTarVersion()
{
    $output = shell_exec('tar  --version');
    preg_match('@[0-9]+\.[0-9]+@', $output, $version);

    $versionoutput = $version[0] ?? 0;

    return $versionoutput != 0 ? 'OK' : 'not OK';
}

function checkExtensions()
{
    global $required_extentions;

    $not_ok = [];
    $extentions = get_loaded_extensions();

    foreach ($required_extentions as $ext) {
        if (! preg_grep('/^(?=.*'.$ext.').*$/', $extentions)) {
            array_push($not_ok, $ext);
        }
    }

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

function getEnvironmentValue($envKey)
{
    $envFile = dirname(__FILE__, 3).'/.env';
    $str = file_get_contents($envFile);

    $str .= "\n"; // In case the searched variable is in the last line without \n
    $keyPosition = strpos($str, "{$envKey}=");
    $endOfLinePosition = strpos($str, PHP_EOL, $keyPosition);
    $oldLine = substr($str, $keyPosition, $endOfLinePosition - $keyPosition);
    $value = substr($oldLine, strpos($oldLine, '=') + 1);

    return $value;
}

function run_console($command)
{
    $path = dirname(__FILE__, 3);
    $descriptors = $descriptors ?? [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']];
    $handle = proc_open("cd '$path' && bash -c 'exec -a ServerCPP $command'", $descriptors, $pipes, $cwd, null, $options);
    $output = stream_get_contents($pipes[1]);
    $exit_code = proc_close($handle);

    if ($exit_code > 0) {
        wh_log('command result: ' . $output, 'error');
        throw new Exception("There was an error after running command `$command`", $exit_code);
        return $output;
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

function generateRandomString($length = 8)
{
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }

    return $randomString;
}

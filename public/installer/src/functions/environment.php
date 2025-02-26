<?php

/**
* Sets the environment variable into the env file
 * @param string $envKey The environment key to set or modify
 * @param string $envValue The environment variable to set
 * @return bool true on success or false on failure.
 */
function setenv($envKey, $envValue)
{
    $rootDirectory = dirname(__DIR__, 4);
    $envFile = $rootDirectory . '/.env';

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

$required_extensions = ['openssl', 'gd', 'mysql', 'PDO', 'mbstring', 'tokenizer', 'bcmath', 'xml', 'curl', 'zip', 'intl', 'redis'];

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

$requirements = [
    'minPhp' => '8.2',
    'maxPhp' => '8.5', // This version is not supported
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
 * Check if the environment file is writable
 * @return bool Returns true on writable and false on not writable.
 */
function checkWriteable(): bool
{
    return is_writable('../../.env');
}

?>

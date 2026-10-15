<?php

/**
 * Run a shell command
 * @param array $command Tokenized command arguments.
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
 * @return string Returns stdout from the command.
 */
function run_console(array $command, ?array $descriptors = null, ?string $cwd = null, ?array $options = null, bool $logging=true): string
{
    if ($command === []) {
        throw new InvalidArgumentException('Command cannot be empty.');
    }

    if ($logging) {
        $printable = implode(' ', array_map(static fn ($arg) => escapeshellarg((string) $arg), mask_sensitive_command_args($command)));
        wh_log('running command: ' . $printable, 'debug');
    }

    $path = dirname(__DIR__, 4);
    $descriptors = $descriptors ?? [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']];
    $cwd = $cwd ?? $path;

    $handle = proc_open($command, $descriptors, $pipes, $cwd, null, $options);
    if (!is_resource($handle)) {
        throw new RuntimeException('Failed to start command process.');
    }

    fclose($pipes[0]);
    $stdout = stream_get_contents($pipes[1]) ?: '';
    $stderr = stream_get_contents($pipes[2]) ?: '';
    fclose($pipes[1]);
    fclose($pipes[2]);

    $exit_code = proc_close($handle);

    if ($exit_code !== 0) {
        $combined = trim($stdout . PHP_EOL . $stderr);
        wh_log('command result: ' . $combined, 'error');
        throw new RuntimeException("Command failed with exit code {$exit_code}.");
    }

    return $stdout;
}

/**
 * Mask sensitive command arguments before logging.
 *
 * @param array $command
 * @return array
 */
function mask_sensitive_command_args(array $command): array
{
    $masked = $command;

    // php artisan settings:set <group> <key> <value>
    if (
        isset($masked[0], $masked[1], $masked[2], $masked[3], $masked[4], $masked[5]) &&
        $masked[0] === 'php' &&
        $masked[1] === 'artisan' &&
        $masked[2] === 'settings:set'
    ) {
        $group = (string) $masked[3];
        $key = (string) $masked[4];

        $sensitiveSettings = [
            'MailSettings' => ['mail_password'],
            'PterodactylSettings' => ['admin_token', 'user_token'],
        ];

        if (isset($sensitiveSettings[$group]) && in_array($key, $sensitiveSettings[$group], true)) {
            $masked[5] = '***REDACTED***';
        }
    }

    return $masked;
}

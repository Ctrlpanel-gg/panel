<?php

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
function run_console(string $command, array $descriptors = null, string $cwd = null, array $options = null, bool $logging=true)
{
    if ($logging) {
        wh_log('running command: ' . $command, 'debug');
    }

    $path = dirname(__DIR__, 4);
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

?>

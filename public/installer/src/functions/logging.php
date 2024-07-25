<?php

use DevCoder\DotEnv;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

(new DotEnv(dirname(__FILE__, 5) . '/.env'))->load();

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
    $stream = new StreamHandler(dirname(__DIR__, 4) . '/storage/logs/installer.log', Logger::DEBUG);
    $stream->setFormatter($formatter);

    $log = new Logger('CtrlPanel');
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

?>

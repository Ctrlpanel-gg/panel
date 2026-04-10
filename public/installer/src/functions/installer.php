<?php

$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";

function get_host(): string
{
    $serverName = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? '';
    $serverPort = $_SERVER['SERVER_PORT'] ?? '';
    $serverName = trim($serverName);
    $serverName = preg_replace('/[\s\x00-\x1f\x7f]+/', '', $serverName);

    if (preg_match('/^([a-zA-Z0-9.-]+)(?::([0-9]+))?$/', $serverName, $matches)) {
        $hostname = $matches[1];
        $port = $matches[2] ?? $serverPort;

        if ($port && $port !== '80' && $port !== '443') {
            return $hostname . ':' . $port;
        }

        return $hostname;
    }

    // Fallback safe host
    if (filter_var($serverName, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME)) {
        return $serverName;
    }

    return 'localhost';
}

$host = get_host();

function send_error_message(string $message): void
{
    global $protocol, $host;

    $_SESSION['error-message'] = $message;
    $escapedMessage = rawurlencode($message);
    header("Location: {$protocol}://{$host}/installer/index.php?message={$escapedMessage}");
    exit();
}

function next_step(): void
{
    global $protocol, $host;

    $_SESSION['current_installation_step']++;
    header("Location: {$protocol}://{$host}/installer/index.php");
    exit();
}

?>

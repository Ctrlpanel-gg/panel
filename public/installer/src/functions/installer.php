<?php

$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";

function get_host(): string
{
    $serverName = $_SERVER['SERVER_NAME'] ?? '';
    $serverPort = $_SERVER['SERVER_PORT'] ?? '';

    // Only include port if it's not the default port for the protocol
    if ($serverPort && $serverPort !== '80' && $serverPort !== '443') {
        return $serverName . ':' . $serverPort;
    }

    return $serverName;
}

$host = get_host();

function send_error_message(string $message): void
{
    global $protocol, $host;

    $_SESSION['error-message'] = $message;
    header("Location: {$protocol}://{$host}/installer/index.php");
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

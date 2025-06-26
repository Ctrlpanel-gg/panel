<?php

$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
$host = $_SERVER['HTTP_HOST'];

function send_error_message(string $message): void
{
    $_SESSION['error-message'] = $message;
    header("LOCATION: {$protocol}://{$host}/installer/index.php");
    exit();
}

function next_step(): void
{
    $_SESSION['current_installation_step']++;
    header("LOCATION: {$protocol}://{$host}/installer/index.php");
    exit();
}

?>

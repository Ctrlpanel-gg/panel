<?php

function send_error_message(string $message): void
{
    $_SESSION['error-message'] = $message;
    header("LOCATION: index.php");
    exit();
}

function next_step(): void
{
    $_SESSION['current_installation_step']++;
    header("LOCATION: index.php");
    exit();
}

?>

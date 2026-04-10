<?php

function send_error_message(string $message): void
{
    $_SESSION['error-message'] = $message;
    header('Location: /installer/index.php');
    exit();
}

function next_step(): void
{
    $_SESSION['current_installation_step']++;
    header('Location: /installer/index.php');
    exit();
}

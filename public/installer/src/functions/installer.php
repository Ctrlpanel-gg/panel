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

/**
 * Check whether an install started by this installer is still in progress.
 *
 * @return bool
 */
function installer_install_in_progress(): bool
{
    return file_exists(dirname(__FILE__, 5) . '/install.inprogress');
}

/**
 * Detect whether the configured database is already migrated.
 *
 * @return bool
 */
function installer_detects_existing_installation(): bool
{
    if (filter_var(getenv('INSTALLER_FORCE'), FILTER_VALIDATE_BOOLEAN)) {
        return false;
    }

    $host = getenv('DB_HOST');
    $database = getenv('DB_DATABASE');

    if (!$host || !$database) {
        return false;
    }

    try {
        $connection = new mysqli(
            $host,
            getenv('DB_USERNAME') ?: '',
            getenv('DB_PASSWORD') ?: '',
            $database,
            (int) (getenv('DB_PORT') ?: 3306)
        );
    } catch (mysqli_sql_exception $exception) {
        // Cannot connect yet: fresh install.
        return false;
    }

    try {
        $result = $connection->query('SELECT COUNT(*) AS count FROM migrations');

        if (!$result) {
            return false;
        }

        return ((int) ($result->fetch_assoc()['count'] ?? 0)) > 0;
    } catch (mysqli_sql_exception $exception) {
        return false;
    } finally {
        $connection->close();
    }
}

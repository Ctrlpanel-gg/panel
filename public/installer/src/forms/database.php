<?php

use DevCoder\DotEnv;

(new DotEnv(dirname(__FILE__, 5) . '/.env'))->load();

mysqli_report(MYSQLI_REPORT_STRICT | MYSQLI_REPORT_ALL);

if (isset($_POST['checkDB'])) {
    $values = [
        //SETTINGS::VALUE => REQUEST-VALUE (coming from the html-form)
        'DB_HOST' => 'databasehost',
        'DB_DATABASE' => 'database',
        'DB_USERNAME' => 'databaseuser',
        'DB_PASSWORD' => 'databaseuserpass',
        'DB_PORT' => 'databaseport',
        'DB_CONNECTION' => 'databasedriver',
    ];

    wh_log('Trying to connect to the Database', 'debug');

    try {
        $db = new mysqli($_POST['databasehost'], $_POST['databaseuser'], $_POST['databaseuserpass'], $_POST['database'], $_POST['databaseport']);
    } catch (mysqli_sql_exception $e) {
        wh_log($e->getMessage(), 'error');
        send_error_message($e->getMessage());
        exit();
    }

    foreach ($values as $key => $value) {
        $param = $_POST[$value];
        setenv($key, $param);
    }

    wh_log('Start APP_KEY generation', 'debug');

    try {
        if (!str_contains(getenv('APP_KEY'), 'base64')) {
            $logs = run_console('php artisan key:generate --force');
            wh_log($logs, 'debug');

            wh_log('Created APP_KEY successful', 'debug');
        } else {
            wh_log('Key already exists. Skipping', 'debug');
        }
    } catch (Throwable $th) {
        wh_log('Creating APP_KEY failed', 'error');
        header("LOCATION: index.php?step=3&message=" . $th->getMessage() . " <br>Please check the installer.log file in " . dirname(__DIR__,4) . '/storage/logs' . "!");
        exit();
    }

    wh_log('Database connection successful', 'debug');
    next_step();
}

if (isset($_POST['feedDB'])) {
    wh_log('Feeding the Database', 'debug');
    $logs = '';

    try {
        $logs .= run_console('php artisan storage:link');
        $logs .= run_console('php artisan migrate --seed --force');
        $logs .= run_console('php artisan db:seed --class=ExampleItemsSeeder --force');
        $logs .= run_console('php artisan db:seed --class=PermissionsSeeder --force');

        wh_log($logs, 'debug');

        wh_log('Feeding the Database successful', 'debug');
        next_step();
    } catch (Throwable $th) {
        wh_log('Feeding the Database failed', 'error');
        send_error_message("Feeding the Database failed");
    }
}

?>

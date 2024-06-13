<?php

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

    wh_log('Database connection successful', 'debug');
    next_step();
}

if (isset($_POST['feedDB'])) {
    wh_log('Feeding the Database', 'debug');
    $logs = '';

    try {
        if (!str_contains(getenv('APP_KEY'), 'base64')) {
            $logs .= run_console('php artisan key:generate --force');
        } else {
            $logs .= "Key already exists. Skipping\n";
        }
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

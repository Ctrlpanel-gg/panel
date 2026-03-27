<?php

use DevCoder\DotEnv;

(new DotEnv(dirname(__FILE__, 5) . '/.env'))->load();

if (isset($_POST['createUser'])) {
    wh_log('Getting Pterodactyl User', 'debug');

    try {
        $db = new mysqli(getenv('DB_HOST'), getenv('DB_USERNAME'), getenv('DB_PASSWORD'), getenv('DB_DATABASE'), getenv('DB_PORT'));
    } catch (Throwable $th) {
        wh_log($th->getMessage(), 'error');
        send_error_message("Could not connect to the Database");
        exit();
    }

    $pteroID = $_POST['pteroID'];
    $pass = $_POST['pass'];
    $repass = $_POST['repass'];

    try {
        $panelUrl = run_console("php artisan settings:get 'PterodactylSettings' 'panel_url' --sameline");
        $adminToken = run_console("php artisan settings:get 'PterodactylSettings' 'admin_token' --sameline");
    } catch (Throwable $th) {
        wh_log("Getting Pterodactyl information failed.", 'error');
        send_error_message($th->getMessage() . " <br>Please check the installer.log file in " . dirname(__DIR__,4) . '/storage/logs' . "!");

        exit();
    }

    $panelApiUrl = $panelUrl . '/api/application/users/' . $pteroID;

    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $panelApiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Accept: application/json',
        'Content-Type: application/json',
        'Authorization: Bearer ' . $adminToken,
    ]);
    $response = curl_exec($ch);
    $result = json_decode($response, true);
    curl_close($ch);

    if ($pass !== $repass) {
        send_error_message("The Passwords did not match!");
        exit();
    }

    if (array_key_exists('errors', $result)) {
        send_error_message("Could not find the user with pterodactyl ID" . $pteroID);
        exit();
    }

    $mail = $result['attributes']['email'];
    $name = $result['attributes']['username'];
    $pass = password_hash($pass, PASSWORD_DEFAULT);

    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $panelApiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Accept: application/json',
        'Content-Type: application/json',
        'Authorization: Bearer ' . $adminToken,
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, [
        'email' => $mail,
        'username' => $name,
        'first_name' => $name,
        'last_name' => $name,
        'password' => $pass,
    ]);
    $response = curl_exec($ch);
    $result = json_decode($response, true);
    curl_close($ch);

    $random = substr(str_shuffle('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 8); // random referal

    $creditsInDatabase = 250000; // default

    try {
        $settingValue = run_console("php artisan settings:get 'UserSettings' 'initial_credits' --sameline");
        if (!empty($settingValue) && is_numeric($settingValue)) {
            $creditsInDatabase = (int) $settingValue;
            wh_log('Successfully retrieved initial_credits from UserSettings: ' . $creditsInDatabase, 'debug');
        } else {
            wh_log('UserSettings initial_credits is empty or invalid, using default: 250000', 'warning');
            $creditsInDatabase = 250000;
        }
    } catch (Throwable $th) {
        wh_log('Could not retrieve initial_credits setting, using default of 250000', 'warning');
        wh_log('Error: ' . $th->getMessage(), 'warning');
        $creditsInDatabase = 250000;
    }

    try {
        $userInsert = $db->prepare(
            'INSERT INTO `users` (`name`, `credits`, `server_limit`, `pterodactyl_id`, `email`, `password`, `created_at`, `referral_code`) VALUES (?, ?, 1, ?, ?, ?, CURRENT_TIMESTAMP, ?)'
        );
        $userInsert->bind_param('siisss', $name, $creditsInDatabase, $pteroID, $mail, $pass, $random);
        $userInsert->execute();

        $newUserId = $db->insert_id;

        $roleInsert = $db->prepare(
            'INSERT INTO `model_has_roles` (`role_id`, `model_type`, `model_id`) VALUES (1, ?, ?)'
        );
        $modelType = 'App\\Models\\User';
        $roleInsert->bind_param('si', $modelType, $newUserId);
        $roleInsert->execute();

        wh_log('Created user with Email ' . $mail . ' and pterodactyl ID ' . $pteroID);
        next_step();
    } catch (Throwable $th) {
        wh_log($th->getMessage(), 'error');
        if (str_contains($th->getMessage(), 'Duplicate entry')) {
            send_error_message("User already exists in CtrlPanel\'s Database");
        } else {
            send_error_message("Something went wrong when communicating with the Database.");
        }
        exit();
    }
}

?>

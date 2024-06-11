<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;
use Predis\Client;

require 'phpmailer/Exception.php';
require 'phpmailer/PHPMailer.php';
require 'phpmailer/SMTP.php';

include 'functions.php';

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
        header('LOCATION: index.php?step=2&message=' . $e->getMessage());
        exit();
    }


    foreach ($values as $key => $value) {
        $param = $_POST[$value];
        // if ($key == "DB_PASSWORD") {
        //    $param = '"' . $_POST[$value] . '"';
        // }
        setenv($key, $param);
    }

    wh_log('Database connection successful', 'debug');
    header('LOCATION: index.php?step=2.5');
}

if (isset($_POST['feedDB'])) {
    wh_log('Feeding the Database', 'debug');
    $logs = '';

    try {
        //$logs .= run_console(setenv('COMPOSER_HOME', dirname(__FILE__, 3) . '/vendor/bin/composer'));
        //$logs .= run_console('composer install --no-dev --optimize-autoloader');
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
        header('LOCATION: index.php?step=2.6');
    } catch (Throwable $th) {
        wh_log('Feeding the Database failed', 'error');
        header("LOCATION: index.php?step=2.5&message=" . $th->getMessage() . " <br>Please check the installer.log file in /var/www/controlpanel/storage/logs !");
    }
}

if (isset($_POST['redisSetup'])) {
    wh_log('Setting up Redis', 'debug');
    $redisHost = $_POST['redishost'];
    $redisPort = $_POST['redisport'];
    $redisPassword = $_POST['redispassword'];

    $redisClient = new Client([
        'host'     => $redisHost,
        'port'     => $redisPort,
        'password' => $redisPassword,
        'timeout'  => 1.0,
    ]);

    try {
        $redisClient->ping();

        setenv('MEMCACHED_HOST', $redisHost);
        setenv('REDIS_HOST', $redisHost);
        setenv('REDIS_PORT', $redisPort);
        setenv('REDIS_PASSWORD', ($redisPassword === '' ? 'null' : $redisPassword));

        wh_log('Redis connection successful. Settings updated.', 'debug');
        header('LOCATION: index.php?step=3');
    } catch (Throwable $th) {
        wh_log('Redis connection failed. Settings updated.', 'debug');
        header("LOCATION: index.php?step=2.6&message=Please check your credentials!<br>" . $th->getMessage());
    }
}

if (isset($_POST['checkGeneral'])) {
    wh_log('setting app settings', 'debug');
    $appname = '"' . $_POST['name'] . '"';
    $appurl = $_POST['url'];

    if (substr($appurl, -1) === '/') {
        $appurl = substr_replace($appurl, '', -1);
    }

    setenv('APP_NAME', $appname);
    setenv('APP_URL', $appurl);

    wh_log('App settings set', 'debug');
    header('LOCATION: index.php?step=4');
}

if (isset($_POST['checkSMTP'])) {
    wh_log('Checking SMTP Settings', 'debug');
    try {
        $mail = new PHPMailer(true);

        //Server settings
        // Send using SMTP
        $mail->isSMTP();
        $mail->Host = $_POST['host'];
        // Enable SMTP authentication
        $mail->SMTPAuth = true;
        $mail->Username = $_POST['user'];
        $mail->Password = $_POST['pass'];
        $mail->SMTPSecure = $_POST['encryption'];
        $mail->Port = (int) $_POST['port'];

        // Test E-mail metadata
        $mail->setFrom($_POST['user'], $_POST['user']);
        $mail->addAddress($_POST['user'], $_POST['user']);

        // Content
        // Set email format to HTML
        $mail->isHTML(true);
        $mail->Subject = 'It Worked! - Test E-Mail from Ctrlpanel.gg';
        $mail->Body = 'Your E-Mail Settings are correct!';

        $mail->send();
    } catch (Exception $e) {
        wh_log($mail->ErrorInfo, 'error');
        header('LOCATION: index.php?step=4&message=Something went wrong while sending test E-Mail!<br>' . $mail->ErrorInfo);
        exit();
    }

    wh_log('SMTP Settings are correct', 'debug');
    wh_log('Updating Database', 'debug');
    $db = new mysqli(getenv('DB_HOST'), getenv('DB_USERNAME'), getenv('DB_PASSWORD'), getenv('DB_DATABASE'), getenv('DB_PORT'));
    if ($db->connect_error) {
        wh_log($db->connect_error, 'error');
        header('LOCATION: index.php?step=4&message=Could not connect to the Database: ');
        exit();
    }
    $values = [
        'mail_mailer' => $_POST['method'],
        'mail_host' => $_POST['host'],
        'mail_port' => $_POST['port'],
        'mail_username' => $_POST['user'],
        'mail_password' => $_POST['pass'],
        'mail_encryption' => $_POST['encryption'],
        'mail_from_address' => $_POST['user'],
    ];

    foreach ($values as $key => $value) {
        run_console("php artisan settings:set 'MailSettings' '$key' '$value'");
    }

    wh_log('Database updated', 'debug');
    header('LOCATION: index.php?step=5');
}

if (isset($_POST['checkPtero'])) {
    wh_log('Checking Pterodactyl Settings', 'debug');

    $url = $_POST['url'];
    $key = $_POST['key'];
    $clientkey = $_POST['clientkey'];

    if (substr($url, -1) === '/') {
        $url = substr_replace($url, '', -1);
    }

    $callpteroURL = $url . '/api/client/account';
    $call = curl_init();

    curl_setopt($call, CURLOPT_URL, $callpteroURL);
    curl_setopt($call, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($call, CURLOPT_HTTPHEADER, [
        'Accept: Application/vnd.pterodactyl.v1+json',
        'Content-Type: application/json',
        'Authorization: Bearer ' . $clientkey,
    ]);
    $callresponse = curl_exec($call);
    $callresult = json_decode($callresponse, true);
    curl_close($call); // Close the connection

    $pteroURL = $url . '/api/application/users';
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $pteroURL);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Accept: Application/vnd.pterodactyl.v1+json',
        'Content-Type: application/json',
        'Authorization: Bearer ' . $key,
    ]);
    $response = curl_exec($ch);
    $result = json_decode($response, true);
    curl_close($ch); // Close the connection

    if (!is_array($result) and $result['errors'][0] !== null) {
        header('LOCATION: index.php?step=5&message=Couldn\'t connect to Pterodactyl. Make sure your API key has all read and write permissions!');
        wh_log('API CALL ERROR: ' . $result['errors'][0]['code'], 'error');
        exit();
    } elseif (!is_array($callresult) and $callresult['errors'][0] !== null or $callresult['attributes']['admin'] == false) {
        header('LOCATION: index.php?step=5&message=Your ClientAPI Key is wrong or the account is not an admin!');
        wh_log('API CALL ERROR: ' . $callresult['errors'][0]['code'], 'error');
        exit();
    } else {
        wh_log('Pterodactyl Settings are correct', 'debug');

        try {
            run_console("php artisan settings:set 'PterodactylSettings' 'panel_url' '$url'");
            run_console("php artisan settings:set 'PterodactylSettings' 'admin_token' '$key'");
            run_console("php artisan settings:set 'PterodactylSettings' 'user_token' '$clientkey'");
            wh_log('Database updated', 'debug');
            header('LOCATION: index.php?step=6');
        } catch (Throwable $th) {
            wh_log("Setting Pterodactyl information failed.", 'error');
            header("LOCATION: index.php?step=5&message=" . $th->getMessage() . " <br>Please check the installer.log file in /var/www/controlpanel/storage/logs!");
        }
    }
}

if (isset($_POST['createUser'])) {
    wh_log('Getting Pterodactyl User', 'debug');
    $db = new mysqli(getenv('DB_HOST'), getenv('DB_USERNAME'), getenv('DB_PASSWORD'), getenv('DB_DATABASE'), getenv('DB_PORT'));
    if ($db->connect_error) {
        wh_log($db->connect_error, 'error');
        header('LOCATION: index.php?step=6&message=Could not connect to the Database');
        exit();
    }

    $pteroID = $_POST['pteroID'];
    $pass = $_POST['pass'];
    $repass = $_POST['repass'];

    try {
        $panel_url = run_console("php artisan settings:get 'PterodactylSettings' 'panel_url' --sameline");
        $admin_token = run_console("php artisan settings:get 'PterodactylSettings' 'admin_token' --sameline");
        wh_log('Database updated', 'debug');
        header('LOCATION: index.php?step=6');
    } catch (Throwable $th) {
        wh_log("Getting Pterodactyl information failed.", 'error');
        header("LOCATION: index.php?step=5&message=" . $th->getMessage() . " <br>Please check the installer.log file in /var/www/controlpanel/storage/logs!");
    }

    $panel_api_url = $panel_url . '/api/application/users/' . $pteroID;

    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $panel_api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Accept: application/json',
        'Content-Type: application/json',
        'Authorization: Bearer ' . $admin_token,
    ]);
    $response = curl_exec($ch);
    $result = json_decode($response, true);
    curl_close($ch); // Close the connection

    if (!$result['attributes']['email']) {
        header('LOCATION: index.php?step=6&message=Could not find the user with pterodactyl ID ' . $pteroID);
        exit();
    }
    if ($pass !== $repass) {
        header('LOCATION: index.php?step=6&message=The Passwords did not match!');
        exit();
    }

    $mail = $result['attributes']['email'];
    $name = $result['attributes']['username'];
    $pass = password_hash($pass, PASSWORD_DEFAULT);

    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $panel_api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Accept: application/json',
        'Content-Type: application/json',
        'Authorization: Bearer ' . $admin_token,
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
    curl_close($ch); // Close the connection

    if (!is_array($result) or in_array($result['errors'][0]['code'], $result)) {
        header('LOCATION: index.php?step=5&message=Couldn\'t connect to Pterodactyl. Make sure your API key has all read and write permissions!');
        exit();
    }

    $random = generateRandomString();

    $query1 = 'INSERT INTO `' . getenv('DB_DATABASE') . "`.`users` (`name`, `role`, `credits`, `server_limit`, `pterodactyl_id`, `email`, `password`, `created_at`, `referral_code`) VALUES ('$name', 'admin', '250', '1', '$pteroID', '$mail', '$pass', CURRENT_TIMESTAMP, '$random')";
    $query2 = "INSERT INTO `" . getenv('DB_DATABASE') . "`.`model_has_roles` (`role_id`, `model_type`, `model_id`) VALUES ('1', 'App\\\Models\\\User', '1')";
    if ($db->query($query1) && $db->query($query2)) {
        wh_log('Created user with Email ' . $mail . ' and pterodactyl ID ' . $pteroID, 'info');
        header('LOCATION: index.php?step=7');
    } else {
        wh_log($db->error, 'error');
        header('LOCATION: index.php?step=6&message=Something went wrong when communicating with the Database');
    }
}

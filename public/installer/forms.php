<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;
use Predis\Client;

require './src/phpmailer/Exception.php';
require './src/phpmailer/PHPMailer.php';
require './src/phpmailer/SMTP.php';

if (isset($_POST['timezoneConfig'])) {
    wh_log('Setting up Timezone', 'debug');
    $timezone = $_POST['timezone'];

    setenv('APP_TIMEZONE', $timezone);

    wh_log('Timezone set: ' . $timezone, 'debug');
    header('LOCATION: index.php?step=3');
}

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
        header('LOCATION: index.php?step=3&message=' . $e->getMessage());
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
    header('LOCATION: index.php?step=3.5');
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
        header('LOCATION: index.php?step=4');
    } catch (Throwable $th) {
        wh_log('Feeding the Database failed', 'error');
        header("LOCATION: index.php?step=3.5&message=" . $th->getMessage() . " <br>Please check the installer.log file in /var/www/controlpanel/storage/logs !");
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
        header('LOCATION: index.php?step=5');
    } catch (Throwable $th) {
        wh_log('Redis connection failed. Settings updated.', 'debug');
        header("LOCATION: index.php?step=4&message=Please check your credentials!<br>" . $th->getMessage());
    }
}

if (isset($_POST['checkGeneral'])) {
    wh_log('setting app settings', 'debug');
    $appname = '"' . $_POST['name'] . '"';
    $appurl = $_POST['url'];

    $parsedUrl = parse_url($appurl);

    if (!isset($parsedUrl['scheme'])) {
        header('LOCATION: index.php?step=5&message=Please set an URL Scheme like "https://"!');
        exit();
    }

    if (!isset($parsedUrl['host'])) {
        header('LOCATION: index.php?step=5&message=Please set an valid URL host like "https://ctrlpanel.example.com"!');
        exit();
    }

    $appurl = $parsedUrl['scheme'] . '://' . $parsedUrl['host'];

    setenv('APP_NAME', $appname);
    setenv('APP_URL', $appurl);

    wh_log('App settings set', 'debug');
    header('LOCATION: index.php?step=6');
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
        header('LOCATION: index.php?step=6&message=Something went wrong while sending test E-Mail!<br>' . $mail->ErrorInfo);
        exit();
    }

    wh_log('SMTP Settings are correct', 'debug');
    wh_log('Updating Database', 'debug');
    $db = new mysqli(getenv('DB_HOST'), getenv('DB_USERNAME'), getenv('DB_PASSWORD'), getenv('DB_DATABASE'), getenv('DB_PORT'));
    if ($db->connect_error) {
        wh_log($db->connect_error, 'error');
        header('LOCATION: index.php?step=6&message=Could not connect to the Database: ');
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
    header('LOCATION: index.php?step=7');
}

if (isset($_POST['checkPtero'])) {
    wh_log('Checking Pterodactyl Settings', 'debug');

    $url = $_POST['url'];
    $key = $_POST['key'];
    $clientkey = $_POST['clientkey'];

    $parsedUrl = parse_url($url);

    if (!isset($parsedUrl['scheme'])) {
        header('LOCATION: index.php?step=7&message=Please set an URL Scheme like "https://"!');
        exit();
    }

    if (!isset($parsedUrl['host'])) {
        header('LOCATION: index.php?step=7&message=Please set an valid URL host like "https://panel.example.com"!');
        exit();
    }

    $url = $parsedUrl['scheme'] . '://' . $parsedUrl['host'];

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
    curl_close($call);

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
    curl_close($ch);

    if (!is_array($result)) {
        wh_log('No array in response found', 'error');
        header('LOCATION: index.php?step=7&message=An unknown Error occured, please try again!');
    }

    if (array_key_exists('errors', $result) && $result['errors'][0]['detail'] === 'This action is unauthorized.') {
        wh_log('API CALL ERROR: ' . $result['errors'][0]['code'], 'error');
        header('LOCATION: index.php?step=7&message=Couldn\'t connect to Pterodactyl. Make sure your Application API key has all read and write permissions!');
        exit();
    }

    if (array_key_exists('errors', $callresult) && $callresult['errors'][0]['detail'] === 'Unauthenticated.') {
        wh_log('API CALL ERROR: ' . $callresult['errors'][0]['code'], 'error');
        header('LOCATION: index.php?step=7&message=Your ClientAPI Key is wrong or the account is not an admin!');
        exit();
    }

    try {
        run_console("php artisan settings:set 'PterodactylSettings' 'panel_url' '$url'");
        run_console("php artisan settings:set 'PterodactylSettings' 'admin_token' '$key'");
        run_console("php artisan settings:set 'PterodactylSettings' 'user_token' '$clientkey'");
        wh_log('Database updated', 'debug');
        header('LOCATION: index.php?step=8');
    } catch (Throwable $th) {
        wh_log("Setting Pterodactyl information failed.", 'error');
        header("LOCATION: index.php?step=7&message=" . $th->getMessage() . " <br>Please check the installer.log file in /var/www/controlpanel/storage/logs!");
        exit();
    }
}

if (isset($_POST['createUser'])) {
    wh_log('Getting Pterodactyl User', 'debug');

    try {
        $db = new mysqli(getenv('DB_HOST'), getenv('DB_USERNAME'), getenv('DB_PASSWORD'), getenv('DB_DATABASE'), getenv('DB_PORT'));
    } catch (Throwable $th) {
        wh_log($th->getMessage(), 'error');
        header('LOCATION: index.php?step=8&message=Could not connect to the Database');
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
        header("LOCATION: index.php?step=7&message=" . $th->getMessage() . " <br>Please check the installer.log file in /var/www/controlpanel/storage/logs!");
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
        header('LOCATION: index.php?step=8&message=The Passwords did not match!');
        exit();
    }

    if (array_key_exists('errors', $result)) {
        header('LOCATION: index.php?step=8&message=Could not find the user with pterodactyl ID ' . $pteroID);
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

    $random = generateRandomString();

    $query1 = 'INSERT INTO `' . getenv('DB_DATABASE') . "`.`users` (`name`, `role`, `credits`, `server_limit`, `pterodactyl_id`, `email`, `password`, `created_at`, `referral_code`) VALUES ('$name', 'admin', '250', '1', '$pteroID', '$mail', '$pass', CURRENT_TIMESTAMP, '$random')";
    $query2 = "INSERT INTO `" . getenv('DB_DATABASE') . "`.`model_has_roles` (`role_id`, `model_type`, `model_id`) VALUES ('1', 'App\\\Models\\\User', '1')";
    try {
        $db->query($query1);
        $db->query($query2);

        wh_log('Created user with Email ' . $mail . ' and pterodactyl ID ' . $pteroID);
        header('LOCATION: index.php?step=9');
    } catch (Throwable $th) {
        wh_log($th->getMessage(), 'error');
        if (str_contains($th->getMessage(), 'Duplicate entry')) {
            header('LOCATION: index.php?step=8&message=User already exists in CtrlPanel\'s Database.');
        } else {
            header('LOCATION: index.php?step=8&message=Something went wrong when communicating with the Database.');
        }
        exit();
    }
}

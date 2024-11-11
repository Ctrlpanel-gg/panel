<?php

use DevCoder\DotEnv;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

(new DotEnv(dirname(__FILE__, 5) . '/.env'))->load();

require './src/phpmailer/Exception.php';
require './src/phpmailer/PHPMailer.php';
require './src/phpmailer/SMTP.php';


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
        send_error_message("Something went wrong while sending test E-Mail!<br>" . $mail->ErrorInfo);
        exit();
    }

    wh_log('SMTP Settings are correct', 'debug');
    wh_log('Updating Database', 'debug');
    $db = new mysqli(getenv('DB_HOST'), getenv('DB_USERNAME'), getenv('DB_PASSWORD'), getenv('DB_DATABASE'), getenv('DB_PORT'));
    if ($db->connect_error) {
        wh_log($db->connect_error, 'error');
        send_error_message("Could not connect to the Database");
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
    next_step();
}

?>
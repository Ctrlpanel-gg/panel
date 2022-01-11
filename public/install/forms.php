<?php 
	use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\SMTP;
    use PHPMailer\PHPMailer\Exception;

    require 'phpmailer/Exception.php';
    require 'phpmailer/PHPMailer.php';
    require 'phpmailer/SMTP.php';
include("functions.php");

if(isset($_POST['checkDB'])){

        $values = [
            //SETTINGS::VALUE => REQUEST-VALUE (coming from the html-form)
            "DB_HOST" => "databasehost",
            "DB_DATABASE" => "database",
            "DB_USERNAME" => "databaseuser",
            "DB_PASSWORD" => "databaseuserpass",
            "DB_PORT" => "databaseport",
            "DB_CONNECTION" => "databasedriver"
        ];



		$db = new mysqli($_POST["databasehost"], $_POST["databaseuser"], $_POST["databaseuserpass"], $_POST["database"], $_POST["databaseport"]);
			if ($db->connect_error) {
				header("LOCATION: index.php?step=2&message=Could not connect to the Database");	
				die();
			}

			foreach ($values as $key => $value) {
				$param = $_POST[$value];
				setEnvironmentValue($key, $param);
			}
			header("LOCATION: index.php?step=3");	
		
	}


if(isset($_POST['checkGeneral'])){

        $values = [
            //SETTINGS::VALUE => REQUEST-VALUE (coming from the html-form)
            "APP_NAME" => "name",
            "APP_URL" => "url"
        ];


			foreach ($values as $key => $value) {
				$param = $_POST[$value];
				setEnvironmentValue($key, $param);
			}
			header("LOCATION: index.php?step=4");	
		
	}

if(isset($_POST['checkSMTP'])){
	try{ 
        $mail = new PHPMailer(true);

        //Server settings
        $mail->isSMTP();                                            // Send using SMTP
        $mail->Host       = $_POST['host'];                    // Set the SMTP server to send through
        $mail->SMTPAuth   = true;                                   // Enable SMTP authentication
        $mail->Username   = $_POST['user'];                     // SMTP username
        $mail->Password   = $_POST['pass'];                               // SMTP password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;         // Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` encouraged
        $mail->Port       = $_POST['port'];                                    // TCP port to connect to, use 465 for `PHPMailer::ENCRYPTION_SMTPS` 

        //Recipients
        $mail->setFrom($_POST['user'], $_POST['user']);
        $mail->addAddress($_POST['user'], $_POST['user']);     // Add a recipient  

        // Content
        $mail->isHTML(true);                                  // Set email format to HTML
        $mail->Subject = 'It Worked!';
        $mail->Body    = "Your E-Mail Settings are correct!";


        $mail->send();
    }catch (Exception $e){
		header("LOCATION: index.php?step=4&message=Something wasnt right when sending the E-Mail!");	
		die();
    }

        $values = [
            //SETTINGS::VALUE => REQUEST-VALUE (coming from the html-form)
            "MAIL_MAILER" => "method",
            "MAIL_HOST" => "host",
			"MAIL_PORT" => "port",
			"MAIL_USERNAME" => "user",
			"MAIL_PASSWORD" => "pass",
			"MAIL_ENCRYPTION" => "encryption",
			"MAIL_FROM_ADDRESS" => "user"
        ];

			foreach ($values as $key => $value) {
				$param = $_POST[$value];
				setEnvironmentValue($key, $param);
			}
			header("LOCATION: index.php?step=5");	

		
		
	}



?>
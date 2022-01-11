<?php 
	use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\SMTP;
    use PHPMailer\PHPMailer\Exception;
    use DevCoder\DotEnv;

    require 'dotenv.php';
    require 'phpmailer/Exception.php';
    require 'phpmailer/PHPMailer.php';
    require 'phpmailer/SMTP.php';


    (new DotEnv(dirname(__FILE__,3)."/.env"))->load();

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

if(isset($_POST['checkPtero'])){
			$url = $_POST['url'];
			$key = $_POST['key'];

			if(substr($url, -1)==="/"){
				$url = substr_replace($url ,"", -1);
			}


			$pteroURL = $url."/api/application/users";
			 $ch = curl_init();

			  curl_setopt($ch, CURLOPT_URL, $pteroURL);
			  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			  curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			  		"Accept: application/json",
			        "Content-Type: application/json",
			        "Authorization: Bearer " . $key
			    ));
			  $response = curl_exec($ch);
			  $result = json_decode($response, true);
			curl_close($ch); // Close the connection


			if(!is_array($result) or in_array($result["errors"][0]["code"],$result)){
				header("LOCATION: index.php?step=5&message=Couldnt connect to Pterodactyl. Make sure your API key has all read and write permissions!");
				die();	
			}else{
			$query1= "UPDATE `dashboard`.`settings` SET `value` = '$url' WHERE (`key` = 'SETTINGS::SYSTEM:PTERODACTYL:URL')";
			$query2= "UPDATE `dashboard`.`settings` SET `value` = '$key' WHERE (`key` = 'SETTINGS::SYSTEM:PTERODACTYL:TOKEN')";

			$db = new mysqli(getenv("DB_HOST"), getenv("DB_USERNAME"), getenv("DB_PASSWORD"), getenv("DB_DATABASE"), getenv("DB_PORT"));
			if ($db->connect_error) {
				header("LOCATION: index.php?step=5&message=Could not connect to the Database");	
				die();
			}

			if($db->query($query1) && $db->query($query2)){
				header("LOCATION: index.php?step=6");	
			}else{
				header("LOCATION: index.php?step=5&message=Something went wrong when communicating with the Database!");	
			}
			}

		
	}


?>
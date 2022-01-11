<?php 
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
?>
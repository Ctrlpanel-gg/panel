<?php

/*
 * ---------CONFIG----------
 *
 * FILL IN THE DATABASE INFORMATION
 */

function generateRandomString($length = 8) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}


echo "ENTER YOUR PTERODACTYL DATABASE HOST: ";
$PTERODACTYL_HOST = trim(fgets(STDIN));
echo "ENTER YOUR PTERODACTYL DATABASE USER: ";
$PTERODACTYL_USER = trim(fgets(STDIN));
echo "ENTER YOUR PTERODACTYL DATABASE PASSWORD: ";
$PTERODACTYL_PASSWORD = trim(fgets(STDIN));
echo "ENTER YOUR PTERODACTYL DATABASE DATABASE NAME: ";
$PTERODACTYL_DATABASE = trim(fgets(STDIN));
$pterodb = new mysqli($PTERODACTYL_HOST, $PTERODACTYL_USER, $PTERODACTYL_PASSWORD, $PTERODACTYL_DATABASE);
if (!$pterodb) {
    die('Connect Error (' . mysqli_connect_errno() . ') '
        . mysqli_connect_error());
}
echo "ENTER YOUR CPGG DATABASE HOST: ";
$CPGG_HOST = trim(fgets(STDIN));
echo "ENTER YOUR CPGG DATABASE USER: ";
$CPGG_USER = trim(fgets(STDIN));
echo "ENTER YOUR CPGG DATABASE PASSWORD: ";
$CPPPG_PASSWORD = trim(fgets(STDIN));
echo "ENTER YOUR CPGG DATABASE DATABASE NAME: ";
$CPGG_DATABASE = trim(fgets(STDIN));

$cpggdb = new mysqli($CPGG_HOST, $CPGG_USER, $CPPPG_PASSWORD, $CPGG_DATABASE);
if (!$cpggdb) {
    die('Connect Error (' . mysqli_connect_errno() . ') '
        . mysqli_connect_error());
}



echo "ENTER THE AMOUNT OF CREDITS A USER SHOULD START WITH (default: 250)";
$init_credits = trim(fgets(STDIN));
if (empty($init_credits)) {
    $init_credits = 250;
}
echo "ENTER THE AMOUNT OF SERVERS A USER SHOULD START WITH (default: 2)";
$serverlimit = trim(fgets(STDIN));
if (empty($serverlimit)) {
    $serverlimit = 2;
}


$userSQL = "SELECT * FROM `users`";
$pteroUserResult = mysqli_query($pterodb, $userSQL);
$cpggUserResult = mysqli_query($cpggdb, $userSQL);

while ($pterouser = $pteroUserResult->fetch_assoc()) {
    $id = $pterouser["id"];
    $username = $pterouser["username"];
    $email = $pterouser['email'];
    $password = $pterouser['password'];
    $now = date("Y-m-d H:i:s");
    $role = 3;
    $referral_code = generateRandomString();
    try {
        if ($pterouser["root_admin"]) {
            $role = 1;
        }
        $checkusersql = mysqli_query($cpggdb, "SELECT * FROM `users` WHERE `email` = '$email'");
        if (mysqli_num_rows($checkusersql) > 0) {
            echo "User ".$email." exists. Skipping! \n";
        } else {


            $sql = "INSERT INTO `users` (`id`, `name`, `role`, `credits`, `server_limit`, `pterodactyl_id`, `avatar`, `email`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`, `ip`, `last_seen`, `discord_verified_at`, `suspended`, `referral_code`) VALUES (NULL, '$username', '$role', '$init_credits', '$serverlimit', '$id', NULL, '$email', NULL, '$password', NULL, '$now', NULL, NULL, NULL, NULL, '0', '$referral_code')";
            $res = mysqli_query($cpggdb, $sql);
            $userSQL = "SELECT * FROM `users` WHERE `email` = '$email'";
            $cpggUserResult = mysqli_query($cpggdb, $userSQL);
            $cpggUser = $cpggUserResult->fetch_assoc();
            $id = $cpggUser["id"];
            $sql = "INSERT INTO `model_has_roles` (`role_id`, `model_type`, `model_id`) VALUES ('$role', 'App\\\Models\\\User', '$id')";
            $res = mysqli_query($cpggdb, $sql);
            echo "User ".$email."  created \n";
        }

    } catch (Exception $e) {
        echo "Fail: " . $e;
    }
}

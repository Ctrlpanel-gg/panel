<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

use DevCoder\DotEnv;
// use Illuminate\Encryption\Encrypter;
// use Illuminate\Support\Str;

require '../../vendor/autoload.php';
require 'dotenv.php';

// Include the function files
require_once './src/functions/installer.php'; // very important
require_once './src/functions/environment.php';
require_once './src/functions/shell.php';
require_once './src/functions/logging.php';
require_once './src/functions/utils.php';

// Include the form files
include './src/forms/timezone.php';
include './src/forms/database.php';
include './src/forms/redis.php';
include './src/forms/dashboard.php';
include './src/forms/smtp.php';
include './src/forms/pterodactyl.php';
include './src/forms/admin.php';

if (file_exists('../../install.lock')) {
    exit("The installation has been completed already. Please delete the File 'install.lock' to re-run");
}

if (!file_exists('../../.env')) {
    echo run_console('cp .env.example .env');
}

(new DotEnv(dirname(__FILE__, 3) . '/.env'))->load();

$viewNames = [
    1 => 'mandatory-checks',
    2 => 'timezone-configuration',
    3 => 'database-configuration',
    4 => 'database-migration',
    5 => 'redis-configuration',
    6 => 'dashboard-configuration',
    7 => 'email-configuration',
    8 => 'pterodactyl-configuration',
    9 => 'admin-creation',
    10 => 'installation-complete',
];

// Prioritize $_GET['step'], then session, then default to 1
$step = isset($_GET['step'])
    ? (int)$_GET['step']  // Convert to integer for safety
    : (isset($_SESSION['installation_step'])
        ? $_SESSION['installation_step']
        : 1);

// Update session with the current step
$_SESSION['installation_step'] = $step;

$viewName = $viewNames[$step];  // Get the appropriate view name

// Load the layout and the specific view file
include './views/layout-top.php';
include "./views/{$viewName}.php";
include './views/layout-bottom.php';

// setting / reseting the error message
$_SESSION['error-message'] = null;

?>
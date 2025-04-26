<?php
// report all error
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

if (!file_exists('../../.env')) {
    echo shell_exec('cp ../../.env.example ../../.env');
}

use DevCoder\DotEnv;

// Include systems
require_once '../../vendor/autoload.php';
require_once 'dotenv.php';
require_once './src/functions/installer.php';

// Include the function files
require_once './src/functions/logging.php';
require_once './src/functions/environment.php';
require_once './src/functions/shell.php';
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

// load all the .env value in php env
(new DotEnv(dirname(__FILE__, 3) . '/.env'))->load();

$stepConfig = [
    1 => ['view' => 'mandatory-checks', 'is_revertable' => false],
    2 => ['view' => 'timezone-configuration', 'is_revertable' => true],
    3 => ['view' => 'database-configuration', 'is_revertable' => true],
    4 => ['view' => 'database-migration', 'is_revertable' => false],
    5 => ['view' => 'redis-configuration', 'is_revertable' => true],
    6 => ['view' => 'dashboard-configuration', 'is_revertable' => true],
    7 => ['view' => 'email-configuration', 'is_revertable' => true],
    8 => ['view' => 'pterodactyl-configuration', 'is_revertable' => false],
    9 => ['view' => 'admin-creation', 'is_revertable' => false],
    10 => ['view' => 'installation-complete', 'is_revertable' => false],
];

$_SESSION['last_installation_step'] = count($stepConfig);

// Initialize or get the current step:
if (!isset($_SESSION['current_installation_step'])) {
    // Session variable is not set, initialize it in the SESSION
    $_SESSION['current_installation_step'] = 1;
}

if (isset($_GET['step'])) {
    $stepValue = $_GET['step'];
    $currentStep = $_SESSION['current_installation_step'];

    if (strtolower($stepValue) === 'next' && $currentStep < $_SESSION['last_installation_step']) {
        $_SESSION['current_installation_step']++;
        // Redirect to clean URL after processing
        header('Location: /installer/index.php');
        exit;
    }
    elseif (strtolower($stepValue) === 'previous' && $currentStep > 1) {
        if ($stepConfig[$currentStep - 1]['is_revertable']) {
            $_SESSION['current_installation_step']--;
            header('Location: /installer/index.php');
            exit;
        }
    }
    elseif (is_numeric($stepValue)) {
        // Only allow accessing previous or current steps
        if ($stepValue <= $currentStep && $stepValue >= 1 && $stepValue <= $_SESSION['last_installation_step']) {
            $_SESSION['current_installation_step'] = $stepValue;
        }
        header('Location: /installer/index.php');
        exit;
    }
}

$viewName = $stepConfig[$_SESSION['current_installation_step']]['view'];

// Set previous button availability based on step reversibility
$_SESSION['is_previous_button_available'] = $_SESSION['current_installation_step'] > 1 && $stepConfig[$_SESSION['current_installation_step'] - 1]['is_revertable'];

// Load the layout and the specific view file
include './views/layout-top.php';
include "./views/{$viewName}.php";
include './views/layout-bottom.php';

// setting / reseting the error message
$_SESSION['error-message'] = null;

?>

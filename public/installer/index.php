<?php
// report all error
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

if (file_exists('../../install.lock')) {
    exit("The installation has been completed already. Please delete the file 'install.lock' to re-run");
}

if (!file_exists('../../.env')) {
    echo shell_exec('cp ../../.env.example ../../.env');
}

use DevCoder\DotEnv;

// Load only what the database guard needs, before any shell or form code.
require_once 'dotenv.php';
require_once './src/functions/installer.php';

// Load .env so the guard below can read DB credentials.
(new DotEnv(dirname(__FILE__, 3) . '/.env'))->load();

// A migrated database without an in-progress marker means a finished install.
if (!installer_install_in_progress() && installer_detects_existing_installation()) {
    @file_put_contents(dirname(__FILE__, 3) . '/install.lock', 'the installation is locked, delete this file to unlock it');

    exit('The database already contains an installation, so the installer is blocked. If you are moving to a new server, follow the migration guide first: https://ctrlpanel.gg/docs/guides/migration. To force a re-run, delete install.lock and set INSTALLER_FORCE=true in .env.');
}

// Mark the install as in progress; installation-complete.php removes it.
@file_put_contents(dirname(__FILE__, 3) . '/install.inprogress', 'the installation is in progress, this file is removed when the install completes');

// Include systems
require_once '../../vendor/autoload.php';

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
    exit("The installation has been completed already. To re-run, delete the File 'install.lock' and set INSTALLER_FORCE=true in .env");
}

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

// Handle reset parameter to return to first step
if (isset($_GET['reset'])) {
    $_SESSION['current_installation_step'] = 1;
    header('Location: /installer/index.php');
    exit;
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

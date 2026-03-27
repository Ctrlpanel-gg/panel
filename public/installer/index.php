<?php

session_start();

use DevCoder\DotEnv;

require_once 'dotenv.php';

$rootPath = dirname(__DIR__, 2);
$environmentFile = $rootPath . '/.env';
$environmentExampleFile = $rootPath . '/.env.example';
$installLockFile = $rootPath . '/install.lock';

if (file_exists($installLockFile)) {
    exit("The installation has been completed already. Please delete the File 'install.lock' to re-run");
}

if (! file_exists($environmentFile)) {
    copy($environmentExampleFile, $environmentFile);
}

if (file_exists($environmentFile)) {
    (new DotEnv($environmentFile))->load();
}

$webInstallerEnabled = filter_var(getenv('ENABLE_WEB_INSTALLER') ?: false, FILTER_VALIDATE_BOOLEAN);
$appEnvironment = strtolower(getenv('APP_ENV') ?: 'production');
$remoteAddress = $_SERVER['REMOTE_ADDR'] ?? '';
$isLocalRequest = in_array($remoteAddress, ['127.0.0.1', '::1'], true);
$isDevelopmentEnvironment = in_array($appEnvironment, ['local', 'development'], true);

if (! $webInstallerEnabled && ! $isLocalRequest && ! $isDevelopmentEnvironment) {
    http_response_code(403);
    exit('The web installer is disabled for this environment. Set ENABLE_WEB_INSTALLER=true temporarily if you need to run it.');
}

// Include systems
require_once '../../vendor/autoload.php';
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

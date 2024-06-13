<?php

// Include the function files
require_once './src/functions/environment.php';
require_once './src/functions/database.php';
require_once './src/functions/shell.php';
require_once './src/functions/logging.php';
require_once './src/functions/utils.php';

// Include the form files
require_once './src/forms/timezone.php';
require_once './src/forms/database.php';
require_once './src/forms/redis.php';
require_once './src/forms/dashboard.php';
require_once './src/forms/smtp.php';
require_once './src/forms/pterodactyl.php';
require_once './src/forms/admin.php';

require_once './functions.php';
require_once './forms.php';

if (file_exists('../../install.lock')) {
    exit("The installation has been completed already. Please delete the File 'install.lock' to re-run");
}

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

$step = isset($_GET['step']) ? $_GET['step'] : 1;
$viewName = $viewNames[$step];  // Get the appropriate view name

// Load the layout and the specific view file
include './views/layout-top.php';
include "./views/{$viewName}.php";
include './views/layout-bottom.php';

?>
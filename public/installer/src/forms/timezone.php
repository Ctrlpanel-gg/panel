<?php

if (isset($_POST['timezoneConfig'])) {
    wh_log('Setting up Timezone', 'debug');
    $timezone = $_POST['timezone'];

    setenv('APP_TIMEZONE', $timezone);

    wh_log('Timezone set: ' . $timezone, 'debug');
    next_step();
}

?>
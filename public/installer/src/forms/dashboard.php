<?php

if (isset($_POST['checkGeneral'])) {
    wh_log('setting app settings', 'debug');
    $appname = '"' . $_POST['name'] . '"';
    $appurl = $_POST['url'];

    $parsedUrl = parse_url($appurl);

    if (!isset($parsedUrl['scheme'])) {
        send_error_message("Please set an URL Scheme like 'https://'!");
        exit();
    }

    if (!isset($parsedUrl['host'])) {
        send_error_message("Please set an valid URL host like 'https://ctrlpanel.example.com'!");
        exit();
    }

    $appurl = $parsedUrl['scheme'] . '://' . $parsedUrl['host'];

    setenv('APP_NAME', $appname);
    setenv('APP_URL', $appurl);

    wh_log('App settings set', 'debug');
    next_step();
}

?>

<?php

if (isset($_POST['checkGeneral'])) {
    wh_log('setting app settings', 'debug');
    $appname = '"' . $_POST['name'] . '"';
    $appurl = $_POST['url'];

    $parsedUrl = parse_url($appurl);

    if (!isset($parsedUrl['scheme']) || !in_array(strtolower((string) $parsedUrl['scheme']), ['http', 'https'], true)) {
        send_error_message("Please set an URL Scheme like 'https://'!");
        exit();
    }

    if (!isset($parsedUrl['host'])) {
        send_error_message("Please set an valid URL host like 'https://ctrlpanel.example.com'!");
        exit();
    }

    $normalizedAppUrl = $parsedUrl['scheme'] . '://' . $parsedUrl['host'];
    if (isset($parsedUrl['port'])) {
        $normalizedAppUrl .= ':' . $parsedUrl['port'];
    }

    $appurl = rtrim($normalizedAppUrl, '/');

    setenv('APP_NAME', $appname);
    setenv('APP_URL', $appurl);

    wh_log('App settings set', 'debug');
    next_step();
}
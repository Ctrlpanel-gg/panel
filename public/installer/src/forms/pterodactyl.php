<?php
if (isset($_POST['checkPtero'])) {
    wh_log('Checking Pterodactyl Settings', 'debug');

    $url = trim((string) ($_POST['url'] ?? ''));
    $key = trim((string) ($_POST['key'] ?? ''));
    $clientkey = trim((string) ($_POST['clientkey'] ?? ''));

    $parsedUrl = parse_url($url);

    if (!isset($parsedUrl['scheme']) || !in_array(strtolower((string) $parsedUrl['scheme']), ['http', 'https'], true)) {
        send_error_message("Please set an URL Scheme like 'https://'!");
        exit();
    }

    if (!isset($parsedUrl['host'])) {
        send_error_message("Please set an valid URL host like 'https://panel.example.com'!");
        exit();
    }

    $url = $parsedUrl['scheme'] . '://' . $parsedUrl['host'];

    $callpteroURL = $url . '/api/client/account';
    $call = curl_init();

    curl_setopt($call, CURLOPT_URL, $callpteroURL);
    curl_setopt($call, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($call, CURLOPT_HTTPHEADER, [
        'Accept: Application/vnd.pterodactyl.v1+json',
        'Content-Type: application/json',
        'Authorization: Bearer ' . $clientkey,
    ]);
    $callresponse = curl_exec($call);
    $callresult = json_decode((string) $callresponse, true);
    curl_close($call);

    $pteroURL = $url . '/api/application/users';
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $pteroURL);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Accept: Application/vnd.pterodactyl.v1+json',
        'Content-Type: application/json',
        'Authorization: Bearer ' . $key,
    ]);
    $response = curl_exec($ch);
    $result = json_decode((string) $response, true);
    curl_close($ch);

    if (!is_array($result)) {
        $responseType = gettype($result);
        if (is_string($response)) {
            $truncatedResponse = substr($response, 0, 500);
        } else {
            $encodedResponse = json_encode($result);
            $truncatedResponse = substr((string) $encodedResponse, 0, 500);
        }
        wh_log(
            'No array in response found. Type: ' . $responseType . '. Raw response (truncated): ' . $truncatedResponse,
            'error'
        );
        send_error_message("An unknown Error occurred, please try again!");
        exit();
    }

    if (array_key_exists('errors', $result) && ($result['errors'][0]['detail'] ?? '') === 'This action is unauthorized.') {
        wh_log('API CALL ERROR: ' . ($result['errors'][0]['code'] ?? 'unknown'), 'error');
        send_error_message("Couldn\'t connect to Pterodactyl. Make sure your Application API key has all read and write permissions!");
        exit();
    }

    if (is_array($callresult) && array_key_exists('errors', $callresult) && ($callresult['errors'][0]['detail'] ?? '') === 'Unauthenticated.') {
        wh_log('API CALL ERROR: ' . ($callresult['errors'][0]['code'] ?? 'unknown'), 'error');
        send_error_message("Your ClientAPI Key is wrong or the account is not an admin!");
        exit();
    }

    try {
        run_console(['php', 'artisan', 'settings:set', 'PterodactylSettings', 'panel_url', $url], null, null, null, false);
        run_console(['php', 'artisan', 'settings:set', 'PterodactylSettings', 'admin_token', $key], null, null, null, false);
        run_console(['php', 'artisan', 'settings:set', 'PterodactylSettings', 'user_token', $clientkey], null, null, null, false);
        wh_log('Database updated with pterodactyl Settings.', 'debug');
        next_step();
    } catch (Throwable $th) {
        wh_log("Setting Pterodactyl information failed. " . $th->getMessage(), 'error');
        send_error_message("Could not update pterodactyl settings. Please check installer.log.");
        exit();
    }
}
?>

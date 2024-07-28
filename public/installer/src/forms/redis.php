<?php

use Predis\Client;

if (isset($_POST['redisSetup'])) {
    wh_log('Setting up Redis', 'debug');
    $redisHost = $_POST['redishost'];
    $redisPort = $_POST['redisport'];
    $redisPassword = $_POST['redispassword'];

    $redisClient = new Client([
        'host'     => $redisHost,
        'port'     => $redisPort,
        'password' => $redisPassword,
        'timeout'  => 1.0,
    ]);

    try {
        $redisClient->ping();

        setenv('MEMCACHED_HOST', $redisHost);
        setenv('REDIS_HOST', $redisHost);
        setenv('REDIS_PORT', $redisPort);
        setenv('REDIS_PASSWORD', ($redisPassword === '' ? 'null' : $redisPassword));

        wh_log('Redis connection successful. Settings updated.', 'debug');
        next_step();
    } catch (Throwable $th) {
        wh_log('Redis connection failed. Settings updated.', 'debug');
        send_error_message("Please check your credentials!<br>" . $th->getMessage());
    }
}

?>

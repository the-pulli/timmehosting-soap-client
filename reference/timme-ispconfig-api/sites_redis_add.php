<?php

// System > Entfernte Benutzer: erfordert Checkbox 'Webseiten-Redis-Funktionen'
require 'soap_config.php';

$context = stream_context_create([
    'ssl' => [
        'verify_peer' => false,
        'verify_peer_name' => false,
    ],
]);

$client = new SoapClient(null, [
    'location' => $soap_location,
    'uri' => $soap_uri,
    'trace' => 1,
    'exceptions' => 1,
    'stream_context' => $context,
]);

try {
    if ($session_id = $client->login($username, $password)) {
        echo 'Login successful. Session ID:'.$session_id.'<br>';
    }

    // * Set the function parameters.

    $params = [
        'server_id' => 1,
        'parent_domain_id' => 1,
        'description' => 'My redis instance',
        'redis_max_databases' => 1,
        'redis_max_memory' => 128,
        'redis_max_memory_policy' => 1,
        'redis_password' => '1234567890', // if empty, server will generate a password
        'active' => 'y',
    ];

    $affected_rows = $client->sites_redis_add($session_id, $params);

    echo 'Redis ID: '.$affected_rows.'<br>';

    if ($client->logout($session_id)) {
        echo 'Logged out.<br>';
    }

} catch (SoapFault $e) {
    echo $client->__getLastResponse();
    exit('SOAP Error: '.$e->getMessage());
}

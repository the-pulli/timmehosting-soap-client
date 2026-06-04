<?php

// System > Entfernte Benutzer: erfordert Checkbox 'DNS-A-Funktionen'
require 'soap_config.php';

$context = stream_context_create([
    'ssl' => [
        'verify_peer' => false,
        'verify_peer_name' => false,
    ],
]);

$client = new SoapClient(null, ['location' => $soap_location,
    'uri' => $soap_uri,
    'trace' => 1,
    'exceptions' => 1,
    'stream_context' => $context]);

try {
    if ($session_id = $client->login($username, $password)) {
        echo 'Login successful. Session ID:'.$session_id.'<br>';
    }

    // * Set the function parameters.
    $client_id = 1;
    $params = [
        'server_id' => 1,
        'zone' => 5,
        'name' => 'a',
        'type' => 'a',
        'data' => '192.168.1.88',
        'aux' => '0',
        'ttl' => '3600',
        'active' => 'y',
        'stamp' => 'CURRENT_TIMESTAMP',
        'serial' => '1',
    ];

    $id = $client->dns_a_add($session_id, $client_id, $params);

    echo 'ID: '.$id.'<br>';

    if ($client->logout($session_id)) {
        echo 'Logged out.<br>';
    }

} catch (SoapFault $e) {
    echo $client->__getLastResponse();
    exit('SOAP Error: '.$e->getMessage());
}

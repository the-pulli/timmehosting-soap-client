<?php

// System > Entfernte Benutzer: erfordert Checkbox 'DNS-Zone-Funktionen'
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
    $client_id = 2;
    $params = [
        'server_id' => 1,
        'origin' => 'test.intt.',
        'ns' => 'one',
        'mbox' => 'zonemaster.test.tld.',
        'serial' => '1',
        'refresh' => '28800',
        'retry' => '7200',
        'expire' => '604800',
        'minimum' => '3600',
        'ttl' => '3600',
        'active' => 'y',
        'xfer' => '',
        'also_notify' => '',
        'update_acl' => '',
    ];

    $id = $client->dns_zone_add($session_id, $client_id, $params);

    echo 'DNS ID: '.$id.'<br>';

    if ($client->logout($session_id)) {
        echo 'Logged out.<br>';
    }

} catch (SoapFault $e) {
    echo $client->__getLastResponse();
    exit('SOAP Error: '.$e->getMessage());
}

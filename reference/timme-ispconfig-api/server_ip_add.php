<?php

// System > Entfernte Benutzer: erfordert Checkbox 'Server-Funktionen'
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
        'client_id' => 1,
        'ip_type' => 'IPv4',
        'ip_address' => '127.0.0.1',
        'virtualhost' => 'y',
        'virtualhost_port' => '1',
        'create_catchall' => 'y',
        'loadbalancer' => 'n',
        'smtp_mailserver_ip' => 'y',
        'min_tls_version' => 'TLSv1.2',
    ];

    $affected_rows = $client->server_ip_add($session_id, $client_id, $params);

    echo 'Cron ID: '.$affected_rows.'<br>';

    if ($client->logout($session_id)) {
        echo 'Logged out.<br>';
    }

} catch (SoapFault $e) {
    echo $client->__getLastResponse();
    exit('SOAP Error: '.$e->getMessage());
}

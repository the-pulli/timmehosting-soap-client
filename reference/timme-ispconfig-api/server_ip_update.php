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

    // * Parameters
    $server_ip_id = 5;
    $client_id = 1;

    // * Get the server ip record
    $server_ip_record = $client->server_ip_get($session_id, $server_ip_id);

    $server_ip_record['ip_type'] = 'IPv4';
    $server_ip_record['ip_address'] = '127.0.0.1';
    $server_ip_record['virtualhost'] = 'n';
    $server_ip_record['virtualhost_port'] = '1';
    $server_ip_record['create_catchall'] = 'n';
    $server_ip_record['loadbalancer'] = 'n';
    $server_ip_record['smtp_mailserver_ip'] = 'n';
    $server_ip_record['min_tls_version'] = 'TLSv1.2';

    $affected_rows = $client->server_ip_update($session_id, $client_id, $server_ip_id, $server_ip_record);

    echo 'Number of records that have been changed in the database: '.$affected_rows.'<br>';

    if ($client->logout($session_id)) {
        echo 'Logged out.<br>';
    }

} catch (SoapFault $e) {
    echo $client->__getLastResponse();
    exit('SOAP Error: '.$e->getMessage());
}

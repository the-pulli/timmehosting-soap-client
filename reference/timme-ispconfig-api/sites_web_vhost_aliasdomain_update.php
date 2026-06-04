<?php

// System > Entfernte Benutzer: erfordert Checkbox 'Webseiten-Aliasdomain-Funktionen'
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
    $aliasdomain_id = 10;
    $client_id = 1;

    // * Get the aliasdomain record
    $aliasdomain_record = $client->sites_web_vhost_aliasdomain_get($session_id, $aliasdomain_id);

    // * Change the status to inactive
    $aliasdomain_record['active'] = 'n';

    $affected_rows = $client->sites_web_vhost_aliasdomain_update($session_id, $client_id, $aliasdomain_id, $aliasdomain_record);

    echo 'Number of records that have been changed in the database: '.$affected_rows.'<br>';

    if ($client->logout($session_id)) {
        echo 'Logged out.<br>';
    }

} catch (SoapFault $e) {
    echo $client->__getLastResponse();
    exit('SOAP Error: '.$e->getMessage());
}

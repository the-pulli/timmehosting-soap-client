<?php

// System > Entfernte Benutzer: erfordert Checkbox 'Webseiten-Cron-Funktionen'
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
    $client_id = 1;
    $cron_id = 1;

    // * Get the cron record
    $cron_record = $client->sites_cron_get($session_id, $cron_id);

    // * Change active to no
    $cron_record['active'] = 'n';

    $affected_rows = $client->sites_cron_update($session_id, $client_id, $cron_id, $cron_record);

    echo 'Number of records that have been changed in the database: '.$affected_rows.'<br>';

    if ($client->logout($session_id)) {
        echo 'Logged out.<br>';
    }

} catch (SoapFault $e) {
    echo $client->__getLastResponse();
    exit('SOAP Error: '.$e->getMessage());
}

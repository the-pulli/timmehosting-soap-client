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

    // * Parameters
    $redis_id = 1;

    // * Get the redis record
    $redis_record = $client->sites_redis_get($session_id, $redis_id);

    // * Change record
    $redis_record['description'] = 'My new instance';
    $redis_record['redis_max_memory_policy'] = 2;
    $redis_record['redis_max_memory'] = 256;
    $redis_record['active'] = 'y';

    $affected_rows = $client->sites_redis_update($session_id, $redis_id, $redis_record);

    echo 'Number of records that have been changed in the database: '.$affected_rows.'<br>';

    if ($client->logout($session_id)) {
        echo 'Logged out.<br>';
    }

} catch (SoapFault $e) {
    echo $client->__getLastResponse();
    exit('SOAP Error: '.$e->getMessage());
}

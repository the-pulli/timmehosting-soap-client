<?php

// System > Entfernte Benutzer: erfordert Checkbox 'Webseiten-Shell-Benutzer-Funktionen'
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
    $client_id = 0;
    $shell_user_id = 1;

    // * Get the shell user record
    $shell_user_record = $client->sites_shell_user_get($session_id, $shell_user_id);

    // * Change active to no
    $shell_user_record['active'] = 'n';

    $affected_rows = $client->sites_shell_user_update($session_id, $client_id, $shell_user_id, $shell_user_record);

    echo 'Number of records that have been changed in the database: '.$affected_rows.'<br>';

    if ($client->logout($session_id)) {
        echo 'Logged out.<br>';
    }

} catch (SoapFault $e) {
    echo $client->__getLastResponse();
    exit('SOAP Error: '.$e->getMessage());
}

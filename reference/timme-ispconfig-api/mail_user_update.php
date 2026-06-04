<?php

// System > Entfernte Benutzer: erfordert Checkbox 'E-Mail-Benutzer-Funktionen'
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
    $mailuser_id = 1;
    $client_id = 1;

    // * Get the email user record
    $mail_user_record = $client->mail_user_get($session_id, $mailuser_id);

    // * Change the status to inactive
    // * If no password is set, a new hash from the old password will be generated. The old password is still valid.
    $mail_user_record['name'] = 'hmmyea';

    $affected_rows = $client->mail_user_update($session_id, $client_id, $mailuser_id, $mail_user_record);

    echo 'Number of records that have been changed in the database: '.$affected_rows.'<br>';

    if ($client->logout($session_id)) {
        echo 'Logged out.<br>';
    }

} catch (SoapFault $e) {
    echo $client->__getLastResponse();
    exit('SOAP Error: '.$e->getMessage());
}

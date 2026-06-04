<?php

// System > Entfernte Benutzer: erfordert Checkbox 'E-Mail-Domain-Funktionen'
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
    $domain_id = 1;
    $client_id = 1;

    // * Get the email domain record
    $mail_domain_record = $client->mail_domain_get($session_id, $domain_id);

    // * Change the status to inactive
    $mail_domain_record['active'] = 'n';

    $affected_rows = $client->mail_domain_update($session_id, $client_id, $domain_id, $mail_domain_record);

    echo 'Number of records that have been changed in the database: '.$affected_rows.'<br>';

    if ($client->logout($session_id)) {
        echo 'Logged out.<br>';
    }

} catch (SoapFault $e) {
    echo $client->__getLastResponse();
    exit('SOAP Error: '.$e->getMessage());
}

<?php

// System > Entfernte Benutzer: erfordert Checkbox 'E-Mail-Spamfilter-Whitelist-Funktionen'
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
    $wblist_id = 1;
    $client_id = 1;

    // * Get the spamfilter whitelist record
    $mail_wblist_record = $client->mail_spamfilter_whitelist_get($session_id, $wblist_id);

    // * Change the priority to 2
    $mail_wblist_record['priority'] = 2;

    $affected_rows = $client->mail_spamfilter_whitelist_update($session_id, $client_id, $wblist_id, $mail_wblist_record);

    echo 'Number of records that have been changed in the database: '.$affected_rows.'<br>';

    if ($client->logout($session_id)) {
        echo 'Logged out.<br>';
    }

} catch (SoapFault $e) {
    echo $client->__getLastResponse();
    exit('SOAP Error: '.$e->getMessage());
}

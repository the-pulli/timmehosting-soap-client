<?php

// System > Entfernte Benutzer: erfordert Checkbox 'E-Mail-Spamfilter-Blacklist-Funktionen'
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
    $wblist_id = 2;

    $mail_wblist_record = $client->mail_spamfilter_blacklist_get($session_id, $wblist_id);

    print_r($mail_wblist_record);

    if ($client->logout($session_id)) {
        echo 'Logged out.<br>';
    }

} catch (SoapFault $e) {
    echo $client->__getLastResponse();
    exit('SOAP Error: '.$e->getMessage());
}

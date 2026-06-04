<?php

// System > Entfernte Benutzer: erfordert Checkbox 'E-Mail-Weiterleitung-Funktionen'
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
    $forwarding_id = 1;

    $mail_forwarding_record = $client->mail_forward_get($session_id, $forwarding_id);
    // $mail_forwarding_record = $client->mail_forward_get($session_id, array('source' => '%@test.int'));

    print_r($mail_forwarding_record);

    if ($client->logout($session_id)) {
        echo 'Logged out.<br>';
    }

} catch (SoapFault $e) {
    echo $client->__getLastResponse();
    exit('SOAP Error: '.$e->getMessage());
}

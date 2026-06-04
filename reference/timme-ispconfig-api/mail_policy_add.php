<?php

// System > Entfernte Benutzer: erfordert Checkbox 'E-Mail-Spamfilter-Richtlinien-Funktionen'
require 'soap_config.php';

$context = stream_context_create([
    'ssl' => [
        'verify_peer' => false,
        'verify_peer_name' => false,
    ],
]);

$client = new SoapClient(
    null,
    [
        'location' => $soap_location,
        'uri' => $soap_uri,
        'trace' => 1,
        'exceptions' => 1,
        'stream_context' => $context,
    ],
);

try {
    if ($session_id = $client->login($username, $password)) {
        echo "Login successful. Session ID: $session_id <br>";
    }

    // * Set the function parameters.
    $client_id = 1;
    $params = [
        'policy_name' => 'numberone',
        'virus_lover' => 'Y', // N | Y, default => N
        'spam_lover' => 'Y', // N | Y, default => N
    ];

    $affected_rows = $client->mail_policy_add($session_id, $client_id, $params);

    echo "Policy ID: $affected_rows<br>";

    if ($client->logout($session_id)) {
        echo 'Logged out.<br>';
    }
} catch (SoapFault $e) {
    echo $client->__getLastResponse();
    exit("SOAP Error: {$e->getMessage()}");
}

<?php

// System > Entfernte Benutzer: erfordert Checkbox 'Webseiten-Datenbanken-Funktionen'
require 'soap_config.php';

$context = stream_context_create(
    [
        'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false,
        ],
    ]
);

$client = new SoapClient(
    null,
    [
        'location' => $soap_location,
        'uri' => $soap_uri,
        'trace' => 1,
        'exceptions' => 1,
        'stream_context' => $context,
    ]
);

try {
    if ($session_id = $client->login($username, $password)) {
        echo "Login successful. Session ID: $session_id <br>";
    }

    // * Set the function parameters.
    $server_id = 1;

    $database_port = $client->sites_database_get_free_port($session_id, $server_id);

    echo "Next free database port: $database_port<br>";

    if ($client->logout($session_id)) {
        echo 'Logged out.<br>';
    }
} catch (SoapFault $e) {
    echo $client->__getLastResponse();
    exit("SOAP Error: {$e->getMessage()}");
}

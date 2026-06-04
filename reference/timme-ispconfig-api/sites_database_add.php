<?php

// System > Entfernte Benutzer: erfordert Checkbox 'Webseiten-Datenbanken-Funktionen'
require 'soap_config.php';

$context = stream_context_create(
    [
        'ssl' => [
            'verify_peer'      => false,
            'verify_peer_name' => false,
        ]
    ]
);


$client = new SoapClient(
    null,
    [
        'location'       => $soap_location,
        'uri'            => $soap_uri,
        'trace'          => 1,
        'exceptions'     => 1,
        'stream_context' => $context
    ]
);


try {
    if ($session_id = $client->login($username, $password)) {
        echo "Login successful. Session ID: $session_id <br>";
    }
    
    //* Set the function parameters.
    $client_id = 1;
    
    $params = [
        'server_id'           => 1,
        'type'                => 'mysql',
        'website_id'          => 1,
        'database_name'       => 'db_name2',
        'database_user_id'    => 1,
        'database_ro_user_id' => 0,
        'database_charset'    => 'utf8',
        'remote_access'       => 'y', // y | n, default => y
        'remote_ips'          => '',
        'backup_interval'     => 'daily',
        'backup_copies'       => 7,
        'active'              => 'y', // y | n, default => y
        'database_version'    => '', // '' = system database or use sites_databaseversion_get() to get possible versions
    ];
    
    $database_id = $client->sites_database_add($session_id, $client_id, $params);
    
    echo "Database ID: $database_id<br>";

    // Get the new inserted database to get the new port
    $new_db = $client->sites_database_get($session_id, $database_id);
    echo "New inserted database:<br>"
    echo json_encode($new_db);
    
    if ($client->logout($session_id)) {
        echo "Logged out.<br>";
    }
} catch (SoapFault $e) {
    echo $client->__getLastResponse();
    die("SOAP Error: {$e->getMessage()}");
}

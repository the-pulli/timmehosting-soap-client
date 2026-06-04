<?php

// System > Entfernte Benutzer: erfordert Checkbox 'Webseiten-Geschützte-Ordner-Funktionen'
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
    $client_id = 1;
    $params = [
        'sys_perm_user' => '',
        'sys_perm_group' => '',
        'sys_perm_other' => '',
        'server_id' => 1,
        'parent_domain_id' => 1,
        'path' => '/',
        'active' => 'y',
        'whitelist_ips' => '',
    ];

    $folder_id = $client->sites_web_folder_add($session_id, $client_id, $params);

    echo $folder_id.'<br>';

    if ($client->logout($session_id)) {
        echo 'Logged out.<br>';
    }

} catch (SoapFault $e) {
    echo $client->__getLastResponse();
    exit('SOAP Error: '.$e->getMessage());
}

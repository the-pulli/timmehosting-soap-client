<?php

// System > Entfernte Benutzer: erfordert Checkbox 'E-Mail-Relay-Funktionen'
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
        'sys_userid' => 1,
        'sys_groupid' => 1,
        'sys_perm_user' => 'user',
        'sys_perm_user' => 'group',
        'sys_perm_other' => 'other',
        'server_id' => 1,
        'source' => '',
        'access' => 'OK',
        'active' => 'y',
    ];

    $mail_policy_record = $client->mail_relay_recipient_add($session_id, $client_id, $params);

    print_r($mail_policy_record);

    if ($client->logout($session_id)) {
        echo 'Logged out.<br>';
    }

} catch (SoapFault $e) {
    echo $client->__getLastResponse();
    exit('SOAP Error: '.$e->getMessage());
}

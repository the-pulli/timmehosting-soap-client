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

    // * Set the function parameters.
    $client_id = 1;
    $params = [
        'server_id' => 1,
        'email' => 'joe@test.int',
        'login' => 'joe@test.int',
        'password' => 'howtoforge',
        'name' => 'joe',
        'uid' => 5000,
        'gid' => 5000,
        'maildir' => '/var/vmail/test.int/joe',
        'quota' => 5242880,
        'cc' => '',
        'homedir' => '/var/vmail',
        'autoresponder' => 'n',
        'autoresponder_start_date' => ['day' => 1, 'month' => 7, 'year' => 2012, 'hour' => 0, 'minute' => 0],
        'autoresponder_end_date' => ['day' => 20, 'month' => 7, 'year' => 2012, 'hour' => 0, 'minute' => 0],
        'autoresponder_text' => 'hallo',
        'move_junk' => 'n',
        'custom_mailfilter' => 'spam',
        'postfix' => 'n',
        'access' => 'n',
        'disableimap' => 'n',
        'disablepop3' => 'n',
        'disabledeliver' => 'n',
        'disablesmtp' => 'n',
    ];

    $affected_rows = $client->mail_user_add($session_id, $client_id, $params);

    echo 'New user: '.$affected_rows.'<br>';

    if ($client->logout($session_id)) {
        echo 'Logged out.<br>';
    }

} catch (SoapFault $e) {
    echo $client->__getLastResponse();
    exit('SOAP Error: '.$e->getMessage());
}

<?php

// System > Entfernte Benutzer: erfordert Checkbox 'E-Mail-Backup-Funktionen'
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
        echo 'Login successful. Session ID:'.$session_id.'<br>';
    }

    // * Set the function parameters.
    $server_id = 1;
    $backup_id = 1;
    $action_type = 'backup_download_mail_link';

    $tstamp = $client->mail_user_backup($session_id, $backup_id, $action_type);

    $link = $client->mail_user_backup_download_link($session_id, $server_id, $tstamp);

    while ($link === true) {
        echo 'Waiting for link...<br>';
        sleep(60);
        echo 'Look if link is ready...<br>';
        $link = $client->mail_user_backup_download_link($session_id, $server_id, $tstamp);
    }

    print_r($link);
    echo '<br>';

    if ($client->logout($session_id)) {
        echo 'Logged out.<br>';
    }
} catch (SoapFault $e) {
    echo $client->__getLastResponse();
    exit('SOAP Error: '.$e->getMessage());
}

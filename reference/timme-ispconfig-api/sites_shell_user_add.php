<?php
// System > Entfernte Benutzer: erfordert Checkbox 'Webseiten-Shell-Benutzer-Funktionen'
require 'soap_config.php';

$context = stream_context_create(array(
    'ssl' => array(
        'verify_peer'       => false,
        'verify_peer_name'  => false,
    )
));


$client = new SoapClient(null, array('location' => $soap_location,
        'uri'      => $soap_uri,
        'trace' => 1,
        'exceptions' => 1,
        'stream_context' => $context));


try {
    if($session_id = $client->login($username, $password)) {
        echo 'Login successful. Session ID:'.$session_id.'<br>';
    }

    //* Set the function parameters.
    $client_id = 2;
    
    $params = array(
        'server_id'        => 1,
        'parent_domain_id' => 1,
        'username'         => 'prefix_tom',
        'username_prefix'  => 'prefix_',
        'password'         => 'test@123',
        'quota_size'       => 10000,
        'active'           => 'y',
        'puser'            => 'web1',
        'pgroup'           => 'client2',
        'shell'            => '/bin/bash',
        'dir'              => '/var/www/clients/client2/web1/home/prefix_tom',
        'chroot'           => 'no'
    );

    $affected_rows = $client->sites_shell_user_add($session_id, $client_id, $params);

    echo "Shell User ID: ".$affected_rows."<br>";

    // Get the new inserted shell user to get the final username (if prefix was set automatically)
    $new_user = $client->sites_shell_user_get($session_id, $affected_rows);
    echo "New inserted shell user:<br>"
    echo json_encode($new_user);

    if($client->logout($session_id)) {
        echo 'Logged out.<br>';
    }


} catch (SoapFault $e) {
    echo $client->__getLastResponse();
    die('SOAP Error: '.$e->getMessage());
}



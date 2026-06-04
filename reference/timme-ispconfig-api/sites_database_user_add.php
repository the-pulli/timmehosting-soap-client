<?php
// System > Entfernte Benutzer: erfordert Checkbox 'Webseiten-Datenbanken-Funktionen'
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
    $client_id = 1;
    $params = array(
        'server_id' => 1,
        'database_user' => 'db_name2',
        'database_password' => 'db_name2'
    );

    $database_id = $client->sites_database_user_add($session_id, $client_id, $params);

    echo "Database ID: ".$database_user_id."<br>";

    // Get the new inserted database user to get the final username (if prefix was set automatically)
    $new_user = $client->sites_database_user_get($session_id, $database_user_id);
    echo "New inserted database user:<br>"
    echo json_encode($new_user);

    if($client->logout($session_id)) {
        echo 'Logged out.<br>';
    }


} catch (SoapFault $e) {
    echo $client->__getLastResponse();
    die('SOAP Error: '.$e->getMessage());
}



<?php

// System > Entfernte Benutzer: erfordert Checkbox 'Webseiten-Domain-Funktionen'
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
        echo "Login successful. Session ID: $session_id<br>";
    }

    // * Set the function parameters.
    $client_id = 1;

    $params = [
        'server_id' => 1,
        'ip_address' => '*',
        'domain' => 'test2.int',
        'type' => 'vhost', // vhost | alias | vhostalias | subdomain | vhostsubdomain
        'parent_domain_id' => 0,
        'vhost_type' => 'name',
        'hd_quota' => -1,
        'traffic_quota' => -1,
        'cgi' => 'y', // y | n, default => y
        'ssi' => 'y', // y | n, default => y
        'suexec' => 'y', // y | n, default => y
        'errordocs' => 0, // default => 0
        'is_subdomainwww' => 1,
        'subdomain' => 'none', // none | www, default => none
        'php' => 'php-fpm', // no | fast-cgi | php-fpm, default => php-fpm
        'ruby' => 'n', // y | n, default => n
        'redirect_type' => '',
        'redirect_path' => '',
        'ssl' => 'n', // y | n, default => n
        'ssl_letsencrypt' => 'n', // y | n, default => n
        'ssl_state' => '',
        'ssl_locality' => '',
        'ssl_organisation' => '',
        'ssl_organisation_unit' => '',
        'ssl_country' => '',
        'ssl_domain' => '',
        'ssl_request' => '',
        'ssl_key' => '',
        'ssl_cert' => '',
        'ssl_bundle' => '',
        'ssl_action' => '', // show | save | create | delete
        'stats_password' => '',
        'stats_type' => '', // '' | goaccess
        'storage_time_log_files' => 7,
        'allow_override' => 'All',
        'apache_directives' => '',
        'php_open_basedir' => '/',
        'pm' => 'ondemand', // static | dynamic | ondemand
        'pm_max_children' => 500, // default => 500
        'pm_max_requests' => 0,
        'pm_process_idle_timeout' => 5, // default => 5
        'custom_php_ini' => '',
        'backup_interval' => 'daily',
        'backup_copies' => 7,
        'active' => 'y', // y | n, default => y
        'traffic_quota_lock' => 'n', // y | n, default => n
        'http_port' => '80',
        'https_port' => '443',
        'tideways_sample_rate' => 25,
    ];

    $affected_rows = $client->sites_web_domain_add($session_id, $client_id, $params, $readonly = false);

    echo "Web Domain ID: $affected_rows<br>";

    if ($client->logout($session_id)) {
        echo 'Logged out.<br>';
    }
} catch (SoapFault $e) {
    echo $client->__getLastResponse();
    exit("SOAP Error: {$e->getMessage()}");
}

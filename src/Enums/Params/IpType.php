<?php

namespace Pulli\TimmeSoapClient\Enums\Params;

/**
 * IP address family — ISPConfig stores this as the literal `'IPv4'` /
 * `'IPv6'` string on `server_ip` records.
 */
enum IpType: string
{
    case IPv4 = 'IPv4';
    case IPv6 = 'IPv6';
}

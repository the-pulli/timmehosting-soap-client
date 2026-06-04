<?php

namespace Pulli\TimmeSoapClient\Enums\Params;

/**
 * `dns_zone.type` — ISPConfig stores this **uppercase** (one of the few
 * fields that's not lowercase). The backed values mirror that exactly.
 */
enum DnsZoneType: string
{
    case Master = 'MASTER';
    case Slave = 'SLAVE';
}

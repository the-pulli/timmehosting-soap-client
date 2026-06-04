<?php

namespace Pulli\TimmeSoapClient\Enums;

/**
 * Status flag used by ISPConfig's `*_set_status` family of SOAP functions
 * (sites_web_domain, mail_domain, dns_zone, …). Mirrors the literal
 * strings ISPConfig expects.
 */
enum Status: string
{
    case Active = 'active';
    case Inactive = 'inactive';

    public static function fromBool(bool $value): self
    {
        return $value ? self::Active : self::Inactive;
    }
}

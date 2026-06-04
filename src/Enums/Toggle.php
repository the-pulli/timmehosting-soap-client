<?php

namespace Pulli\TimmeSoapClient\Enums;

/**
 * ISPConfig stores boolean-ish "active" / "enabled" flags as the literal
 * strings "y" / "n". Use this enum everywhere a SOAP parameter takes one
 * of those values — passes the right string to ISPConfig, gives callers
 * type-safe naming on this side.
 */
enum Toggle: string
{
    case Yes = 'y';
    case No = 'n';

    public static function fromBool(bool $value): self
    {
        return $value ? self::Yes : self::No;
    }
}

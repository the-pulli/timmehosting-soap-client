<?php

namespace Pulli\TimmeSoapClient\Enums\Params;

/**
 * PHP execution handler for `web_domain.php`. `'no'` disables PHP entirely;
 * `PhpFpm` is the per-pool FPM setup TimmeHosting uses by default.
 */
enum PhpHandler: string
{
    case Disabled = 'no';
    case FastCgi = 'fast-cgi';
    case PhpFpm = 'php-fpm';
    case Mod = 'mod';
}

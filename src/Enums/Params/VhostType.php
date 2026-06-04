<?php

namespace Pulli\TimmeSoapClient\Enums\Params;

/**
 * Vhost type for `web_domain.type` (ISPConfig `sites_web_domain_*`).
 *
 * `Vhost` is the standalone document root case; `Alias`/`Subdomain` create
 * server-aliases under another vhost; `VhostAlias`/`VhostSubdomain` are
 * the "own document root" variants of those.
 */
enum VhostType: string
{
    case Vhost = 'vhost';
    case Alias = 'alias';
    case Subdomain = 'subdomain';
    case VhostAlias = 'vhostalias';
    case VhostSubdomain = 'vhostsubdomain';
}

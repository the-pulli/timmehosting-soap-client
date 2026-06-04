<?php

namespace Pulli\TimmeSoapClient\Enums\Params;

/**
 * `web_domain.subdomain` policy — controls automatic www / wildcard
 * subdomain handling on a vhost.
 *
 * (Named `WebSubdomain` rather than `VhostSubdomain` to avoid colliding
 * with `VhostType::VhostSubdomain`, which is a different concept.)
 */
enum WebSubdomain: string
{
    case None = 'none';
    case Www = 'www';
    case Wildcard = '*';
}

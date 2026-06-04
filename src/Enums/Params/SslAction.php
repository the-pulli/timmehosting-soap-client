<?php

namespace Pulli\TimmeSoapClient\Enums\Params;

/**
 * `web_domain.ssl_action` — drives ISPConfig's SSL state machine on a
 * vhost update. `Create` requests a new certificate, `Save` re-saves an
 * existing one, `Delete` removes it.
 */
enum SslAction: string
{
    case Create = 'create';
    case Save = 'save';
    case Delete = 'del';
}

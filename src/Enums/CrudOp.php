<?php

namespace Pulli\TimmeSoapClient\Enums;

/**
 * ISPConfig SOAP CRUD suffixes — every resource follows the pattern
 * `<resource>_<op>` (e.g. `dns_a_get`, `mail_user_add`).
 */
enum CrudOp: string
{
    case Get = 'get';
    case Add = 'add';
    case Update = 'update';
    case Delete = 'delete';
}

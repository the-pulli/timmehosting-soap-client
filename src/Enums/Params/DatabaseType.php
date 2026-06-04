<?php

namespace Pulli\TimmeSoapClient\Enums\Params;

/**
 * Database engine for `sites_database.type`.
 */
enum DatabaseType: string
{
    case Mysql = 'mysql';
    case Postgresql = 'postgresql';
}

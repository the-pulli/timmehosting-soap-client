<?php

namespace Pulli\TimmeSoapClient\Resources;

use Pulli\TimmeSoapClient\Exception;
use Pulli\TimmeSoapClient\Resource;

/**
 * Server-level PHP/FPM control via `server_*_php*` functions.
 */
class Php extends Resource
{
    /**
     * Graceful restart of a PHP-FPM pool on the given server. Returns true
     * on success; throws Exception on a falsy return from ISPConfig.
     */
    public function restart(int $serverId, int $serverPhpId): bool
    {
        $ok = $this->call('server_restart_php', $serverId, $serverPhpId);
        if (! $ok) {
            throw new Exception("server_restart_php returned falsy for server_id={$serverId}, php_id={$serverPhpId}");
        }

        return true;
    }

    /**
     * List the PHP versions ISPConfig knows about on a server. Useful for
     * picking a `server_php_id` at provision time.
     *
     * @return array<int, array<string, mixed>>
     */
    public function versions(int $serverId): array
    {
        return $this->call('server_get_php_versions', $serverId, null) ?: [];
    }
}

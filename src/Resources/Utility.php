<?php

namespace Pulli\TimmeSoapClient\Resources;

use Pulli\TimmeSoapClient\Resource;

/**
 * ISPConfig SOAP utility functions that don't belong to a specific entity.
 */
class Utility extends Resource
{
    /**
     * Every client visible to the current API user across all resellers.
     * Different from clients->all() (which is `client_get_all`) — this one
     * is the global, unscoped lookup.
     *
     * @return array<int, array<string, mixed>>
     */
    public function allClients(): array
    {
        $r = $this->call('get_all_clients');

        return is_array($r) ? $r : [];
    }

    /**
     * Names of every Remote API function the panel exposes to the current
     * user (top-level form — same payload as Server::functionList()).
     *
     * @return array<int, string>
     */
    public function functionList(): array
    {
        $r = $this->call('get_function_list');

        return is_array($r) ? $r : [];
    }

    /**
     * Disk/filesystem quota information for a system user (`quota_get_by_user`).
     *
     * @return array<string, mixed>
     */
    public function quotaByUser(string $username): array
    {
        return $this->call('quota_get_by_user', $username);
    }
}

<?php

namespace Pulli\TimmeSoapClient\Resources;

use Pulli\TimmeSoapClient\Resource;

/**
 * `sites_database_*` and `sites_database_user_*` — panel-managed
 * MySQL/MariaDB/PostgreSQL databases + their users.
 *
 * Quirk: DB names are auto-prefixed with c<client_id> by ISPConfig — pass
 * the suffix you want and inspect the returned record to learn the final
 * full name.
 */
class Database extends Resource
{
    /** @return array<string, mixed> */
    public function find(int $databaseId): array
    {
        return $this->call('sites_database_get', $databaseId);
    }

    /**
     * @param  array<string, mixed>  $filter
     * @return array<int, array<string, mixed>>
     */
    public function all(array $filter = []): array
    {
        $r = $this->call('sites_database_get', $filter);

        return is_array($r) ? $r : [];
    }

    /**
     * Create a database. Returns the new `database_id`.
     *
     * @param  array<string, mixed>  $params
     */
    public function add(int $clientId, array $params): int
    {
        return (int) $this->call('sites_database_add', $clientId, $params);
    }

    /** @param  array<string, mixed>  $params */
    public function update(int $clientId, int $databaseId, array $params): bool
    {
        return (bool) $this->call('sites_database_update', $clientId, $databaseId, $params);
    }

    public function delete(int $databaseId): bool
    {
        return (bool) $this->call('sites_database_delete', $databaseId);
    }

    /** @return array<int, array<string, mixed>> */
    public function allByUser(int $clientId): array
    {
        $r = $this->call('sites_database_get_all_by_user', $clientId);

        return is_array($r) ? $r : [];
    }

    /** Returns an unused TCP port on the server for a new DB instance. */
    public function freePort(int $serverId): int
    {
        return (int) $this->call('sites_database_get_free_port', $serverId);
    }

    // ── Database users ────────────────────────────────────────────────

    /** @return array<string, mixed> */
    public function findUser(int $databaseUserId): array
    {
        return $this->call('sites_database_user_get', $databaseUserId);
    }

    /**
     * @param  array<string, mixed>  $params
     */
    public function addUser(int $clientId, array $params): int
    {
        return (int) $this->call('sites_database_user_add', $clientId, $params);
    }

    /** @param  array<string, mixed>  $params */
    public function updateUser(int $clientId, int $databaseUserId, array $params): bool
    {
        return (bool) $this->call('sites_database_user_update', $clientId, $databaseUserId, $params);
    }

    public function deleteUser(int $databaseUserId): bool
    {
        return (bool) $this->call('sites_database_user_delete', $databaseUserId);
    }
}

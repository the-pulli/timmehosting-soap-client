<?php

namespace Pulli\TimmeSoapClient\Resources;

use Pulli\TimmeSoapClient\Resource;

/**
 * `sites_shell_user_*` — panel-managed SSH/SFTP shell users on a site.
 */
class ShellUser extends Resource
{
    /** @return array<string, mixed> */
    public function find(int $shellUserId): array
    {
        return $this->call('sites_shell_user_get', $shellUserId);
    }

    /**
     * @param  array<string, mixed>  $filter
     * @return array<int, array<string, mixed>>
     */
    public function all(array $filter = []): array
    {
        $r = $this->call('sites_shell_user_get', $filter);

        return is_array($r) ? $r : [];
    }

    /**
     * Add a shell user. Returns the new `shell_user_id`.
     *
     * @param  array<string, mixed>  $params
     */
    public function add(int $clientId, array $params): int
    {
        return (int) $this->call('sites_shell_user_add', $clientId, $params);
    }

    /** @param  array<string, mixed>  $params */
    public function update(int $clientId, int $shellUserId, array $params): bool
    {
        return (bool) $this->call('sites_shell_user_update', $clientId, $shellUserId, $params);
    }

    public function delete(int $shellUserId): bool
    {
        return (bool) $this->call('sites_shell_user_delete', $shellUserId);
    }
}

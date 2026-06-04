<?php

namespace Pulli\TimmeSoapClient\Resources;

use Pulli\TimmeSoapClient\Resource;

/**
 * `sites_ftp_user_*` — panel-managed FTP accounts on a site.
 */
class FtpUser extends Resource
{
    /** @return array<string, mixed> */
    public function find(int $ftpUserId): array
    {
        return $this->call('sites_ftp_user_get', $ftpUserId);
    }

    /**
     * @param  array<string, mixed>  $filter
     * @return array<int, array<string, mixed>>
     */
    public function all(array $filter = []): array
    {
        $r = $this->call('sites_ftp_user_get', $filter);

        return is_array($r) ? $r : [];
    }

    /**
     * Add an FTP user. Returns the new `ftp_user_id`.
     *
     * @param  array<string, mixed>  $params
     */
    public function add(int $clientId, array $params): int
    {
        return (int) $this->call('sites_ftp_user_add', $clientId, $params);
    }

    /** @param  array<string, mixed>  $params */
    public function update(int $clientId, int $ftpUserId, array $params): bool
    {
        return (bool) $this->call('sites_ftp_user_update', $clientId, $ftpUserId, $params);
    }

    public function delete(int $ftpUserId): bool
    {
        return (bool) $this->call('sites_ftp_user_delete', $ftpUserId);
    }

    /**
     * Variant of `find()` that includes the associated server record
     * (sites_ftp_user_server_get) — useful when you need both in one call.
     *
     * @return array<string, mixed>
     */
    public function findWithServer(int $ftpUserId): array
    {
        return $this->call('sites_ftp_user_server_get', $ftpUserId);
    }
}

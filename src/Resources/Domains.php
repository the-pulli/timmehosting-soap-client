<?php

namespace Pulli\TimmeSoapClient\Resources;

use Pulli\TimmeSoapClient\Resource;

/**
 * `domains_*` — registrar-style domain records (separate from web vhosts
 * under `sites_web_domain_*`). Only present when ISPConfig's reseller /
 * domain-module is enabled on the panel.
 */
class Domains extends Resource
{
    /** @return array<string, mixed> */
    public function find(int $id): array
    {
        return $this->call('domains_domain_get', $id);
    }

    /** @param  array<string, mixed>  $params */
    public function add(int $clientId, array $params): int
    {
        return (int) $this->call('domains_domain_add', $clientId, $params);
    }

    public function delete(int $id): bool
    {
        return (bool) $this->call('domains_domain_delete', $id);
    }

    /** @return array<int, array<string, mixed>> */
    public function allByUser(int $clientId): array
    {
        $r = $this->call('domains_get_all_by_user', $clientId);

        return is_array($r) ? $r : [];
    }
}

<?php

namespace Pulli\TimmeSoapClient\Resources;

use Pulli\TimmeSoapClient\Enums\CronType;
use Pulli\TimmeSoapClient\Enums\Toggle;
use Pulli\TimmeSoapClient\Resource;

/**
 * `sites_cron_*` — panel-managed cron entries scoped to a site.
 */
class Cron extends Resource
{
    /**
     * Cron entries for a site.
     *
     * @return array<int, array<string, mixed>>
     */
    public function forSite(int $domainId): array
    {
        $r = $this->call('sites_cron_get', ['parent_domain_id' => $domainId]);

        return is_array($r) ? $r : [];
    }

    /** @return array<string, mixed> */
    public function find(int $cronId): array
    {
        return $this->call('sites_cron_get', $cronId);
    }

    /**
     * Add a cron entry under the given site. Schedule defaults to every minute.
     * Returns the new `cron_id`. Idempotency is the caller's job — typical
     * pattern: forSite() first, skip if a matching command already exists.
     */
    public function add(
        int $clientId,
        int $domainId,
        string $command,
        string $minute = '*',
        string $hour = '*',
        string $mday = '*',
        string $month = '*',
        string $wday = '*',
        CronType $type = CronType::Full,
        int $serverId = 1,
        Toggle $active = Toggle::Yes,
    ): int {
        return (int) $this->call('sites_cron_add', $clientId, [
            'server_id' => $serverId,
            'parent_domain_id' => $domainId,
            'type' => $type->value,
            'command' => $command,
            'run_min' => $minute,
            'run_hour' => $hour,
            'run_mday' => $mday,
            'run_month' => $month,
            'run_wday' => $wday,
            'active' => $active->value,
        ]);
    }

    /** @param  array<string, mixed>  $params */
    public function update(int $clientId, int $cronId, array $params): bool
    {
        return (bool) $this->call('sites_cron_update', $clientId, $cronId, $params);
    }

    public function delete(int $cronId): bool
    {
        return (bool) $this->call('sites_cron_delete', $cronId);
    }
}

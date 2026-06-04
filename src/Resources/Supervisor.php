<?php

namespace Pulli\TimmeSoapClient\Resources;

use Pulli\TimmeSoapClient\Resource;

/**
 * `sites_supervisor_*` — panel-managed supervisord jobs.
 *
 * Note: `sites_supervisor_add` is **not** exposed via the Remote API.
 * Jobs themselves must be created in the panel UI (Sites → Supervisor).
 * This resource lists and restarts the existing entries.
 */
class Supervisor extends Resource
{
    /**
     * Supervisor jobs for a site.
     *
     * @return array<int, array<string, mixed>>
     */
    public function forSite(int $domainId): array
    {
        $r = $this->call('sites_supervisor_get', ['parent_domain_id' => $domainId]);

        return is_array($r) ? $r : [];
    }

    public function restart(int $jobId): bool
    {
        return (bool) $this->call('sites_supervisor_restart', $jobId);
    }
}

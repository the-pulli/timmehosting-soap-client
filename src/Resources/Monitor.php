<?php

namespace Pulli\TimmeSoapClient\Resources;

use Pulli\TimmeSoapClient\Resource;

/**
 * `monitor_*` — ISPConfig's monitoring data feeds (service states, system
 * load, disk usage, mail/web logs, etc.).
 *
 * The monitor surface is broad; this resource wraps the common reads and
 * exposes a generic `state()` accessor for everything else.
 */
class Monitor extends Resource
{
    /** @return array<string, mixed> */
    public function serverState(int $serverId): array
    {
        return $this->call('monitor_get_server_state', $serverId);
    }

    /** @return array<int, array<string, mixed>> */
    public function dataLog(int $serverId, string $type): array
    {
        $r = $this->call('monitor_get_data', $type, $serverId);

        return is_array($r) ? $r : [];
    }

    /**
     * Generic monitor accessor. Wraps `monitor_get_<name>` — pass the suffix
     * lowercase, e.g. `state` → `monitor_get_state`.
     */
    public function state(string $name, mixed ...$args): mixed
    {
        return $this->call("monitor_get_{$name}", ...$args);
    }
}

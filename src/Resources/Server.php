<?php

namespace Pulli\TimmeSoapClient\Resources;

use Pulli\TimmeSoapClient\Resource;

/**
 * `server_*` — server-level discovery, config, IPs, fail2ban,
 * record-by-id, and arbitrary lookups.
 */
class Server extends Resource
{
    // ── Discovery ────────────────────────────────────────────────────

    /**
     * Names of every Remote API function the panel exposes to the current
     * user. The authoritative "is this method available?" source.
     *
     * @return array<int, string>
     */
    public function functionList(): array
    {
        $r = $this->call('server_get_function_list');

        return is_array($r) ? $r : [];
    }

    /**
     * Per-server function manifest (which functions are enabled on which
     * server, with role flags). Differs from functionList in scope.
     *
     * @return array<int, array<string, mixed>>
     */
    public function functions(): array
    {
        $r = $this->call('server_get_functions');

        return is_array($r) ? $r : [];
    }

    /** Resolve a numeric server_id from an IP address. */
    public function idByIp(string $ip): int
    {
        return (int) $this->call('server_get_serverid_by_ip', $ip);
    }

    /** Resolve a numeric server_id from a server hostname. */
    public function idByName(string $name): int
    {
        return (int) $this->call('server_get_serverid_by_name', $name);
    }

    /** Datalog progress flag (per-server queue health). */
    public function sysDatalogStatus(int $serverId): mixed
    {
        return $this->call('server_get_sys_datalog_status', $serverId);
    }

    /** @return array<string, mixed> */
    public function recordById(string $table, int $primaryId): array
    {
        return $this->call('server_get_record_by_id', $table, $primaryId);
    }

    /** @return array<string, mixed> */
    public function config(int $serverId): array
    {
        return $this->call('server_get', $serverId);
    }

    /** @return array<int, array<string, mixed>> */
    public function all(): array
    {
        $r = $this->call('server_get_all');

        return is_array($r) ? $r : [];
    }

    /** @return array<string, mixed> */
    public function appVersion(): array
    {
        return $this->call('server_get_app_version');
    }

    // ── Fail2ban (server_f2b_*) ──────────────────────────────────────
    //
    // ISPConfig exposes only `add`, `delete`, and `get_all` for f2b
    // black/white lists — there's no `_get` (single-record) or `_update`.

    /**
     * @param  array<string, mixed>  $params
     */
    public function addF2bBlacklist(int $clientId, array $params): int
    {
        return (int) $this->call('server_f2b_blacklist_add', $clientId, $params);
    }

    public function deleteF2bBlacklist(int $id): bool
    {
        return (bool) $this->call('server_f2b_blacklist_delete', $id);
    }

    /** @return array<int, array<string, mixed>> */
    public function f2bBlacklists(int $serverId): array
    {
        $r = $this->call('server_f2b_blacklist_get_all', $serverId);

        return is_array($r) ? $r : [];
    }

    /** @param  array<string, mixed>  $params */
    public function addF2bWhitelist(int $clientId, array $params): int
    {
        return (int) $this->call('server_f2b_whitelist_add', $clientId, $params);
    }

    public function deleteF2bWhitelist(int $id): bool
    {
        return (bool) $this->call('server_f2b_whitelist_delete', $id);
    }

    /** @return array<int, array<string, mixed>> */
    public function f2bWhitelists(int $serverId): array
    {
        $r = $this->call('server_f2b_whitelist_get_all', $serverId);

        return is_array($r) ? $r : [];
    }

    // ── Server-level IP addresses (server_ip_*) ──────────────────────

    /** @return array<string, mixed> */
    public function findIp(int $id): array
    {
        return $this->call('server_ip_get', $id);
    }

    /** @param  array<string, mixed>  $params */
    public function addIp(int $clientId, array $params): int
    {
        return (int) $this->call('server_ip_add', $clientId, $params);
    }

    /** @param  array<string, mixed>  $params */
    public function updateIp(int $clientId, int $id, array $params): bool
    {
        return (bool) $this->call('server_ip_update', $clientId, $id, $params);
    }

    public function deleteIp(int $id): bool
    {
        return (bool) $this->call('server_ip_delete', $id);
    }
}

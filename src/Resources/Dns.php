<?php

namespace Pulli\TimmeSoapClient\Resources;

use Pulli\TimmeSoapClient\Enums\CrudOp;
use Pulli\TimmeSoapClient\Enums\DnsRecordType;
use Pulli\TimmeSoapClient\Enums\Status;
use Pulli\TimmeSoapClient\Resource;

/**
 * `dns_*` — DNS zones and records.
 *
 * Every RR type ISPConfig exposes (A, AAAA, ALIAS, CNAME, HINFO, MX, NS,
 * PTR, RP, SRV, TXT, plus the v9+ additions DS/DNSKEY/CAA/TLSA/SSHFP via
 * the generic `record()` accessor) has the same CRUD pattern.
 */
class Dns extends Resource
{
    // ── Zones ─────────────────────────────────────────────────────────

    /** @return array<string, mixed> */
    public function findZone(int $zoneId): array
    {
        return $this->call('dns_zone_get', $zoneId);
    }

    /**
     * @param  array<string, mixed>  $filter
     * @return array<int, array<string, mixed>>
     */
    public function zones(array $filter = []): array
    {
        $r = $this->call('dns_zone_get', $filter);

        return is_array($r) ? $r : [];
    }

    /** @return array<int, array<string, mixed>> */
    public function zonesByUser(int $clientId, int $serverId): array
    {
        $r = $this->call('dns_zone_get_by_user', $clientId, $serverId);

        return is_array($r) ? $r : [];
    }

    /** Look up a zone_id by its origin (e.g. "example.com."). */
    public function zoneIdByOrigin(int $clientId, string $origin): int
    {
        return (int) $this->call('dns_zone_get_id', $clientId, $origin);
    }

    /**
     * @param  array<string, mixed>  $params
     */
    public function addZone(int $clientId, array $params): int
    {
        return (int) $this->call('dns_zone_add', $clientId, $params);
    }

    /** Create a zone from a server-side template. */
    public function addZoneFromTemplate(
        int $clientId,
        int $serverId,
        string $domain,
        string $ip,
        int $templateId,
        string $ns1,
        string $ns2,
        string $email,
    ): int {
        return (int) $this->call(
            'dns_templatezone_add', $clientId, $serverId, $domain, $ip, $templateId, $ns1, $ns2, $email,
        );
    }

    /** @param  array<string, mixed>  $params */
    public function updateZone(int $clientId, int $zoneId, array $params): bool
    {
        return (bool) $this->call('dns_zone_update', $clientId, $zoneId, $params);
    }

    public function deleteZone(int $zoneId): bool
    {
        return (bool) $this->call('dns_zone_delete', $zoneId);
    }

    public function setZoneStatus(int $zoneId, Status $status): mixed
    {
        return $this->call('dns_zone_set_status', $zoneId, $status->value);
    }

    /**
     * All resource records under a zone (any RR type, returned with `type`
     * column populated).
     *
     * @return array<int, array<string, mixed>>
     */
    public function recordsForZone(int $zoneId): array
    {
        $r = $this->call('dns_rr_get_all_by_zone', $zoneId);

        return is_array($r) ? $r : [];
    }

    // ── Generic per-record-type accessors ─────────────────────────────

    /**
     * Generic record accessor — composes `dns_<type>_<op>` for any record
     * type ISPConfig supports. Prefer the typed shortcuts (addA, addAaaa,
     * …) for the common types; this is the long-tail escape hatch.
     */
    public function record(DnsRecordType $type, CrudOp $op, mixed ...$args): mixed
    {
        return $this->call("dns_{$type->value}_{$op->value}", ...$args);
    }

    // ── Typed shortcuts: A ────────────────────────────────────────────

    /** @return array<string, mixed> */
    public function findA(int $id): array
    {
        return $this->call('dns_a_get', $id);
    }

    /** @param  array<string, mixed>  $params */
    public function addA(int $clientId, array $params): int
    {
        return (int) $this->call('dns_a_add', $clientId, $params);
    }

    /** @param  array<string, mixed>  $params */
    public function updateA(int $clientId, int $id, array $params): bool
    {
        return (bool) $this->call('dns_a_update', $clientId, $id, $params);
    }

    public function deleteA(int $id): bool
    {
        return (bool) $this->call('dns_a_delete', $id);
    }

    // ── AAAA ─────────────────────────────────────────────────────────

    /** @return array<string, mixed> */
    public function findAaaa(int $id): array
    {
        return $this->call('dns_aaaa_get', $id);
    }

    /** @param  array<string, mixed>  $params */
    public function addAaaa(int $clientId, array $params): int
    {
        return (int) $this->call('dns_aaaa_add', $clientId, $params);
    }

    /** @param  array<string, mixed>  $params */
    public function updateAaaa(int $clientId, int $id, array $params): bool
    {
        return (bool) $this->call('dns_aaaa_update', $clientId, $id, $params);
    }

    public function deleteAaaa(int $id): bool
    {
        return (bool) $this->call('dns_aaaa_delete', $id);
    }

    // ── ALIAS (ANAME) ────────────────────────────────────────────────

    /** @return array<string, mixed> */
    public function findAlias(int $id): array
    {
        return $this->call('dns_alias_get', $id);
    }

    /** @param  array<string, mixed>  $params */
    public function addAlias(int $clientId, array $params): int
    {
        return (int) $this->call('dns_alias_add', $clientId, $params);
    }

    /** @param  array<string, mixed>  $params */
    public function updateAlias(int $clientId, int $id, array $params): bool
    {
        return (bool) $this->call('dns_alias_update', $clientId, $id, $params);
    }

    public function deleteAlias(int $id): bool
    {
        return (bool) $this->call('dns_alias_delete', $id);
    }

    // ── CNAME ────────────────────────────────────────────────────────

    /** @return array<string, mixed> */
    public function findCname(int $id): array
    {
        return $this->call('dns_cname_get', $id);
    }

    /** @param  array<string, mixed>  $params */
    public function addCname(int $clientId, array $params): int
    {
        return (int) $this->call('dns_cname_add', $clientId, $params);
    }

    /** @param  array<string, mixed>  $params */
    public function updateCname(int $clientId, int $id, array $params): bool
    {
        return (bool) $this->call('dns_cname_update', $clientId, $id, $params);
    }

    public function deleteCname(int $id): bool
    {
        return (bool) $this->call('dns_cname_delete', $id);
    }

    // ── HINFO ────────────────────────────────────────────────────────

    /** @return array<string, mixed> */
    public function findHinfo(int $id): array
    {
        return $this->call('dns_hinfo_get', $id);
    }

    /** @param  array<string, mixed>  $params */
    public function addHinfo(int $clientId, array $params): int
    {
        return (int) $this->call('dns_hinfo_add', $clientId, $params);
    }

    /** @param  array<string, mixed>  $params */
    public function updateHinfo(int $clientId, int $id, array $params): bool
    {
        return (bool) $this->call('dns_hinfo_update', $clientId, $id, $params);
    }

    public function deleteHinfo(int $id): bool
    {
        return (bool) $this->call('dns_hinfo_delete', $id);
    }

    // ── MX ───────────────────────────────────────────────────────────

    /** @return array<string, mixed> */
    public function findMx(int $id): array
    {
        return $this->call('dns_mx_get', $id);
    }

    /** @param  array<string, mixed>  $params */
    public function addMx(int $clientId, array $params): int
    {
        return (int) $this->call('dns_mx_add', $clientId, $params);
    }

    /** @param  array<string, mixed>  $params */
    public function updateMx(int $clientId, int $id, array $params): bool
    {
        return (bool) $this->call('dns_mx_update', $clientId, $id, $params);
    }

    public function deleteMx(int $id): bool
    {
        return (bool) $this->call('dns_mx_delete', $id);
    }

    // ── NS ───────────────────────────────────────────────────────────

    /** @return array<string, mixed> */
    public function findNs(int $id): array
    {
        return $this->call('dns_ns_get', $id);
    }

    /** @param  array<string, mixed>  $params */
    public function addNs(int $clientId, array $params): int
    {
        return (int) $this->call('dns_ns_add', $clientId, $params);
    }

    /** @param  array<string, mixed>  $params */
    public function updateNs(int $clientId, int $id, array $params): bool
    {
        return (bool) $this->call('dns_ns_update', $clientId, $id, $params);
    }

    public function deleteNs(int $id): bool
    {
        return (bool) $this->call('dns_ns_delete', $id);
    }

    // ── PTR ──────────────────────────────────────────────────────────

    /** @return array<string, mixed> */
    public function findPtr(int $id): array
    {
        return $this->call('dns_ptr_get', $id);
    }

    /** @param  array<string, mixed>  $params */
    public function addPtr(int $clientId, array $params): int
    {
        return (int) $this->call('dns_ptr_add', $clientId, $params);
    }

    /** @param  array<string, mixed>  $params */
    public function updatePtr(int $clientId, int $id, array $params): bool
    {
        return (bool) $this->call('dns_ptr_update', $clientId, $id, $params);
    }

    public function deletePtr(int $id): bool
    {
        return (bool) $this->call('dns_ptr_delete', $id);
    }

    // ── RP ───────────────────────────────────────────────────────────

    /** @return array<string, mixed> */
    public function findRp(int $id): array
    {
        return $this->call('dns_rp_get', $id);
    }

    /** @param  array<string, mixed>  $params */
    public function addRp(int $clientId, array $params): int
    {
        return (int) $this->call('dns_rp_add', $clientId, $params);
    }

    /** @param  array<string, mixed>  $params */
    public function updateRp(int $clientId, int $id, array $params): bool
    {
        return (bool) $this->call('dns_rp_update', $clientId, $id, $params);
    }

    public function deleteRp(int $id): bool
    {
        return (bool) $this->call('dns_rp_delete', $id);
    }

    // ── SRV ──────────────────────────────────────────────────────────

    /** @return array<string, mixed> */
    public function findSrv(int $id): array
    {
        return $this->call('dns_srv_get', $id);
    }

    /** @param  array<string, mixed>  $params */
    public function addSrv(int $clientId, array $params): int
    {
        return (int) $this->call('dns_srv_add', $clientId, $params);
    }

    /** @param  array<string, mixed>  $params */
    public function updateSrv(int $clientId, int $id, array $params): bool
    {
        return (bool) $this->call('dns_srv_update', $clientId, $id, $params);
    }

    public function deleteSrv(int $id): bool
    {
        return (bool) $this->call('dns_srv_delete', $id);
    }

    // ── TXT ──────────────────────────────────────────────────────────

    /** @return array<string, mixed> */
    public function findTxt(int $id): array
    {
        return $this->call('dns_txt_get', $id);
    }

    /** @param  array<string, mixed>  $params */
    public function addTxt(int $clientId, array $params): int
    {
        return (int) $this->call('dns_txt_add', $clientId, $params);
    }

    /** @param  array<string, mixed>  $params */
    public function updateTxt(int $clientId, int $id, array $params): bool
    {
        return (bool) $this->call('dns_txt_update', $clientId, $id, $params);
    }

    public function deleteTxt(int $id): bool
    {
        return (bool) $this->call('dns_txt_delete', $id);
    }
}

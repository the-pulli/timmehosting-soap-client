<?php

namespace Pulli\TimmeSoapClient\Resources;

use Pulli\TimmeSoapClient\Resource;
use SoapFault;

/**
 * `client_*` — ISPConfig customer/reseller records.
 *
 * Note: `client_update` is not callable by non-reseller API users. If you
 * provisioned `pulli_server` as a Remote User (System → Remote Users), you
 * can read clients but updating one will throw a SOAP fault.
 */
class Clients extends Resource
{
    /** @return array<string, mixed> */
    public function find(int $clientId): array
    {
        return $this->call('client_get', $clientId);
    }

    /** @return array<int, array<string, mixed>> */
    public function all(): array
    {
        $r = $this->call('client_get_all');

        return is_array($r) ? $r : [];
    }

    /**
     * Resolve the `client_group_id` (the gid the panel uses for filesystem
     * ownership + multi-client scoping). Needed for `sites_*_update` calls.
     */
    public function groupId(int $clientId): int
    {
        return (int) $this->call('client_get_groupid', $clientId);
    }

    /** @return array<string, mixed>|null */
    public function findByUsername(string $username): ?array
    {
        try {
            $r = $this->call('client_get_by_username', $username);

            return is_array($r) ? $r : null;
        } catch (SoapFault) {
            return null;
        }
    }

    /**
     * Create a new client. Returns the new `client_id`.
     *
     * @param  array<string, mixed>  $params
     */
    public function add(array $params, ?int $resellerId = null): int
    {
        return (int) $this->call('client_add', $resellerId, $params);
    }

    /**
     * Update an existing client (reseller-API-user only — see class docblock).
     *
     * @param  array<string, mixed>  $params
     */
    public function update(int $clientId, ?int $resellerId, array $params): bool
    {
        return (bool) $this->call('client_update', $clientId, $resellerId, $params);
    }

    public function delete(int $clientId): bool
    {
        return (bool) $this->call('client_delete', $clientId);
    }

    /**
     * Delete a client AND every site/db/email/dns record they own. Use
     * with care — irreversible.
     */
    public function deleteEverything(int $clientId): bool
    {
        return (bool) $this->call('client_delete_everything', $clientId);
    }

    /**
     * Change a client's panel login password. Bypasses the normal
     * client_update path so it works for non-reseller API users too.
     */
    public function changePassword(int $clientId, string $newPassword): bool
    {
        return (bool) $this->call('client_change_password', $clientId, $newPassword);
    }

    public function idByUsername(string $username): int
    {
        return (int) $this->call('client_get_id', $username);
    }

    /** @return array<string, mixed>|null */
    public function findByCustomerNo(string $customerNo): ?array
    {
        $r = $this->call('client_get_by_customer_no', $customerNo);

        return is_array($r) ? $r : null;
    }

    public function emailContact(int $clientId): string
    {
        return (string) $this->call('client_get_emailcontact', $clientId);
    }

    /** @return array<int, array<string, mixed>> */
    public function sitesByUser(int $clientId): array
    {
        $r = $this->call('client_get_sites_by_user', $clientId);

        return is_array($r) ? $r : [];
    }

    // ── Additional client templates ───────────────────────────────────

    /** @return array<string, mixed> */
    public function findTemplateAdditional(int $id): array
    {
        return $this->call('client_template_additional_get', $id);
    }

    /** @param  array<string, mixed>  $params */
    public function addTemplateAdditional(int $clientId, array $params): int
    {
        return (int) $this->call('client_template_additional_add', $clientId, $params);
    }

    public function deleteTemplateAdditional(int $id): bool
    {
        return (bool) $this->call('client_template_additional_delete', $id);
    }

    /** @return array<int, array<string, mixed>> */
    public function allTemplates(): array
    {
        $r = $this->call('client_templates_get_all');

        return is_array($r) ? $r : [];
    }
}

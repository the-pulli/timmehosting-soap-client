<?php

namespace Pulli\TimmeSoapClient\Resources;

use Pulli\TimmeSoapClient\Enums\BackupAction;
use Pulli\TimmeSoapClient\Enums\Status;
use Pulli\TimmeSoapClient\Resource;

/**
 * `mail_*` — mail domains, mailboxes, aliases, forwards, filters,
 * spamfilter, transport, policies, relay recipients, fetchmail, quotas.
 *
 * Every entity follows the standard ISPConfig CRUD pattern unless noted.
 */
class Mail extends Resource
{
    // ── Domains ───────────────────────────────────────────────────────

    /** @return array<string, mixed> */
    public function findDomain(int $id): array
    {
        return $this->call('mail_domain_get', $id);
    }

    /** @return array<string, mixed>|null */
    public function findDomainByName(string $domain): ?array
    {
        $r = $this->call('mail_domain_get_by_domain', $domain);

        return is_array($r) ? $r : null;
    }

    /** @param  array<string, mixed>  $params */
    public function addDomain(int $clientId, array $params): int
    {
        return (int) $this->call('mail_domain_add', $clientId, $params);
    }

    /** @param  array<string, mixed>  $params */
    public function updateDomain(int $clientId, int $id, array $params): bool
    {
        return (bool) $this->call('mail_domain_update', $clientId, $id, $params);
    }

    public function deleteDomain(int $id): bool
    {
        return (bool) $this->call('mail_domain_delete', $id);
    }

    public function setDomainStatus(int $id, Status $status): mixed
    {
        return $this->call('mail_domain_set_status', $id, $status->value);
    }

    // ── Alias domains ─────────────────────────────────────────────────

    /** @return array<string, mixed> */
    public function findAliasdomain(int $id): array
    {
        return $this->call('mail_aliasdomain_get', $id);
    }

    /** @param  array<string, mixed>  $params */
    public function addAliasdomain(int $clientId, array $params): int
    {
        return (int) $this->call('mail_aliasdomain_add', $clientId, $params);
    }

    /** @param  array<string, mixed>  $params */
    public function updateAliasdomain(int $clientId, int $id, array $params): bool
    {
        return (bool) $this->call('mail_aliasdomain_update', $clientId, $id, $params);
    }

    public function deleteAliasdomain(int $id): bool
    {
        return (bool) $this->call('mail_aliasdomain_delete', $id);
    }

    // ── Mailboxes / users ─────────────────────────────────────────────

    /** @return array<string, mixed> */
    public function findUser(int $id): array
    {
        return $this->call('mail_user_get', $id);
    }

    /** @param  array<string, mixed>  $params */
    public function addUser(int $clientId, array $params): int
    {
        return (int) $this->call('mail_user_add', $clientId, $params);
    }

    /** @param  array<string, mixed>  $params */
    public function updateUser(int $clientId, int $id, array $params): bool
    {
        return (bool) $this->call('mail_user_update', $clientId, $id, $params);
    }

    public function deleteUser(int $id): bool
    {
        return (bool) $this->call('mail_user_delete', $id);
    }

    /** @return array<int, array<string, mixed>> */
    public function listUsers(int $serverId): array
    {
        $r = $this->call('mail_user_list', $serverId);

        return is_array($r) ? $r : [];
    }

    // ── Mailbox backups (mail_user_backup*) ───────────────────────────

    /** @return array<int, array<string, mixed>> */
    public function userBackups(int $userId): array
    {
        $r = $this->call('mail_user_backup_list', $userId);

        return is_array($r) ? $r : [];
    }

    /**
     * Trigger a backup action on an existing mailbox backup record.
     */
    public function userBackup(int $backupId, BackupAction $action): mixed
    {
        return $this->call('mail_user_backup', $backupId, $action->value);
    }

    /** Convenience: download-link shortcut. */
    public function userBackupDownloadLink(int $backupId): string
    {
        return (string) $this->userBackup($backupId, BackupAction::BackupDownloadLink);
    }

    /** Toggle the `backup_active` flag for a mailbox. */
    public function userBackupActive(int $serverId, int $mailUserId): mixed
    {
        return $this->call('mail_user_backup_active', $serverId, $mailUserId);
    }

    /** Trigger an immediate (out-of-schedule) backup for a mailbox. */
    public function userInstantBackup(int $serverId, int $mailUserId): mixed
    {
        return $this->call('mail_user_instant_backup', $serverId, $mailUserId);
    }

    // ── Per-user filters (mail_user_filter_*) ─────────────────────────

    /** @return array<string, mixed> */
    public function findUserFilter(int $id): array
    {
        return $this->call('mail_user_filter_get', $id);
    }

    /** @param  array<string, mixed>  $params */
    public function addUserFilter(int $clientId, array $params): int
    {
        return (int) $this->call('mail_user_filter_add', $clientId, $params);
    }

    /** @param  array<string, mixed>  $params */
    public function updateUserFilter(int $clientId, int $id, array $params): bool
    {
        return (bool) $this->call('mail_user_filter_update', $clientId, $id, $params);
    }

    public function deleteUserFilter(int $id): bool
    {
        return (bool) $this->call('mail_user_filter_delete', $id);
    }

    // ── Aliases / forwards / catchalls ────────────────────────────────

    /** @return array<string, mixed> */
    public function findAlias(int $id): array
    {
        return $this->call('mail_alias_get', $id);
    }

    /** @param  array<string, mixed>  $params */
    public function addAlias(int $clientId, array $params): int
    {
        return (int) $this->call('mail_alias_add', $clientId, $params);
    }

    /** @param  array<string, mixed>  $params */
    public function updateAlias(int $clientId, int $id, array $params): bool
    {
        return (bool) $this->call('mail_alias_update', $clientId, $id, $params);
    }

    public function deleteAlias(int $id): bool
    {
        return (bool) $this->call('mail_alias_delete', $id);
    }

    /** @return array<string, mixed> */
    public function findForward(int $id): array
    {
        return $this->call('mail_forward_get', $id);
    }

    /** @param  array<string, mixed>  $params */
    public function addForward(int $clientId, array $params): int
    {
        return (int) $this->call('mail_forward_add', $clientId, $params);
    }

    /** @param  array<string, mixed>  $params */
    public function updateForward(int $clientId, int $id, array $params): bool
    {
        return (bool) $this->call('mail_forward_update', $clientId, $id, $params);
    }

    public function deleteForward(int $id): bool
    {
        return (bool) $this->call('mail_forward_delete', $id);
    }

    /** @return array<string, mixed> */
    public function findCatchall(int $id): array
    {
        return $this->call('mail_catchall_get', $id);
    }

    /** @param  array<string, mixed>  $params */
    public function addCatchall(int $clientId, array $params): int
    {
        return (int) $this->call('mail_catchall_add', $clientId, $params);
    }

    /** @param  array<string, mixed>  $params */
    public function updateCatchall(int $clientId, int $id, array $params): bool
    {
        return (bool) $this->call('mail_catchall_update', $clientId, $id, $params);
    }

    public function deleteCatchall(int $id): bool
    {
        return (bool) $this->call('mail_catchall_delete', $id);
    }

    // ── Blacklists / whitelists ───────────────────────────────────────

    /** @return array<string, mixed> */
    public function findBlacklist(int $id): array
    {
        return $this->call('mail_blacklist_get', $id);
    }

    /** @param  array<string, mixed>  $params */
    public function addBlacklist(int $clientId, array $params): int
    {
        return (int) $this->call('mail_blacklist_add', $clientId, $params);
    }

    /** @param  array<string, mixed>  $params */
    public function updateBlacklist(int $clientId, int $id, array $params): bool
    {
        return (bool) $this->call('mail_blacklist_update', $clientId, $id, $params);
    }

    public function deleteBlacklist(int $id): bool
    {
        return (bool) $this->call('mail_blacklist_delete', $id);
    }

    /** @return array<string, mixed> */
    public function findWhitelist(int $id): array
    {
        return $this->call('mail_whitelist_get', $id);
    }

    /** @param  array<string, mixed>  $params */
    public function addWhitelist(int $clientId, array $params): int
    {
        return (int) $this->call('mail_whitelist_add', $clientId, $params);
    }

    /** @param  array<string, mixed>  $params */
    public function updateWhitelist(int $clientId, int $id, array $params): bool
    {
        return (bool) $this->call('mail_whitelist_update', $clientId, $id, $params);
    }

    public function deleteWhitelist(int $id): bool
    {
        return (bool) $this->call('mail_whitelist_delete', $id);
    }

    // ── Spamfilter (user / blacklist / whitelist) ─────────────────────

    /** @return array<string, mixed> */
    public function findSpamfilterUser(int $id): array
    {
        return $this->call('mail_spamfilter_user_get', $id);
    }

    /** @param  array<string, mixed>  $params */
    public function addSpamfilterUser(int $clientId, array $params): int
    {
        return (int) $this->call('mail_spamfilter_user_add', $clientId, $params);
    }

    /** @param  array<string, mixed>  $params */
    public function updateSpamfilterUser(int $clientId, int $id, array $params): bool
    {
        return (bool) $this->call('mail_spamfilter_user_update', $clientId, $id, $params);
    }

    public function deleteSpamfilterUser(int $id): bool
    {
        return (bool) $this->call('mail_spamfilter_user_delete', $id);
    }

    /** @return array<string, mixed> */
    public function findSpamfilterBlacklist(int $id): array
    {
        return $this->call('mail_spamfilter_blacklist_get', $id);
    }

    /** @param  array<string, mixed>  $params */
    public function addSpamfilterBlacklist(int $clientId, array $params): int
    {
        return (int) $this->call('mail_spamfilter_blacklist_add', $clientId, $params);
    }

    /** @param  array<string, mixed>  $params */
    public function updateSpamfilterBlacklist(int $clientId, int $id, array $params): bool
    {
        return (bool) $this->call('mail_spamfilter_blacklist_update', $clientId, $id, $params);
    }

    public function deleteSpamfilterBlacklist(int $id): bool
    {
        return (bool) $this->call('mail_spamfilter_blacklist_delete', $id);
    }

    /** @return array<string, mixed> */
    public function findSpamfilterWhitelist(int $id): array
    {
        return $this->call('mail_spamfilter_whitelist_get', $id);
    }

    /** @param  array<string, mixed>  $params */
    public function addSpamfilterWhitelist(int $clientId, array $params): int
    {
        return (int) $this->call('mail_spamfilter_whitelist_add', $clientId, $params);
    }

    /** @param  array<string, mixed>  $params */
    public function updateSpamfilterWhitelist(int $clientId, int $id, array $params): bool
    {
        return (bool) $this->call('mail_spamfilter_whitelist_update', $clientId, $id, $params);
    }

    public function deleteSpamfilterWhitelist(int $id): bool
    {
        return (bool) $this->call('mail_spamfilter_whitelist_delete', $id);
    }

    // ── Filters / policies / transport / relay / fetchmail ────────────

    /** @return array<string, mixed> */
    public function findFilter(int $id): array
    {
        return $this->call('mail_filter_get', $id);
    }

    /** @param  array<string, mixed>  $params */
    public function addFilter(int $clientId, array $params): int
    {
        return (int) $this->call('mail_filter_add', $clientId, $params);
    }

    /** @param  array<string, mixed>  $params */
    public function updateFilter(int $clientId, int $id, array $params): bool
    {
        return (bool) $this->call('mail_filter_update', $clientId, $id, $params);
    }

    public function deleteFilter(int $id): bool
    {
        return (bool) $this->call('mail_filter_delete', $id);
    }

    /** @return array<string, mixed> */
    public function findPolicy(int $id): array
    {
        return $this->call('mail_policy_get', $id);
    }

    /** @param  array<string, mixed>  $params */
    public function addPolicy(int $clientId, array $params): int
    {
        return (int) $this->call('mail_policy_add', $clientId, $params);
    }

    /** @param  array<string, mixed>  $params */
    public function updatePolicy(int $clientId, int $id, array $params): bool
    {
        return (bool) $this->call('mail_policy_update', $clientId, $id, $params);
    }

    public function deletePolicy(int $id): bool
    {
        return (bool) $this->call('mail_policy_delete', $id);
    }

    /** @return array<string, mixed> */
    public function findTransport(int $id): array
    {
        return $this->call('mail_transport_get', $id);
    }

    /** @param  array<string, mixed>  $params */
    public function addTransport(int $clientId, array $params): int
    {
        return (int) $this->call('mail_transport_add', $clientId, $params);
    }

    /** @param  array<string, mixed>  $params */
    public function updateTransport(int $clientId, int $id, array $params): bool
    {
        return (bool) $this->call('mail_transport_update', $clientId, $id, $params);
    }

    public function deleteTransport(int $id): bool
    {
        return (bool) $this->call('mail_transport_delete', $id);
    }

    /** @return array<string, mixed> */
    public function findRelayRecipient(int $id): array
    {
        return $this->call('mail_relay_recipient_get', $id);
    }

    /** @param  array<string, mixed>  $params */
    public function addRelayRecipient(int $clientId, array $params): int
    {
        return (int) $this->call('mail_relay_recipient_add', $clientId, $params);
    }

    /** @param  array<string, mixed>  $params */
    public function updateRelayRecipient(int $clientId, int $id, array $params): bool
    {
        return (bool) $this->call('mail_relay_recipient_update', $clientId, $id, $params);
    }

    public function deleteRelayRecipient(int $id): bool
    {
        return (bool) $this->call('mail_relay_recipient_delete', $id);
    }

    /** @return array<string, mixed> */
    public function findFetchmail(int $id): array
    {
        return $this->call('mail_fetchmail_get', $id);
    }

    /** @param  array<string, mixed>  $params */
    public function addFetchmail(int $clientId, array $params): int
    {
        return (int) $this->call('mail_fetchmail_add', $clientId, $params);
    }

    /** @param  array<string, mixed>  $params */
    public function updateFetchmail(int $clientId, int $id, array $params): bool
    {
        return (bool) $this->call('mail_fetchmail_update', $clientId, $id, $params);
    }

    public function deleteFetchmail(int $id): bool
    {
        return (bool) $this->call('mail_fetchmail_delete', $id);
    }

    // ── Quotas ────────────────────────────────────────────────────────

    /** @return array<string, mixed> */
    public function quotaByUser(string $username): array
    {
        return $this->call('mailquota_get_by_user', $username);
    }
}

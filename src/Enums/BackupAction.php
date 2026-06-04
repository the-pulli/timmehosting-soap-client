<?php

namespace Pulli\TimmeSoapClient\Enums;

/**
 * Action parameter for the `*_backup` SOAP functions (sites_web_domain_backup,
 * mail_user_backup). Selects which operation to perform on an existing
 * backup record.
 */
enum BackupAction: string
{
    case BackupDownload = 'backup_download';
    case BackupDownloadLink = 'backup_download_link';
    case BackupRestore = 'backup_restore';
}

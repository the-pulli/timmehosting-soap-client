<?php

namespace Pulli\TimmeSoapClient\Enums;

/**
 * Cron entry execution mode (ISPConfig `sites_cron.type` column).
 *
 * - Url:       fetches an HTTP URL on schedule
 * - Full:      executes the command with the site's shell environment
 * - Chrooted:  executes the command inside a chroot jail (web user only)
 */
enum CronType: string
{
    case Url = 'url';
    case Full = 'full';
    case Chrooted = 'chrooted';
}

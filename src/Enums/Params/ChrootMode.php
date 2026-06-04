<?php

namespace Pulli\TimmeSoapClient\Enums\Params;

/**
 * Shell-user chroot mode for `shell_user.chroot` (ISPConfig
 * `sites_shell_user_*`). `Jailkit` is the standard production setting on
 * TimmeHosting Scaleservers.
 */
enum ChrootMode: string
{
    case None = 'no';
    case Jailkit = 'jailkit';
    case SshChroot = 'ssh-chroot';
}

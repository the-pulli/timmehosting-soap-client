<?php

namespace Pulli\TimmeSoapClient\Enums;

/**
 * DNS resource record types ISPConfig exposes via the Remote API.
 * Backed values are the lowercase tokens used in the SOAP function names
 * (e.g. `dns_a_*`, `dns_aaaa_*`, `dns_cname_*`).
 *
 * If ISPConfig adds support for a record type not enumerated here, you
 * can still reach it via `$client->call('dns_<type>_<op>', ...)`.
 */
enum DnsRecordType: string
{
    case A = 'a';
    case Aaaa = 'aaaa';
    case Cname = 'cname';
    case Mx = 'mx';
    case Ns = 'ns';
    case Ptr = 'ptr';
    case Soa = 'soa';
    case Srv = 'srv';
    case Txt = 'txt';
    case Ds = 'ds';
    case Dnskey = 'dnskey';
    case Hinfo = 'hinfo';
    case Rp = 'rp';
    case Caa = 'caa';
    case Tlsa = 'tlsa';
    case Sshfp = 'sshfp';
}

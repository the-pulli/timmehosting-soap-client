<?php

namespace Pulli\TimmeSoapClient;

/**
 * Semantic ISPConfig SOAP API errors (e.g. empty session id, falsy return
 * from a call that promised true). `\SoapFault` still propagates from the
 * SOAP transport layer for network/auth/protocol errors.
 */
class Exception extends \RuntimeException {}

<?php

namespace Pulli\TimmeSoapClient;

/**
 * Base class for grouped resource accessors on Client (e.g. $client->sites,
 * $client->dns). Holds a back-reference to the Client and delegates SOAP
 * invocation through Client::call(), so resources don't manage session
 * state themselves.
 */
abstract class Resource
{
    public function __construct(protected readonly Client $client) {}

    /**
     * Invoke an ISPConfig SOAP function by name. Session id is injected
     * automatically as the first argument — pass only the remaining args.
     *
     * Resource methods are thin typed wrappers around this; use it directly
     * for any function the resource hasn't surfaced explicitly yet.
     */
    protected function call(string $function, mixed ...$args): mixed
    {
        return $this->client->call($function, ...$args);
    }
}

<?php

declare(strict_types=1);

namespace Turnstile\Client;

use Turnstile\Client\Abstract\RequestParameters as AbstractRequestParameters;

/**
 * @internal
 */
final class RequestParameters extends AbstractRequestParameters {
    public function __construct(
        public readonly string $secret,
        public readonly string $response,
        public readonly ?string $remoteip = null,
        public readonly ?string $idempotency_key = null,
    ) {}
}

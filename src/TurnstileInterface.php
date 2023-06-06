<?php

declare(strict_types=1);

namespace Turnstile;

use Turnstile\Client\{Client, ResponseBase};

interface TurnstileInterface {
    /**
     * @var string
     */
    public const SITE_VERIFY_URL = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';

    public function __construct(
        Client $client,
        string $secret,
        ?int $timeoutSeconds,
        ?string $hostname,
        ?string $action,
        ?string $cdata,
    );

    public function verify(string $response, ?string $remoteip, ?string $idempotencyKey): ResponseBase;
}

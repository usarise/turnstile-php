<?php

declare(strict_types=1);

namespace Turnstile;

use Turnstile\Client\{Client, ResponseBase};

/**
 * @api
 */
interface TurnstileInterface {
    /**
     * @var string
     */
    public const SITE_VERIFY_URL = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';

    /**
     * @var int
     */
    public const MAX_TOKEN_LENGTH = 2048;

    public function __construct(
        Client $client,
        string $secretKey,
        ?string $idempotencyKey,
    );

    public function verify(
        string $token,
        ?string $remoteIp,
        ?int $challengeTimeout,
        ?string $expectedHostname,
        ?string $expectedAction,
        ?string $expectedCdata,
    ): ResponseBase;
}

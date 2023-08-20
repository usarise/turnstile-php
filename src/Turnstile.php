<?php

declare(strict_types=1);

namespace Turnstile;

use Turnstile\Client\{Client, RequestParameters, Response};
use Turnstile\Error\Code as ErrorCode;

final class Turnstile implements TurnstileInterface {
    public function __construct(
        private readonly Client $client,
        private readonly string $secretKey,
        private readonly ?string $idempotencyKey = null,
    ) {
        if ($secretKey === '') {
            throw new TurnstileException('The secret key cannot be empty.');
        }
    }

    public function verify(
        string $token,
        ?string $remoteIp = null,
        ?int $challengeTimeout = null,
        ?string $expectedHostname = null,
        ?string $expectedAction = null,
        ?string $expectedCData = null,
    ): Response {
        if ($token === '') {
            return new Response(
                false,
                [ErrorCode::MISSING_INPUT_RESPONSE],
            );
        }

        if (\strlen($token) > self::MAX_LENGTH_TOKEN) {
            return new Response(
                false,
                [ErrorCode::INVALID_INPUT_RESPONSE],
            );
        }

        return $this->extendVerify(
            Response::decode(
                $this->client->sendRequest(
                    new RequestParameters(
                        $this->secretKey,
                        $token,
                        $remoteIp,
                        $this->idempotencyKey,
                    ),
                ),
            ),
            $challengeTimeout,
            $expectedHostname,
            $expectedAction,
            $expectedCData,
        );
    }

    private function extendVerify(
        Response $response,
        ?int $challengeTimeout,
        ?string $expectedHostname,
        ?string $expectedAction,
        ?string $expectedCData,
    ): Response {
        $errorCodes = [];

        if ($challengeTimeout !== null) {
            $challengeTs = strtotime((string) $response->challengeTs);

            if ((int) $challengeTs > 0 && (time() - $challengeTs) > $challengeTimeout) {
                $errorCodes[] = ErrorCode::CHALLENGE_TIMEOUT;
            }
        }

        if ($expectedHostname !== null && $expectedHostname !== $response->hostname) {
            $errorCodes[] = ErrorCode::HOSTNAME_MISMATCH;
        }

        if ($expectedAction !== null && $expectedAction !== $response->action) {
            $errorCodes[] = ErrorCode::ACTION_MISMATCH;
        }

        if ($expectedCData !== null && $expectedCData !== $response->cdata) {
            $errorCodes[] = ErrorCode::CDATA_MISMATCH;
        }

        if ($errorCodes) {
            return new Response(
                false,
                [
                    ...$response->errorCodes,
                    ...$errorCodes,
                ],
                ...[
                    ...\array_slice(
                        array: $response->toArray(strict: true),
                        offset: 2,
                    ),
                    'jsonDecode' => $response->toArray(),
                    'httpBody' => (string) $response,
                ],
            );
        }

        return $response;
    }
}

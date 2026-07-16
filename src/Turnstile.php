<?php

declare(strict_types=1);

namespace Turnstile;

use Psr\Http\Client\ClientInterface as PsrHttpClientInterface;
use Turnstile\Client\{Client, RequestParameters, Response};
use Turnstile\Error\Codes as ErrorCode;
use Turnstile\Exception\InvalidArgumentException;

/**
 * @api
 */
final class Turnstile implements TurnstileInterface {
    private readonly Client $client;

    public function __construct(
        Client|PsrHttpClientInterface $client,
        private readonly string $secretKey,
        private readonly ?string $idempotencyKey = null,
    ) {
        $this->client = ($client instanceof PsrHttpClientInterface) ? new Client($client) : $client;

        if ($secretKey === '') {
            throw new InvalidArgumentException('The secret key cannot be empty.');
        }
    }

    public function verify(
        string $token,
        ?string $remoteIp = null,
        ?int $challengeTimeout = null,
        ?string $expectedHostname = null,
        ?string $expectedAction = null,
        ?string $expectedCdata = null,
    ): Response {
        $errorInputResponse = match (true) {
            $token === '' => ErrorCode::MISSING_INPUT_RESPONSE,
            \strlen($token) > self::MAX_TOKEN_LENGTH => ErrorCode::INVALID_INPUT_RESPONSE,
            default => null,
        };

        if ($errorInputResponse) {
            return new Response(
                false,
                [$errorInputResponse],
            );
        }

        try {
            $httpResponse = $this->client->sendRequest(
                new RequestParameters(
                    $this->secretKey,
                    $token,
                    $remoteIp,
                    $this->idempotencyKey,
                ),
            );
        } catch (\Throwable $throwable) {
            return new Response(
                success: false,
                errorCodes: [ErrorCode::CONNECTION_FAILED],
                messages: [$throwable->getMessage()],
            );
        }

        return $this->enhancedVerify(
            Response::decode(
                httpResponse: $httpResponse,
            ),
            $challengeTimeout,
            $expectedHostname,
            $expectedAction,
            $expectedCdata,
        );
    }

    private function enhancedVerify(
        Response $response,
        ?int $challengeTimeout,
        ?string $expectedHostname,
        ?string $expectedAction,
        ?string $expectedCdata,
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

        if ($expectedCdata !== null && $expectedCdata !== $response->cdata) {
            $errorCodes[] = ErrorCode::CDATA_MISMATCH;
        }

        if ($errorCodes !== []) {
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
                    'httpResponse' => $response->httpResponse,
                    'jsonDecode' => $response->toArray(),
                    'httpBody' => (string) $response,
                ],
            );
        }

        return $response;
    }
}

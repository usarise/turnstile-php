<?php

declare(strict_types=1);

namespace Turnstile;

use Turnstile\Client\{Client, RequestParameters, Response};
use Turnstile\Error\Code as ErrorCode;

final class Turnstile implements TurnstileInterface {
    public function __construct(
        private readonly Client $client,
        private readonly string $secret,
        private readonly ?int $timeoutSeconds = null,
        private readonly ?string $hostname = null,
        private readonly ?string $action = null,
        private readonly ?string $cdata = null,
    ) {
        if ($secret === '') {
            throw new TurnstileException('The secret cannot be empty.');
        }
    }

    public function verify(string $response, ?string $remoteip = null): Response {
        if ($response === '') {
            return new Response(
                false,
                [ErrorCode::MISSING_INPUT_RESPONSE],
            );
        }

        return $this->extendVerify(
            Response::decode(
                $this->client->sendRequest(
                    new RequestParameters(
                        $this->secret,
                        $response,
                        $remoteip,
                    ),
                ),
            ),
        );
    }

    private function extendVerify(Response $response): Response {
        $errorCodes = [];

        if ($this->timeoutSeconds !== null) {
            $challengeTs = strtotime((string) $response->challengeTs);

            if ((int) $challengeTs > 0 && (time() - $challengeTs) > $this->timeoutSeconds) {
                $errorCodes[] = ErrorCode::CHALLENGE_TIMEOUT;
            }
        }

        if ($this->hostname !== null && $this->hostname !== $response->hostname) {
            $errorCodes[] = ErrorCode::HOSTNAME_MISMATCH;
        }

        if ($this->action !== null && $this->action !== $response->action) {
            $errorCodes[] = ErrorCode::ACTION_MISMATCH;
        }

        if ($this->cdata !== null && $this->cdata !== $response->cdata) {
            $errorCodes[] = ErrorCode::CDATA_MISMATCH;
        }

        if ($errorCodes) {
            return new Response(
                false,
                [
                    ...$response->errorCodes,
                    ...$errorCodes,
                ],
                ...\array_slice(
                    array: $response->toArray(),
                    offset: 2,
                ),
                ...[(string) $response],
            );
        }

        return $response;
    }
}

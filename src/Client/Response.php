<?php

declare(strict_types=1);

namespace Turnstile\Client;

use Turnstile\Error\Code as ErrorCode;

final class Response extends ResponseBase {
    /**
     * @param array<int, string> $errorCodes
     */
    public function __construct(
        public readonly bool $success,
        public readonly array $errorCodes,
        public readonly ?string $challengeTs = null,
        public readonly ?string $hostname = null,
        public readonly ?string $action = null,
        public readonly ?string $cdata = null,
        protected readonly string $httpBody = '',
    ) {
    }

    public static function decode(string $httpResponse): static {
        try {
            $dataResponse = json_decode(
                json: $httpResponse,
                associative: true,
                flags: JSON_PRESERVE_ZERO_FRACTION | JSON_THROW_ON_ERROR,
            );

            if (!$dataResponse) {
                return new self(
                    success: false,
                    errorCodes: [ErrorCode::UNKNOWN_ERROR],
                    httpBody: $httpResponse,
                );
            }

            if (!\is_array($dataResponse)) {
                return new self(
                    success: false,
                    errorCodes: [ErrorCode::UNKNOWN_ERROR],
                    httpBody: $httpResponse,
                );
            }

            $success = $dataResponse['success'] ?? false;
            $errorCodes = $dataResponse['error-codes'] ?? [];

            if ($success === false && $errorCodes === []) {
                $errorCodes[] = ErrorCode::UNKNOWN_ERROR;
            }

            $challengeTs = $dataResponse['challenge_ts'] ?? null;
            $hostname = $dataResponse['hostname'] ?? null;

            $action = $dataResponse['action'] ?? null;
            $cdata = $dataResponse['cdata'] ?? null;

            return new self(
                $success,
                $errorCodes,
                $challengeTs,
                $hostname,
                $action,
                $cdata,
                $httpResponse,
            );
        } catch (\JsonException) {
            return new self(
                success: false,
                errorCodes: [ErrorCode::INVALID_JSON],
                httpBody: $httpResponse,
            );
        }
    }
}

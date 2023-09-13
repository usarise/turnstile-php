<?php

declare(strict_types=1);

namespace Turnstile\Client;

use Turnstile\Error\Code as ErrorCode;

final class Response extends ResponseBase {
    /**
     * @param array<int, string> $errorCodes
     * @param array<string, mixed> $jsonDecode
     */
    public function __construct(
        public readonly bool $success,
        public readonly array $errorCodes,
        public readonly ?string $challengeTs = null,
        public readonly ?string $hostname = null,
        public readonly ?string $action = null,
        public readonly ?string $cdata = null,
        protected readonly array $jsonDecode = [],
        protected readonly string $httpBody = '',
    ) {}

    public static function decode(string $httpResponse): static {
        try {
            $jsonDecode = json_decode(
                json: $httpResponse,
                associative: true,
                flags: JSON_PRESERVE_ZERO_FRACTION | JSON_THROW_ON_ERROR,
            );

            if (!$jsonDecode) {
                return new self(
                    success: false,
                    errorCodes: [ErrorCode::UNKNOWN_ERROR],
                    httpBody: $httpResponse,
                );
            }

            if (!\is_array($jsonDecode)) {
                return new self(
                    success: false,
                    errorCodes: [ErrorCode::UNKNOWN_ERROR],
                    httpBody: $httpResponse,
                );
            }

            $success = $jsonDecode['success'] ?? false;
            $errorCodes = $jsonDecode['error-codes'] ?? [];

            if ($success === false && $errorCodes === []) {
                $errorCodes[] = ErrorCode::UNKNOWN_ERROR;
            }

            $challengeTs = $jsonDecode['challenge_ts'] ?? null;
            $hostname = $jsonDecode['hostname'] ?? null;

            $action = $jsonDecode['action'] ?? null;
            $cdata = $jsonDecode['cdata'] ?? null;

            return new self(
                $success,
                $errorCodes,
                $challengeTs,
                $hostname,
                $action,
                $cdata,
                $jsonDecode,
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

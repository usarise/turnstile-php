<?php

declare(strict_types=1);

namespace Turnstile\Client;

use Psr\Http\Message\ResponseInterface;
use Turnstile\Client\Abstract\Response as AbstractResponse;
use Turnstile\Error\Codes as ErrorCode;

/**
 * @api
 */
final class Response extends AbstractResponse {
    /**
     * @param array<int, string> $errorCodes
     * @param array<string, mixed>|null $metadata
     * @param array<int, string> $messages
     * @param array<string, mixed> $jsonDecode
     */
    public function __construct(
        public readonly bool $success,
        public readonly array $errorCodes,
        public readonly ?string $challengeTs = null,
        public readonly ?string $hostname = null,
        public readonly ?string $action = null,
        public readonly ?string $cdata = null,
        public readonly ?array $metadata = null,
        public readonly ?array $messages = null,
        public readonly ?ResponseInterface $httpResponse = null,
        protected readonly array $jsonDecode = [],
        protected readonly string $httpBody = '',
    ) {}

    public static function decode(ResponseInterface $httpResponse): static {
        $httpBody = (string) $httpResponse->getBody();

        try {
            $jsonDecode = json_decode(
                json: $httpBody,
                associative: true,
                flags: JSON_THROW_ON_ERROR,
            );

            if (!$jsonDecode) {
                return new self(
                    success: false,
                    errorCodes: [ErrorCode::UNKNOWN_ERROR],
                    httpResponse: $httpResponse,
                    httpBody: $httpBody,
                );
            }

            if (!\is_array($jsonDecode)) {
                return new self(
                    success: false,
                    errorCodes: [ErrorCode::UNKNOWN_ERROR],
                    httpResponse: $httpResponse,
                    httpBody: $httpBody,
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

            $metadata = $jsonDecode['metadata'] ?? null;
            $messages = $jsonDecode['messages'] ?? null;

            return new self(
                $success,
                $errorCodes,
                $challengeTs,
                $hostname,
                $action,
                $cdata,
                $metadata,
                $messages,
                $httpResponse,
                $jsonDecode,
                $httpBody,
            );
        } catch (\JsonException $jsonException) {
            return new self(
                success: false,
                errorCodes: [ErrorCode::INVALID_JSON],
                messages: [$jsonException->getMessage()],
                httpResponse: $httpResponse,
                httpBody: $httpBody,
            );
        } catch (\Throwable $throwable) {
            return new self(
                success: false,
                errorCodes: [ErrorCode::UNKNOWN_ERROR],
                messages: [$throwable->getMessage()],
                httpResponse: $httpResponse,
                httpBody: $httpBody,
            );
        }
    }
}

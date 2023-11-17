<?php

declare(strict_types=1);

namespace Turnstile\Error;

final class Code {
    /**
     * @var string
     */
    public const MISSING_INPUT_RESPONSE = 'missing-input-response';

    /**
     * @var string
     */
    public const INVALID_INPUT_RESPONSE = 'invalid-input-response';

    /**
     * @var string
     */
    public const CHALLENGE_TIMEOUT = 'challenge-timeout';

    /**
     * @var string
     */
    public const HOSTNAME_MISMATCH = 'hostname-mismatch';

    /**
     * @var string
     */
    public const ACTION_MISMATCH = 'action-mismatch';

    /**
     * @var string
     */
    public const CDATA_MISMATCH = 'cdata-mismatch';

    /**
     * @var string
     */
    public const INVALID_JSON = 'invalid-json';

    /**
     * @var string
     */
    public const UNKNOWN_ERROR = 'unknown-error';

    /**
     * @param array<int, string> $codes
     * @param array<string, string> $descriptions
     * @return array<int, string>
     */
    public static function toDescription(array $codes, array $descriptions = Description::TEXTS): array {
        return array_map(
            static fn($code): string => $descriptions[$code] ?? $code,
            $codes,
        );
    }
}

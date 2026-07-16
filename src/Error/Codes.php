<?php

declare(strict_types=1);

namespace Turnstile\Error;

/**
 * @api
 */
final class Codes {
    /**
     * @var string
     */
    public const MISSING_INPUT_SECRET = 'missing-input-secret';

    /**
     * @var string
     */
    public const INVALID_INPUT_SECRET = 'invalid-input-secret';

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
    public const BAD_REQUEST = 'bad-request';

    /**
     * @var string
     */
    public const TIMEOUT_OR_DUPLICATE = 'timeout-or-duplicate';

    /**
     * @var string
     */
    public const INTERNAL_ERROR = 'internal-error';

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
    public const CONNECTION_FAILED = 'connection-failed';

    /**
     * @var string
     */
    public const INVALID_JSON = 'invalid-json';

    /**
     * @var string
     */
    public const UNKNOWN_ERROR = 'unknown-error';
}

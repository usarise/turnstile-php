<?php

declare(strict_types=1);

namespace Turnstile\Error;

final class Description {
    /**
     * @var array<string, string>
     */
    public const TEXTS = [
        'missing-input-secret' => 'The secret parameter was not passed',
        'invalid-input-secret' => 'The secret parameter was invalid or did not exist',
        'missing-input-response' => 'The response parameter (token) was not passed',
        'invalid-input-response' => 'The response parameter (token) is invalid or has expired. Most of the time, this means a fake token has been used. If the error persists, contact customer support',
        'bad-request' => 'The request was rejected because it was malformed',
        'timeout-or-duplicate' => 'The response parameter (token) has already been validated before. This means that the token was issued five minutes ago and is no longer valid, or it was already redeemed',
        'internal-error' => 'An internal error happened while validating the response. The request can be retried',
        'challenge-timeout' => 'Challenge timeout',
        'hostname-mismatch' => 'Expected hostname did not match',
        'action-mismatch' => 'Expected action did not match',
        'cdata-mismatch' => 'Expected cdata did not match',
        'invalid-json' => 'Invalid JSON received',
        'unknown-error' => 'Not a success, but no error codes received',
    ];
}

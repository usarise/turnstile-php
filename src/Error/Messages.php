<?php

declare(strict_types=1);

namespace Turnstile\Error;

/**
 * @api
 */
final class Messages {
    /**
     * @var array<string, string>
     */
    public const DESCRIPTION = [
        Codes::MISSING_INPUT_SECRET => 'Secret parameter not provided',
        Codes::INVALID_INPUT_SECRET => 'Secret key is invalid or expired',
        Codes::MISSING_INPUT_RESPONSE => 'Response parameter was not provided',
        Codes::INVALID_INPUT_RESPONSE => 'Token is invalid, malformed, or expired',
        Codes::BAD_REQUEST => 'Request is malformed',
        Codes::TIMEOUT_OR_DUPLICATE => 'Token has already been validated',
        Codes::INTERNAL_ERROR => 'Internal error occurred',
        Codes::CHALLENGE_TIMEOUT => 'Token is expired',
        Codes::HOSTNAME_MISMATCH => 'Hostname mismatch',
        Codes::ACTION_MISMATCH => 'Action mismatch',
        Codes::CDATA_MISMATCH => 'cData mismatch',
        Codes::INVALID_JSON => 'Invalid JSON received',
        Codes::UNKNOWN_ERROR => 'Not a success, but no error codes received',
    ];

    /**
     * @var array<string, string>
     */
    public const ACTION_REQUIRED = [
        Codes::MISSING_INPUT_SECRET => 'Ensure secret key is included',
        Codes::INVALID_INPUT_SECRET => 'Check your secret key in the Cloudflare dashboard',
        Codes::MISSING_INPUT_RESPONSE => 'Ensure token is included',
        Codes::INVALID_INPUT_RESPONSE => 'User should retry the challenge',
        Codes::BAD_REQUEST => 'Check request format and parameters',
        Codes::TIMEOUT_OR_DUPLICATE => 'Each token can only be used once',
        Codes::INTERNAL_ERROR => 'Retry the request',
        Codes::CHALLENGE_TIMEOUT => 'User should retry the challenge',
        Codes::HOSTNAME_MISMATCH => 'Check hostname where the challenge was served',
        Codes::ACTION_MISMATCH => 'Check data-action attribute',
        Codes::CDATA_MISMATCH => 'Check data-cdata attribute',
        Codes::INVALID_JSON => 'Check network or Cloudflare Turnstile endpoint',
        Codes::UNKNOWN_ERROR => 'Check Cloudflare Turnstile endpoint',
    ];
}

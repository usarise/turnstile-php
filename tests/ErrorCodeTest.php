<?php

declare(strict_types=1);

namespace TurnstileTests;

use PHPUnit\Framework\TestCase;
use Turnstile\Error\{Codes, Messages};

final class ErrorCodeTest extends TestCase {
    public function testCodes(): void {
        $this->assertSame(
            'missing-input-secret',
            Codes::MISSING_INPUT_SECRET,
        );
        $this->assertSame(
            'invalid-input-secret',
            Codes::INVALID_INPUT_SECRET,
        );
        $this->assertSame(
            'missing-input-response',
            Codes::MISSING_INPUT_RESPONSE,
        );
        $this->assertSame(
            'invalid-input-response',
            Codes::INVALID_INPUT_RESPONSE,
        );
        $this->assertSame(
            'bad-request',
            Codes::BAD_REQUEST,
        );
        $this->assertSame(
            'timeout-or-duplicate',
            Codes::TIMEOUT_OR_DUPLICATE,
        );
        $this->assertSame(
            'internal-error',
            Codes::INTERNAL_ERROR,
        );
        $this->assertSame(
            'challenge-timeout',
            Codes::CHALLENGE_TIMEOUT,
        );
        $this->assertSame(
            'hostname-mismatch',
            Codes::HOSTNAME_MISMATCH,
        );
        $this->assertSame(
            'action-mismatch',
            Codes::ACTION_MISMATCH,
        );
        $this->assertSame(
            'cdata-mismatch',
            Codes::CDATA_MISMATCH,
        );
        $this->assertSame(
            'connection-failed',
            Codes::CONNECTION_FAILED,
        );
        $this->assertSame(
            'invalid-json',
            Codes::INVALID_JSON,
        );
        $this->assertSame(
            'unknown-error',
            Codes::UNKNOWN_ERROR,
        );
    }

    public function testMessages(): void {
        $this->assertSame(
            [
                'missing-input-secret' => 'Secret parameter not provided',
                'invalid-input-secret' => 'Secret key is invalid or expired',
                'missing-input-response' => 'Response parameter was not provided',
                'invalid-input-response' => 'Token is invalid, malformed, or expired',
                'bad-request' => 'Request is malformed',
                'timeout-or-duplicate' => 'Token has already been validated',
                'internal-error' => 'Internal error occurred',
                'challenge-timeout' => 'Token is expired',
                'hostname-mismatch' => 'Hostname mismatch',
                'action-mismatch' => 'Action mismatch',
                'cdata-mismatch' => 'cData mismatch',
                'connection-failed' => 'Not connect to service',
                'invalid-json' => 'Invalid JSON received',
                'unknown-error' => 'Not a success, but no error codes received',
            ],
            Messages::DESCRIPTION,
        );
        $this->assertSame(
            [
                'missing-input-secret' => 'Ensure secret key is included',
                'invalid-input-secret' => 'Check your secret key in the Cloudflare dashboard',
                'missing-input-response' => 'Ensure token is included',
                'invalid-input-response' => 'User should retry the challenge',
                'bad-request' => 'Check request format and parameters',
                'timeout-or-duplicate' => 'Each token can only be used once',
                'internal-error' => 'Retry the request',
                'challenge-timeout' => 'User should retry the challenge',
                'hostname-mismatch' => 'Check hostname where the challenge was served',
                'action-mismatch' => 'Check data-action attribute',
                'cdata-mismatch' => 'Check data-cdata attribute',
                'connection-failed' => 'Check network or Cloudflare Turnstile endpoint availability',
                'invalid-json' => 'Check network or Cloudflare Turnstile endpoint',
                'unknown-error' => 'Check Cloudflare Turnstile endpoint',
            ],
            Messages::ACTION_REQUIRED,
        );
    }
}

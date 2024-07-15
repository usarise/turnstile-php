<?php

declare(strict_types=1);

namespace TurnstileTests;

use PHPUnit\Framework\TestCase;
use Turnstile\Error\Code as ErrorCode;

final class ErrorCodeTest extends TestCase {
    public function testToDescriptionShort(): void {
        $this->assertEquals(
            [
                'The response parameter was not passed',
            ],
            ErrorCode::toDescription(
                [
                    'missing-input-response',
                ],
            ),
        );
    }

    public function testToDescriptionFull(): void {
        $this->assertEquals(
            [
                'The secret parameter was not passed',
                'The secret parameter was invalid or did not exist',
                'The response parameter (token) was not passed',
                'The response parameter (token) is invalid or has expired. Most of the time, this means a fake token has been used. If the error persists, contact customer support',
                'The widget ID extracted from the parsed site secret key was invalid or did not exist',
                'The secret extracted from the parsed site secret key was invalid',
                'The request was rejected because it was malformed',
                'The response parameter (token) has already been validated before. This means that the token was issued five minutes ago and is no longer valid, or it was already redeemed',
                'An internal error happened while validating the response. The request can be retried',
                'Challenge timeout',
                'Expected hostname did not match',
                'Expected action did not match',
                'Expected cdata did not match',
                'Invalid JSON received',
                'Not a success, but no error codes received',
            ],
            ErrorCode::toDescription(
                [
                    'missing-input-secret',
                    'invalid-input-secret',
                    'missing-input-response',
                    'invalid-input-response',
                    'invalid-widget-id',
                    'invalid-parsed-secret',
                    'bad-request',
                    'timeout-or-duplicate',
                    'internal-error',
                    'challenge-timeout',
                    'hostname-mismatch',
                    'action-mismatch',
                    'cdata-mismatch',
                    'invalid-json',
                    'unknown-error',
                ],
            ),
        );
    }

    public function testToDescriptionNotValue(): void {
        $this->assertEquals(
            [
                'test-error',
            ],
            ErrorCode::toDescription(
                [
                    'test-error',
                ],
            ),
        );

        $this->assertEquals(
            [
                'The response parameter was not passed',
                'test-error',
            ],
            ErrorCode::toDescription(
                [
                    ErrorCode::MISSING_INPUT_RESPONSE,
                    'test-error',
                ],
            ),
        );
    }

    public function testToDescriptionCustomTexts(): void {
        $this->assertEquals(
            [
                'Test error',
            ],
            ErrorCode::toDescription(
                [
                    'test-error',
                ],
                [
                    'test-error' => 'Test error',
                ],
            ),
        );

        $this->assertEquals(
            [
                'missing-input-response',
                'Test error',
            ],
            ErrorCode::toDescription(
                [
                    ErrorCode::MISSING_INPUT_RESPONSE,
                    'test-error',
                ],
                [
                    'test-error' => 'Test error',
                ],
            ),
        );
    }
}

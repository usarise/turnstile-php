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
                'The response parameter was not passed',
                'The response parameter is invalid or has expired',
                'The request was rejected because it was malformed',
                'The response parameter has already been validated before',
                'An internal error happened while validating the response. The request can be retried',
                'Challenge timeout',
                'Expected hostname did not match',
                'Expected action did not match',
                'Expected cdata did not match',
                'Invalid JSON received',
                'Not a success, but no error codes received!',
            ],
            ErrorCode::toDescription(
                [
                    'missing-input-secret',
                    'invalid-input-secret',
                    'missing-input-response',
                    'invalid-input-response',
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

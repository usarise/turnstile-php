<?php

declare(strict_types=1);

namespace TurnstileTests;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\Psr18Client;
use Turnstile\Turnstile;

final class TurnstileIntegrationTest extends TestCase {
    public function testVerify(): void {
        $response = (new Turnstile(
            client: new Psr18Client(),
            secretKey: '1x0000000000000000000000000000000AA',
        ))
        ->verify('token', '127.0.0.1')
        ;

        $this->assertTrue($response->success);
        $this->assertEquals(
            [],
            $response->errorCodes,
        );
        $this->assertEquals(
            ['result_with_testing_key' => true],
            $response->metadata,
        );
    }

    public function testChallengeTimeoutValidation(): void {
        $response = (new Turnstile(
            client: new Psr18Client(),
            secretKey: '1x0000000000000000000000000000000AA',
        ))
        ->verify(
            token: 'token',
            challengeTimeout: 30,
        )
        ;

        $this->assertTrue($response->success);
        $this->assertIsString($response->challengeTs);
    }

    public function testBadChallengeTimeoutValidation(): void {
        $response = (new Turnstile(
            client: new Psr18Client(),
            secretKey: '1x0000000000000000000000000000000AA',
        ))
        ->verify(
            token: 'token',
            challengeTimeout: -30,
        )
        ;

        $this->assertFalse($response->success);
        $this->assertEquals(
            ['challenge-timeout'],
            $response->errorCodes,
        );
        $this->assertIsString($response->challengeTs);
    }

    public function testHostnameValidation(): void {
        $response = (new Turnstile(
            client: new Psr18Client(),
            secretKey: '1x0000000000000000000000000000000AA',
        ))
        ->verify(
            token: 'token',
            expectedHostname: 'example.com',
        )
        ;

        $this->assertTrue($response->success);
        $this->assertEquals(
            'example.com',
            $response->hostname,
        );
    }

    public function testBadHostnameValidation(): void {
        $response = (new Turnstile(
            client: new Psr18Client(),
            secretKey: '1x0000000000000000000000000000000AA',
        ))
        ->verify(
            token: 'token',
            expectedHostname: 'localhost',
        )
        ;

        $this->assertFalse($response->success);
        $this->assertEquals(
            ['hostname-mismatch'],
            $response->errorCodes,
        );
        $this->assertEquals(
            'example.com',
            $response->hostname,
        );
    }

    public function testError(): void {
        $response = (new Turnstile(
            client: new Psr18Client(),
            secretKey: '2x0000000000000000000000000000000AA',
        ))
        ->verify('invalid')
        ;

        $this->assertFalse($response->success);
        $this->assertEquals(
            ['invalid-input-response'],
            $response->errorCodes,
        );
        $this->assertEquals(
            ['result_with_testing_key' => true],
            $response->metadata,
        );
        $this->assertEquals(
            [
                'success' => false,
                'errorCodes' => ['invalid-input-response'],
                'challengeTs' => null,
                'hostname' => null,
                'action' => null,
                'cdata' => null,
                'metadata' => [
                    'result_with_testing_key' => true,
                ],
            ],
            $response->toArray(strict: true),
        );
        $this->assertEquals(
            [
                'success' => false,
                'error-codes' => ['invalid-input-response'],
                'messages' => [],
                'metadata' => [
                    'result_with_testing_key' => true,
                ],
            ],
            $response->toArray(strict: false),
        );
        $this->assertEquals(
            '{"success":false,"error-codes":["invalid-input-response"],"messages":[],"metadata":{"result_with_testing_key":true}}',
            (string) $response,
        );
    }
}

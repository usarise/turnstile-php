<?php

declare(strict_types=1);

namespace TurnstileTests;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\Psr18Client;
use Turnstile\Client\Client;
use Turnstile\Turnstile;

final class TurnstileIntegrationTest extends TestCase {
    public function testVerify(): void {
        $response = (new Turnstile(
            client: new Client(
                new Psr18Client(),
            ),
            secretKey: '1x0000000000000000000000000000000AA',
        ))
        ->verify('token', '127.0.0.1')
        ;

        $this->assertTrue($response->success);
        $this->assertEquals(
            $response->errorCodes,
            [],
        );
        $this->assertEquals(
            $response->hostname,
            'example.com',
        );
    }

    public function testError(): void {
        $response = (new Turnstile(
            client: new Client(
                new Psr18Client(),
            ),
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
            [
                'success' => false,
                'errorCodes' => ['invalid-input-response'],
                'challengeTs' => null,
                'hostname' => null,
                'action' => null,
                'cdata' => null,
            ],
            $response->toArray(),
        );
        $this->assertEquals(
            '{"success":false,"error-codes":["invalid-input-response"],"messages":[]}',
            (string) $response,
        );
    }
}

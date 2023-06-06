<?php

declare(strict_types=1);

namespace TurnstileTests\Client;

use PHPUnit\Framework\TestCase;
use Turnstile\Client\RequestParameters;

final class RequestParametersTest extends TestCase {
    public function testToArrayShort(): void {
        $requestParams = new RequestParameters(
            'secret',
            'response',
        );

        $this->assertEquals(
            [
                'secret' => 'secret',
                'response' => 'response',
            ],
            $requestParams->toArray(),
        );
    }

    public function testToArrayFull(): void {
        $requestParams = new RequestParameters(
            'secret',
            'response',
            'remoteip',
            'idempotencyKey',
        );

        $this->assertEquals(
            [
                'secret' => 'secret',
                'response' => 'response',
                'remoteip' => 'remoteip',
                'idempotency_key' => 'idempotencyKey',
            ],
            $requestParams->toArray(),
        );
    }

    public function testToStringShort(): void {
        $requestParams = new RequestParameters(
            'secret',
            'response',
        );

        $this->assertEquals(
            'secret=secret&response=response',
            (string) $requestParams,
        );
    }

    public function testToStringFull(): void {
        $requestParams = new RequestParameters(
            'secret',
            'response',
            'remoteip',
            'idempotencyKey',
        );

        $this->assertEquals(
            'secret=secret&response=response&remoteip=remoteip&idempotency_key=idempotencyKey',
            (string) $requestParams,
        );
    }
}

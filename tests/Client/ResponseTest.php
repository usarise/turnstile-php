<?php

declare(strict_types=1);

namespace TurnstileTests\Client;

use PHPUnit\Framework\TestCase;
use Turnstile\Client\Response;

final class ResponseTest extends TestCase {
    public function testDecodeSimple(): void {
        $responseDecode = Response::decode('{"success": true}');

        $this->assertTrue($responseDecode->success);
        $this->assertEquals(
            [],
            $responseDecode->errorCodes,
        );

        $this->assertNull($responseDecode->hostname);
        $this->assertNull($responseDecode->challengeTs);
        $this->assertNull($responseDecode->action);
        $this->assertNull($responseDecode->cdata);

        $this->assertEquals(
            [
                'success' => true,
                'errorCodes' => [],
                'challengeTs' => null,
                'hostname' => null,
                'action' => null,
                'cdata' => null,
            ],
            $responseDecode->toArray(strict: true),
        );
        $this->assertEquals(
            '{"success": true}',
            (string) $responseDecode,
        );
    }

    public function testDecodeFull(): void {
        $challengeTs = gmdate('Y-m-d\TH:i:s.vp');
        $httpResponse = '{"success": false, "error-codes": ["test-error"], "hostname": "localhost.test", "challenge_ts": "' . $challengeTs . '", "action": "login", "cdata": "sessionid-123456789"}';
        $responseDecode = Response::decode($httpResponse);

        $this->assertFalse($responseDecode->success);
        $this->assertEquals(
            ['test-error'],
            $responseDecode->errorCodes,
        );

        $this->assertEquals(
            'localhost.test',
            $responseDecode->hostname,
        );
        $this->assertEquals(
            $challengeTs,
            $responseDecode->challengeTs,
        );
        $this->assertEquals(
            'login',
            $responseDecode->action,
        );
        $this->assertEquals(
            'sessionid-123456789',
            $responseDecode->cdata,
        );

        $this->assertEquals(
            [
                'success' => false,
                'errorCodes' => ['test-error'],
                'challengeTs' => $challengeTs,
                'hostname' => 'localhost.test',
                'action' => 'login',
                'cdata' => 'sessionid-123456789',
            ],
            $responseDecode->toArray(strict: true),
        );
        $this->assertEquals(
            $httpResponse,
            (string) $responseDecode,
        );
    }

    public function testToArraySuccessTrue(): void {
        $response = new Response(true, []);

        $this->assertEquals(
            [
                'success' => true,
                'errorCodes' => [],
                'challengeTs' => null,
                'hostname' => null,
                'action' => null,
                'cdata' => null,
            ],
            $response->toArray(strict: true),
        );

        $this->assertEquals(
            [],
            $response->toArray(),
        );
    }

    public function testToArraySuccessFalse(): void {
        $response = new Response(
            success: false,
            errorCodes: ['test-error'],
        );

        $this->assertEquals(
            [
                'success' => false,
                'errorCodes' => ['test-error'],
                'challengeTs' => null,
                'hostname' => null,
                'action' => null,
                'cdata' => null,
            ],
            $response->toArray(strict: true),
        );

        $this->assertEquals(
            [],
            $response->toArray(),
        );
    }

    public function testToArrayJsonDecode(): void {
        $response = new Response(
            success: true,
            errorCodes: [],
            jsonDecode: ['test' => 'jsonDecode'],
        );

        $this->assertEquals(
            [
                'success' => true,
                'errorCodes' => [],
                'challengeTs' => null,
                'hostname' => null,
                'action' => null,
                'cdata' => null,
            ],
            $response->toArray(strict: true),
        );

        $this->assertEquals(
            ['test' => 'jsonDecode'],
            $response->toArray(),
        );

        $this->assertEquals(
            ['test' => 'jsonDecode'],
            $response->toArray(strict: false),
        );
    }

    public function testToString(): void {
        $response = new Response(true, []);

        $this->assertEquals(
            '',
            (string) $response,
        );

        $response = new Response(
            success: false,
            errorCodes: [],
            httpBody: 'httpResponse',
        );

        $this->assertEquals(
            'httpResponse',
            (string) $response,
        );
    }

    public function testDecodeUnknownErrorFalse(): void {
        $responseDecode = Response::decode('null');

        $this->assertFalse($responseDecode->success);
        $this->assertEquals(
            ['unknown-error'],
            $responseDecode->errorCodes,
        );

        $this->assertEquals(
            [
                'success' => false,
                'errorCodes' => ['unknown-error'],
                'challengeTs' => null,
                'hostname' => null,
                'action' => null,
                'cdata' => null,
            ],
            $responseDecode->toArray(strict: true),
        );
        $this->assertEquals(
            'null',
            (string) $responseDecode,
        );
    }

    public function testDecodeUnknownErrorNotArray(): void {
        $responseDecode = Response::decode('true');

        $this->assertFalse($responseDecode->success);
        $this->assertEquals(
            ['unknown-error'],
            $responseDecode->errorCodes,
        );
        $this->assertEquals(
            [
                'success' => false,
                'errorCodes' => ['unknown-error'],
                'challengeTs' => null,
                'hostname' => null,
                'action' => null,
                'cdata' => null,
            ],
            $responseDecode->toArray(strict: true),
        );
        $this->assertEquals(
            'true',
            (string) $responseDecode,
        );
    }

    public function testDecodeUnknownError(): void {
        $responseDecode = Response::decode('{"test": true}');

        $this->assertFalse($responseDecode->success);
        $this->assertEquals(
            ['unknown-error'],
            $responseDecode->errorCodes,
        );
        $this->assertEquals(
            [
                'success' => false,
                'errorCodes' => ['unknown-error'],
                'challengeTs' => null,
                'hostname' => null,
                'action' => null,
                'cdata' => null,
            ],
            $responseDecode->toArray(strict: true),
        );
        $this->assertEquals(
            '{"test": true}',
            (string) $responseDecode,
        );
    }

    public function testDecodeInvalidJson(): void {
        $responseDecode = Response::decode('invalid');

        $this->assertFalse($responseDecode->success);
        $this->assertEquals(
            ['invalid-json'],
            $responseDecode->errorCodes,
        );
        $this->assertEquals(
            [
                'success' => false,
                'errorCodes' => ['invalid-json'],
                'challengeTs' => null,
                'hostname' => null,
                'action' => null,
                'cdata' => null,
            ],
            $responseDecode->toArray(strict: true),
        );
        $this->assertEquals(
            'invalid',
            (string) $responseDecode,
        );
    }
}

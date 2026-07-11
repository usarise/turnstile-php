<?php

declare(strict_types=1);

namespace TurnstileTests\Client;

use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Turnstile\Client\Response;

final class ResponseTest extends TestCase {
    public function testDecodeSimple(): void {
        $createResponse = $this->createResponse(
            '{"success": true}',
        );

        $responseDecode = Response::decode(
            $createResponse,
        );

        $this->assertTrue($responseDecode->success);
        $this->assertEquals(
            [],
            $responseDecode->errorCodes,
        );

        $this->assertNull($responseDecode->hostname);
        $this->assertNull($responseDecode->challengeTs);
        $this->assertNull($responseDecode->action);
        $this->assertNull($responseDecode->cdata);
        $this->assertNull($responseDecode->metadata);
        $this->assertNull($responseDecode->messages);

        $this->assertInstanceOf(
            ResponseInterface::class,
            $responseDecode->httpResponse,
        );
        $this->assertEquals(
            $createResponse,
            $responseDecode->httpResponse,
        );
        $this->assertEquals(
            200,
            $responseDecode->httpResponse->getStatusCode(),
        );

        $this->assertEquals(
            ['success' => true],
            $responseDecode->toArray(),
        );
        $this->assertEquals(
            [
                'success' => true,
                'errorCodes' => [],
                'challengeTs' => null,
                'hostname' => null,
                'action' => null,
                'cdata' => null,
                'metadata' => null,
                'messages' => null,
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
        $httpResponse = '{"success": false, "error-codes": ["test-error"], "messages":["Test error."], "hostname": "localhost.test", "challenge_ts": "' . $challengeTs . '", "action": "login", "cdata": "sessionid-123456789", "metadata": {"ephemeral_id": "x:9f78e0ed210960d7693b167e"}}';

        $createResponse = $this->createResponse(
            $httpResponse,
            400,
        );

        $responseDecode = Response::decode(
            $createResponse,
        );

        $this->assertFalse($responseDecode->success);
        $this->assertEquals(
            ['test-error'],
            $responseDecode->errorCodes,
        );
        $this->assertEquals(
            ['Test error.'],
            $responseDecode->messages,
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
            ['ephemeral_id' => 'x:9f78e0ed210960d7693b167e'],
            $responseDecode->metadata,
        );

        $this->assertInstanceOf(
            ResponseInterface::class,
            $responseDecode->httpResponse,
        );
        $this->assertEquals(
            $createResponse,
            $responseDecode->httpResponse,
        );
        $this->assertEquals(
            400,
            $responseDecode->httpResponse->getStatusCode(),
        );

        $this->assertEquals(
            [
                'success' => false,
                'messages' => ['Test error.'],
                'hostname' => 'localhost.test',
                'action' => 'login',
                'cdata' => 'sessionid-123456789',
                'metadata' => ['ephemeral_id' => 'x:9f78e0ed210960d7693b167e'],
                'error-codes' => ['test-error'],
                'challenge_ts' => $challengeTs,
            ],
            $responseDecode->toArray(),
        );
        $this->assertEquals(
            [
                'success' => false,
                'errorCodes' => ['test-error'],
                'messages' => ['Test error.'],
                'challengeTs' => $challengeTs,
                'hostname' => 'localhost.test',
                'action' => 'login',
                'cdata' => 'sessionid-123456789',
                'metadata' => ['ephemeral_id' => 'x:9f78e0ed210960d7693b167e'],
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
                'metadata' => null,
                'messages' => null,
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
                'metadata' => null,
                'messages' => null,
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
                'metadata' => null,
                'messages' => null,
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
        $createResponse = $this->createResponse(
            'null',
        );

        $responseDecode = Response::decode(
            $createResponse,
        );

        $this->assertFalse($responseDecode->success);
        $this->assertEquals(
            ['unknown-error'],
            $responseDecode->errorCodes,
        );

        $this->assertInstanceOf(
            ResponseInterface::class,
            $responseDecode->httpResponse,
        );
        $this->assertEquals(
            $createResponse,
            $responseDecode->httpResponse,
        );
        $this->assertEquals(
            200,
            $responseDecode->httpResponse->getStatusCode(),
        );

        $this->assertEquals(
            [],
            $responseDecode->toArray(),
        );
        $this->assertEquals(
            [
                'success' => false,
                'errorCodes' => ['unknown-error'],
                'challengeTs' => null,
                'hostname' => null,
                'action' => null,
                'cdata' => null,
                'metadata' => null,
                'messages' => null,
            ],
            $responseDecode->toArray(strict: true),
        );
        $this->assertEquals(
            'null',
            (string) $responseDecode,
        );
    }

    public function testDecodeUnknownErrorNotArray(): void {
        $createResponse = $this->createResponse(
            'true',
        );

        $responseDecode = Response::decode(
            $createResponse,
        );

        $this->assertFalse($responseDecode->success);
        $this->assertEquals(
            ['unknown-error'],
            $responseDecode->errorCodes,
        );

        $this->assertInstanceOf(
            ResponseInterface::class,
            $responseDecode->httpResponse,
        );
        $this->assertEquals(
            $createResponse,
            $responseDecode->httpResponse,
        );
        $this->assertEquals(
            200,
            $responseDecode->httpResponse->getStatusCode(),
        );

        $this->assertEquals(
            [],
            $responseDecode->toArray(),
        );
        $this->assertEquals(
            [
                'success' => false,
                'errorCodes' => ['unknown-error'],
                'challengeTs' => null,
                'hostname' => null,
                'action' => null,
                'cdata' => null,
                'metadata' => null,
                'messages' => null,
            ],
            $responseDecode->toArray(strict: true),
        );
        $this->assertEquals(
            'true',
            (string) $responseDecode,
        );
    }

    public function testDecodeUnknownError(): void {
        $createResponse = $this->createResponse(
            '{"test": true}',
        );

        $responseDecode = Response::decode(
            $createResponse,
        );

        $this->assertFalse($responseDecode->success);
        $this->assertEquals(
            ['unknown-error'],
            $responseDecode->errorCodes,
        );

        $this->assertInstanceOf(
            ResponseInterface::class,
            $responseDecode->httpResponse,
        );
        $this->assertEquals(
            $createResponse,
            $responseDecode->httpResponse,
        );
        $this->assertEquals(
            200,
            $responseDecode->httpResponse->getStatusCode(),
        );

        $this->assertEquals(
            ['test' => true],
            $responseDecode->toArray(),
        );
        $this->assertEquals(
            [
                'success' => false,
                'errorCodes' => ['unknown-error'],
                'challengeTs' => null,
                'hostname' => null,
                'action' => null,
                'cdata' => null,
                'metadata' => null,
                'messages' => null,
            ],
            $responseDecode->toArray(strict: true),
        );
        $this->assertEquals(
            '{"test": true}',
            (string) $responseDecode,
        );
    }

    public function testDecodeInvalidJson(): void {
        $createResponse = $this->createResponse(
            'invalid',
            500,
        );

        $responseDecode = Response::decode(
            $createResponse,
        );

        $this->assertFalse($responseDecode->success);
        $this->assertEquals(
            [
                'invalid-json',
                'unknown-error',
            ],
            $responseDecode->errorCodes,
        );

        $this->assertInstanceOf(
            ResponseInterface::class,
            $responseDecode->httpResponse,
        );
        $this->assertEquals(
            $createResponse,
            $responseDecode->httpResponse,
        );
        $this->assertEquals(
            500,
            $responseDecode->httpResponse->getStatusCode(),
        );

        $this->assertEquals(
            [],
            $responseDecode->toArray(),
        );
        $this->assertEquals(
            [
                'success' => false,
                'errorCodes' => [
                    'invalid-json',
                    'unknown-error',
                ],
                'challengeTs' => null,
                'hostname' => null,
                'action' => null,
                'cdata' => null,
                'metadata' => null,
                'messages' => null,
            ],
            $responseDecode->toArray(strict: true),
        );
        $this->assertEquals(
            'invalid',
            (string) $responseDecode,
        );
    }

    private function createResponse(string $httpBody, int $statusCode = 200): ResponseInterface {
        $psr17Factory = new Psr17Factory();

        return $psr17Factory->createResponse($statusCode)->withBody(
            $psr17Factory->createStream(
                $httpBody,
            ),
        );
    }
}

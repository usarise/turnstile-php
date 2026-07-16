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
        $this->assertSame(
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
        $this->assertSame(
            $createResponse,
            $responseDecode->httpResponse,
        );
        $this->assertSame(
            200,
            $responseDecode->httpResponse->getStatusCode(),
        );

        $this->assertSame(
            ['success' => true],
            $responseDecode->toArray(),
        );
        $this->assertSame(
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
        $this->assertSame(
            '{"success": true}',
            (string) $responseDecode,
        );
    }

    public function testDecodeFull(): void {
        $challengeTs = gmdate('Y-m-d\TH:i:s.vp');
        $httpResponse = '{"success": false, "error-codes": ["test-error"], "messages":["Test error."], "challenge_ts": "' . $challengeTs . '", "hostname": "localhost.test", "action": "login", "cdata": "sessionid-123456789", "metadata": {"ephemeral_id": "x:9f78e0ed210960d7693b167e"}}';

        $createResponse = $this->createResponse(
            $httpResponse,
            400,
        );

        $responseDecode = Response::decode(
            $createResponse,
        );

        $this->assertFalse($responseDecode->success);
        $this->assertSame(
            ['test-error'],
            $responseDecode->errorCodes,
        );
        $this->assertSame(
            ['Test error.'],
            $responseDecode->messages,
        );

        $this->assertSame(
            'localhost.test',
            $responseDecode->hostname,
        );
        $this->assertSame(
            $challengeTs,
            $responseDecode->challengeTs,
        );
        $this->assertSame(
            'login',
            $responseDecode->action,
        );
        $this->assertSame(
            'sessionid-123456789',
            $responseDecode->cdata,
        );
        $this->assertSame(
            ['ephemeral_id' => 'x:9f78e0ed210960d7693b167e'],
            $responseDecode->metadata,
        );

        $this->assertInstanceOf(
            ResponseInterface::class,
            $responseDecode->httpResponse,
        );
        $this->assertSame(
            $createResponse,
            $responseDecode->httpResponse,
        );
        $this->assertSame(
            400,
            $responseDecode->httpResponse->getStatusCode(),
        );

        $this->assertSame(
            [
                'success' => false,
                'error-codes' => ['test-error'],
                'messages' => ['Test error.'],
                'challenge_ts' => $challengeTs,
                'hostname' => 'localhost.test',
                'action' => 'login',
                'cdata' => 'sessionid-123456789',
                'metadata' => ['ephemeral_id' => 'x:9f78e0ed210960d7693b167e'],
            ],
            $responseDecode->toArray(),
        );
        $this->assertSame(
            [
                'success' => false,
                'errorCodes' => ['test-error'],
                'challengeTs' => $challengeTs,
                'hostname' => 'localhost.test',
                'action' => 'login',
                'cdata' => 'sessionid-123456789',
                'metadata' => ['ephemeral_id' => 'x:9f78e0ed210960d7693b167e'],
                'messages' => ['Test error.'],
            ],
            $responseDecode->toArray(strict: true),
        );
        $this->assertSame(
            $httpResponse,
            (string) $responseDecode,
        );
    }

    public function testToArraySuccessTrue(): void {
        $response = new Response(true, []);

        $this->assertSame(
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

        $this->assertSame(
            [],
            $response->toArray(),
        );
    }

    public function testToArraySuccessFalse(): void {
        $response = new Response(
            success: false,
            errorCodes: ['test-error'],
        );

        $this->assertSame(
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

        $this->assertSame(
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

        $this->assertSame(
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

        $this->assertSame(
            ['test' => 'jsonDecode'],
            $response->toArray(),
        );

        $this->assertSame(
            ['test' => 'jsonDecode'],
            $response->toArray(strict: false),
        );
    }

    public function testToString(): void {
        $response = new Response(true, []);

        $this->assertSame(
            '',
            (string) $response,
        );

        $response = new Response(
            success: false,
            errorCodes: [],
            httpBody: 'httpResponse',
        );

        $this->assertSame(
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
        $this->assertSame(
            ['unknown-error'],
            $responseDecode->errorCodes,
        );

        $this->assertInstanceOf(
            ResponseInterface::class,
            $responseDecode->httpResponse,
        );
        $this->assertSame(
            $createResponse,
            $responseDecode->httpResponse,
        );
        $this->assertSame(
            200,
            $responseDecode->httpResponse->getStatusCode(),
        );

        $this->assertSame(
            [],
            $responseDecode->toArray(),
        );
        $this->assertSame(
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
        $this->assertSame(
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
        $this->assertSame(
            ['unknown-error'],
            $responseDecode->errorCodes,
        );

        $this->assertInstanceOf(
            ResponseInterface::class,
            $responseDecode->httpResponse,
        );
        $this->assertSame(
            $createResponse,
            $responseDecode->httpResponse,
        );
        $this->assertSame(
            200,
            $responseDecode->httpResponse->getStatusCode(),
        );

        $this->assertSame(
            [],
            $responseDecode->toArray(),
        );
        $this->assertSame(
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
        $this->assertSame(
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
        $this->assertSame(
            ['unknown-error'],
            $responseDecode->errorCodes,
        );

        $this->assertInstanceOf(
            ResponseInterface::class,
            $responseDecode->httpResponse,
        );
        $this->assertSame(
            $createResponse,
            $responseDecode->httpResponse,
        );
        $this->assertSame(
            200,
            $responseDecode->httpResponse->getStatusCode(),
        );

        $this->assertSame(
            ['test' => true],
            $responseDecode->toArray(),
        );
        $this->assertSame(
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
        $this->assertSame(
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
        $this->assertSame(
            ['invalid-json'],
            $responseDecode->errorCodes,
        );
        $this->assertSame(
            ['Syntax error'],
            $responseDecode->messages,
        );

        $this->assertInstanceOf(
            ResponseInterface::class,
            $responseDecode->httpResponse,
        );
        $this->assertSame(
            $createResponse,
            $responseDecode->httpResponse,
        );
        $this->assertSame(
            500,
            $responseDecode->httpResponse->getStatusCode(),
        );

        $this->assertSame(
            [],
            $responseDecode->toArray(),
        );
        $this->assertSame(
            [
                'success' => false,
                'errorCodes' => [
                    'invalid-json',
                ],
                'challengeTs' => null,
                'hostname' => null,
                'action' => null,
                'cdata' => null,
                'metadata' => null,
                'messages' => ['Syntax error'],
            ],
            $responseDecode->toArray(strict: true),
        );
        $this->assertSame(
            'invalid',
            (string) $responseDecode,
        );
    }

    public function testDecodeThrowable(): void {
        $createResponse = $this->createResponse(
            '{"success": true, "challenge_ts": 0}',
        );

        $responseDecode = Response::decode(
            $createResponse,
        );

        $this->assertFalse($responseDecode->success);
        $this->assertSame(
            ['unknown-error'],
            $responseDecode->errorCodes,
        );

        $this->assertInstanceOf(
            ResponseInterface::class,
            $responseDecode->httpResponse,
        );
        $this->assertSame(
            $createResponse,
            $responseDecode->httpResponse,
        );
        $this->assertSame(
            200,
            $responseDecode->httpResponse->getStatusCode(),
        );

        $this->assertSame(
            [],
            $responseDecode->toArray(),
        );
        $this->assertSame(
            '{"success": true, "challenge_ts": 0}',
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

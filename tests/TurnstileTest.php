<?php

declare(strict_types=1);

namespace TurnstileTests;

use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\HttpClient\Psr18Client;
use Turnstile\Client\Client;
use Turnstile\Exception\InvalidArgumentException;
use Turnstile\{Turnstile, TurnstileInterface};
use TurnstileTests\Client\Psr18\HttpFactoryInterface;

final class TurnstileTest extends TestCase {
    /**
     * Interface constant values.
     *
     * @see https://developers.cloudflare.com/turnstile/get-started/server-side-validation/
     */
    public function testInterfaceConstantValue(): void {
        $this->assertSame(
            'https://challenges.cloudflare.com/turnstile/v0/siteverify',
            TurnstileInterface::SITE_VERIFY_URL,
        );

        $this->assertSame(
            'cf-turnstile-response',
            TurnstileInterface::RESPONSE_KEY,
        );

        $this->assertSame(
            2048,
            TurnstileInterface::MAX_TOKEN_LENGTH,
        );
    }

    public function testBadSecretKey(): void {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The secret key cannot be empty.');

        new Turnstile(
            new Client(
                $this->createStub(
                    HttpFactoryInterface::class,
                ),
            ),
            '',
        );
    }

    public function testBaseConstruct(): void {
        $this->assertInstanceOf(
            TurnstileInterface::class,
            new Turnstile(
                new Client(
                    $this->createStub(
                        HttpFactoryInterface::class,
                    ),
                ),
                'secret',
            ),
        );

        $this->assertInstanceOf(
            TurnstileInterface::class,
            new Turnstile(
                $this->createStub(
                    HttpFactoryInterface::class,
                ),
                'secret',
            ),
        );
    }

    public function testVerify(): void {
        $response = (new Turnstile(
            client: $this->getMockHttpClientReturn(
                '{"success": true}',
            ),
            secretKey: 'secret',
        ))
        ->verify('token', '127.0.0.1')
        ;

        $this->assertTrue($response->success);
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
            [
                'success' => true,
            ],
            $response->toArray(),
        );
        $this->assertSame(
            '{"success": true}',
            (string) $response,
        );
    }

    public function testVerifyIdempotency(): void {
        $turnstile = new Turnstile(
            client: $this->getMockHttpClientReturn(
                '{"success": true}',
            ),
            secretKey: 'secret',
            idempotencyKey: '123e4567-e89b-12d3-a456-426655440000',
        );

        $response = $turnstile->verify(
            'token',
            '127.0.0.1',
        );

        $this->assertTrue($response->success);
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
            [
                'success' => true,
            ],
            $response->toArray(),
        );
        $this->assertSame(
            '{"success": true}',
            (string) $response,
        );

        $response = $turnstile->verify(
            'token',
            '127.0.0.1',
        );

        $this->assertTrue($response->success);
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
            [
                'success' => true,
            ],
            $response->toArray(),
        );
        $this->assertSame(
            '{"success": true}',
            (string) $response,
        );
    }

    public function testVerifyMetadata(): void {
        $response = (new Turnstile(
            client: $this->getMockHttpClientReturn(
                '{"success": true, "metadata": {"ephemeral_id": "x:9f78e0ed210960d7693b167e"}}',
            ),
            secretKey: 'secret',
        ))
        ->verify('token', '127.0.0.1')
        ;

        $this->assertTrue($response->success);
        $this->assertSame(
            ['ephemeral_id' => 'x:9f78e0ed210960d7693b167e'],
            $response->metadata,
        );
        $this->assertSame(
            [
                'success' => true,
                'errorCodes' => [],
                'challengeTs' => null,
                'hostname' => null,
                'action' => null,
                'cdata' => null,
                'metadata' => ['ephemeral_id' => 'x:9f78e0ed210960d7693b167e'],
                'messages' => null,
            ],
            $response->toArray(strict: true),
        );
        $this->assertSame(
            [
                'success' => true,
                'metadata' => ['ephemeral_id' => 'x:9f78e0ed210960d7693b167e'],
            ],
            $response->toArray(),
        );
        $this->assertSame(
            '{"success": true, "metadata": {"ephemeral_id": "x:9f78e0ed210960d7693b167e"}}',
            (string) $response,
        );
    }

    public function testHttpResponse(): void {
        $response = (new Turnstile(
            client: $this->getMockHttpClientReturn(
                '{"success": true}',
            ),
            secretKey: 'secret',
        ))
        ->verify('token', '127.0.0.1')
        ;

        $this->assertInstanceOf(
            ResponseInterface::class,
            $response->httpResponse,
        );
        $this->assertSame(
            (string) $response,
            (string) $response->httpResponse->getBody(),
        );
        $this->assertSame(
            200,
            $response->httpResponse->getStatusCode(),
        );
    }

    public function testError(): void {
        $response = (new Turnstile(
            client: $this->getMockHttpClientReturn(
                '{"error-codes":["test-error"], "success": false, "messages": []}',
            ),
            secretKey: 'secret',
        ))
        ->verify('token')
        ;

        $this->assertFalse($response->success);
        $this->assertSame(
            ['test-error'],
            $response->errorCodes,
        );
        $this->assertSame(
            [],
            $response->messages,
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
                'messages' => [],
            ],
            $response->toArray(strict: true),
        );
        $this->assertSame(
            [
                'error-codes' => ['test-error'],
                'success' => false,
                'messages' => [],
            ],
            $response->toArray(),
        );
        $this->assertSame(
            '{"error-codes":["test-error"], "success": false, "messages": []}',
            (string) $response,
        );
    }

    public function testErrorConnection(): void {
        $response = (new Turnstile(
            client: new Client(
                httpClient: new Psr18Client(),
                siteVerifyUrl: '',
            ),
            secretKey: 'secret',
        ))
        ->verify('token')
        ;

        $this->assertFalse($response->success);
        $this->assertSame(
            ['connection-failed'],
            $response->errorCodes,
        );
        $this->assertSame(
            ['Invalid URL: scheme is missing in "". Did you forget to add "http(s)://"?'],
            $response->messages,
        );
        $this->assertSame(
            [
                'success' => false,
                'errorCodes' => ['connection-failed'],
                'challengeTs' => null,
                'hostname' => null,
                'action' => null,
                'cdata' => null,
                'metadata' => null,
                'messages' => ['Invalid URL: scheme is missing in "". Did you forget to add "http(s)://"?'],
            ],
            $response->toArray(strict: true),
        );
        $this->assertSame(
            [],
            $response->toArray(),
        );
        $this->assertSame(
            '',
            (string) $response,
        );
    }

    public function testErrorMessages(): void {
        $response = (new Turnstile(
            client: $this->getMockHttpClientReturn(
                '{"error-codes":["test-error"], "success": false, "messages": ["Test error."]}',
            ),
            secretKey: 'secret',
        ))
        ->verify('token')
        ;

        $this->assertFalse($response->success);
        $this->assertSame(
            ['test-error'],
            $response->errorCodes,
        );
        $this->assertSame(
            ['Test error.'],
            $response->messages,
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
                'messages' => ['Test error.'],
            ],
            $response->toArray(strict: true),
        );
        $this->assertSame(
            [
                'error-codes' => ['test-error'],
                'success' => false,
                'messages' => ['Test error.'],
            ],
            $response->toArray(),
        );
        $this->assertSame(
            '{"error-codes":["test-error"], "success": false, "messages": ["Test error."]}',
            (string) $response,
        );
    }

    public function testChallengeTimeoutValidation(): void {
        $challengeTs = $this->getChallengeTs('now');

        $response = (new Turnstile(
            client: $this->getMockHttpClientReturn(
                '{"success": true, "challenge_ts": "' . $challengeTs . '"}',
            ),
            secretKey: 'secret',
        ))
        ->verify(
            token: 'token',
            challengeTimeout: 15,
        )
        ;

        $this->assertTrue($response->success);
        $this->assertSame(
            $challengeTs,
            $response->challengeTs,
        );
    }

    public function testBadChallengeTimeoutValidation(): void {
        $challengeTs = $this->getChallengeTs('-150 sec');

        $response = (new Turnstile(
            client: $this->getMockHttpClientReturn(
                '{"success": true, "challenge_ts": "' . $challengeTs . '"}',
            ),
            secretKey: 'secret',
        ))
        ->verify(
            token: 'token',
            challengeTimeout: 15,
        )
        ;

        $this->assertFalse($response->success);
        $this->assertSame(
            ['challenge-timeout'],
            $response->errorCodes,
        );
        $this->assertSame(
            $challengeTs,
            $response->challengeTs,
        );
    }

    public function testHostnameValidation(): void {
        $response = (new Turnstile(
            client: $this->getMockHttpClientReturn(
                '{"success": true, "hostname": "localhost.test"}',
            ),
            secretKey: 'secret',
        ))
        ->verify(
            token: 'token',
            expectedHostname: 'localhost.test',
        )
        ;

        $this->assertTrue($response->success);
        $this->assertSame(
            'localhost.test',
            $response->hostname,
        );
    }

    public function testBadHostnameValidation(): void {
        $response = (new Turnstile(
            client: $this->getMockHttpClientReturn(
                '{"success": true, "hostname": "localhost.test"}',
            ),
            secretKey: 'secret',
        ))
        ->verify(
            token: 'token',
            expectedHostname: 'localhost',
        )
        ;

        $this->assertFalse($response->success);
        $this->assertSame(
            ['hostname-mismatch'],
            $response->errorCodes,
        );
        $this->assertSame(
            'localhost.test',
            $response->hostname,
        );
    }

    public function testActionValidation(): void {
        $response = (new Turnstile(
            client: $this->getMockHttpClientReturn(
                '{"success": true, "action": "login"}',
            ),
            secretKey: 'secret',
        ))
        ->verify(
            token: 'token',
            expectedAction: 'login',
        )
        ;

        $this->assertTrue($response->success);
        $this->assertSame(
            'login',
            $response->action,
        );
    }

    public function testBadActionValidation(): void {
        $response = (new Turnstile(
            client: $this->getMockHttpClientReturn(
                '{"success": true, "action": "login"}',
            ),
            secretKey: 'secret',
        ))
        ->verify(
            token: 'token',
            expectedAction: 'sign_in',
        )
        ;

        $this->assertFalse($response->success);
        $this->assertSame(
            ['action-mismatch'],
            $response->errorCodes,
        );
        $this->assertSame(
            'login',
            $response->action,
        );
    }

    public function testCdataValidation(): void {
        $response = (new Turnstile(
            client: $this->getMockHttpClientReturn(
                '{"success": true, "cdata": "sessionid-123456789"}',
            ),
            secretKey: 'secret',
        ))
        ->verify(
            token: 'token',
            expectedCdata: 'sessionid-123456789',
        )
        ;

        $this->assertTrue($response->success);
        $this->assertSame(
            'sessionid-123456789',
            $response->cdata,
        );
    }

    public function testBadCdataValidation(): void {
        $response = (new Turnstile(
            client: $this->getMockHttpClientReturn(
                '{"success": true, "cdata": "sessionid-123456789"}',
            ),
            secretKey: 'secret',
        ))
        ->verify(
            token: 'token',
            expectedCdata: 'sessiondata',
        )
        ;

        $this->assertFalse($response->success);
        $this->assertSame(
            ['cdata-mismatch'],
            $response->errorCodes,
        );
        $this->assertSame(
            'sessionid-123456789',
            $response->cdata,
        );
    }

    public function testBadValidation(): void {
        $challengeTs = $this->getChallengeTs('-150 sec');
        $httpResponse = '{"success": true, "challenge_ts": "' . $challengeTs . '", "hostname": "localhost.test", "action": "login", "cdata": "sessionid-123456789"}';

        $response = (new Turnstile(
            client: $this->getMockHttpClientReturn(
                $httpResponse,
            ),
            secretKey: 'secret',
        ))
        ->verify(
            token: 'token',
            challengeTimeout: 15,
            expectedHostname: 'localhost',
            expectedAction: 'sign_in',
            expectedCdata: 'sessiondata',
        )
        ;

        $this->assertFalse($response->success);
        $this->assertSame(
            [
                'challenge-timeout',
                'hostname-mismatch',
                'action-mismatch',
                'cdata-mismatch',
            ],
            $response->errorCodes,
        );
        $this->assertSame(
            $challengeTs,
            $response->challengeTs,
        );
        $this->assertSame(
            'localhost.test',
            $response->hostname,
        );
        $this->assertSame(
            'login',
            $response->action,
        );
        $this->assertSame(
            'sessionid-123456789',
            $response->cdata,
        );

        $this->assertInstanceOf(
            ResponseInterface::class,
            $response->httpResponse,
        );
        $this->assertSame(
            (string) $response,
            (string) $response->httpResponse->getBody(),
        );
        $this->assertSame(
            200,
            $response->httpResponse->getStatusCode(),
        );

        $this->assertSame(
            [
                'success' => false,
                'errorCodes' => [
                    'challenge-timeout',
                    'hostname-mismatch',
                    'action-mismatch',
                    'cdata-mismatch',
                ],
                'challengeTs' => $challengeTs,
                'hostname' => 'localhost.test',
                'action' => 'login',
                'cdata' => 'sessionid-123456789',
                'metadata' => null,
                'messages' => null,
            ],
            $response->toArray(strict: true),
        );
        $this->assertSame(
            [
                'success' => true,
                'challenge_ts' => $challengeTs,
                'hostname' => 'localhost.test',
                'action' => 'login',
                'cdata' => 'sessionid-123456789',
            ],
            $response->toArray(),
        );
        $this->assertSame(
            $httpResponse,
            (string) $response,
        );
    }

    public function testBadClientValidationAndErrors(): void {
        $challengeTs = $this->getChallengeTs('now');
        $httpResponse = '{"success": false, "error-codes": ["test-error"], "challenge_ts": "' . $challengeTs . '", "hostname": "localhost.test", "action": "login", "cdata": "sessionid-123456789"}';

        $response = (new Turnstile(
            client: $this->getMockHttpClientReturn(
                $httpResponse,
                400,
            ),
            secretKey: 'secret',
        ))
        ->verify(
            token: 'token',
            expectedHostname: 'localhost',
        )
        ;

        $this->assertFalse($response->success);
        $this->assertSame(
            [
                'test-error',
                'hostname-mismatch',
            ],
            $response->errorCodes,
        );
        $this->assertSame(
            'localhost.test',
            $response->hostname,
        );
        $this->assertSame(
            $challengeTs,
            $response->challengeTs,
        );
        $this->assertSame(
            'login',
            $response->action,
        );
        $this->assertSame(
            'sessionid-123456789',
            $response->cdata,
        );

        $this->assertInstanceOf(
            ResponseInterface::class,
            $response->httpResponse,
        );
        $this->assertSame(
            (string) $response,
            (string) $response->httpResponse->getBody(),
        );
        $this->assertSame(
            400,
            $response->httpResponse->getStatusCode(),
        );

        $this->assertSame(
            [
                'success' => false,
                'errorCodes' => [
                    'test-error',
                    'hostname-mismatch',
                ],
                'challengeTs' => $challengeTs,
                'hostname' => 'localhost.test',
                'action' => 'login',
                'cdata' => 'sessionid-123456789',
                'metadata' => null,
                'messages' => null,
            ],
            $response->toArray(strict: true),
        );
        $this->assertSame(
            [
                'success' => false,
                'error-codes' => ['test-error'],
                'challenge_ts' => $challengeTs,
                'hostname' => 'localhost.test',
                'action' => 'login',
                'cdata' => 'sessionid-123456789',
            ],
            $response->toArray(),
        );
        $this->assertSame(
            $httpResponse,
            (string) $response,
        );
    }

    public function testBadEmptyToken(): void {
        $response = (new Turnstile(
            client: $this->getMockHttpClientReturn(
                '{"success": true}',
            ),
            secretKey: 'secret',
        ))
        ->verify('')
        ;

        $this->assertFalse($response->success);
        $this->assertSame(
            ['missing-input-response'],
            $response->errorCodes,
        );
        $this->assertSame(
            '',
            (string) $response,
        );
    }

    public function testBadLengthToken(): void {
        $response = (new Turnstile(
            client: $this->getMockHttpClientReturn(
                '{"success": true}',
            ),
            secretKey: 'secret',
        ))
        ->verify('xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx')
        ;

        $this->assertFalse($response->success);
        $this->assertSame(
            ['invalid-input-response'],
            $response->errorCodes,
        );
        $this->assertSame(
            '',
            (string) $response,
        );
    }

    private function getChallengeTs(string $datetime): string {
        $challengeTs = new \DateTimeImmutable(
            $datetime,
            new \DateTimeZone('UTC'),
        );

        return $challengeTs->format('Y-m-d\TH:i:s.vp');
    }

    private function getMockHttpClientReturn(string $response, int $statusCode = 200): Client {
        $stub = $this->createStub(ClientInterface::class);
        $psr17Factory = new Psr17Factory();

        $createResponse = $psr17Factory->createResponse($statusCode)
            ->withBody(
                $psr17Factory->createStream(
                    $response,
                ),
            )
        ;

        $stub
            ->method('sendRequest')
            ->willReturn($createResponse)
        ;

        return new Client(
            $stub,
            $psr17Factory,
            $psr17Factory,
        );
    }
}

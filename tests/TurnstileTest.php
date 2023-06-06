<?php

declare(strict_types=1);

namespace TurnstileTests;

use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;
use Turnstile\Client\{Client, Response};
use Turnstile\{Turnstile, TurnstileException, TurnstileInterface};
use TurnstileTests\Client\Psr18\HttpFactoryInterface;

final class TurnstileTest extends TestCase {
    public function testSiteVerifyUrlDefault(): void {
        $this->assertEquals(
            'https://challenges.cloudflare.com/turnstile/v0/siteverify',
            TurnstileInterface::SITE_VERIFY_URL,
        );
    }

    public function testBadSecretKey(): void {
        $this->expectException(TurnstileException::class);

        new Turnstile(
            new Client(
                $this->createMock(
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
                    $this->createMock(
                        HttpFactoryInterface::class,
                    ),
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

        $this->assertInstanceOf(
            Response::class,
            $response,
        );

        $this->assertTrue($response->success);
        $this->assertEquals(
            [
                'success' => true,
                'errorCodes' => [],
                'challengeTs' => null,
                'hostname' => null,
                'action' => null,
                'cdata' => null,
            ],
            $response->toArray(),
        );
        $this->assertEquals(
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
        );

        $args = [
            'token',
            '127.0.0.1',
            '123e4567-e89b-12d3-a456-426655440000',
        ];

        $response = $turnstile->verify(
            ...$args,
        );

        $this->assertInstanceOf(
            Response::class,
            $response,
        );

        $this->assertTrue($response->success);
        $this->assertEquals(
            [
                'success' => true,
                'errorCodes' => [],
                'challengeTs' => null,
                'hostname' => null,
                'action' => null,
                'cdata' => null,
            ],
            $response->toArray(),
        );
        $this->assertEquals(
            '{"success": true}',
            (string) $response,
        );

        $response = $turnstile->verify(
            ...$args,
        );

        $this->assertInstanceOf(
            Response::class,
            $response,
        );

        $this->assertTrue($response->success);
        $this->assertEquals(
            [
                'success' => true,
                'errorCodes' => [],
                'challengeTs' => null,
                'hostname' => null,
                'action' => null,
                'cdata' => null,
            ],
            $response->toArray(),
        );
        $this->assertEquals(
            '{"success": true}',
            (string) $response,
        );
    }

    public function testError(): void {
        $response = (new Turnstile(
            client: $this->getMockHttpClientReturn(
                '{"success": false, "error-codes": ["test-error"]}',
            ),
            secretKey: 'secret',
        ))
        ->verify('token')
        ;

        $this->assertInstanceOf(
            Response::class,
            $response,
        );

        $this->assertFalse($response->success);
        $this->assertEquals(
            ['test-error'],
            $response->errorCodes,
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
            $response->toArray(),
        );
        $this->assertEquals(
            '{"success": false, "error-codes": ["test-error"]}',
            (string) $response,
        );
    }

    public function testTimeoutSecondsValidation(): void {
        $challengeTs = $this->getChallengeTs('now');

        $response = (new Turnstile(
            client: $this->getMockHttpClientReturn(
                '{"success": true, "challenge_ts": "' . $challengeTs . '"}',
            ),
            secretKey: 'secret',
            timeoutSeconds: 15,
        ))
        ->verify('token')
        ;

        $this->assertTrue($response->success);
        $this->assertEquals(
            $challengeTs,
            $response->challengeTs,
        );
    }

    public function testBadTimeoutSecondsValidation(): void {
        $challengeTs = $this->getChallengeTs('-150 sec');

        $response = (new Turnstile(
            client: $this->getMockHttpClientReturn(
                '{"success": true, "challenge_ts": "' . $challengeTs . '"}',
            ),
            secretKey: 'secret',
            timeoutSeconds: 15,
        ))
        ->verify('token')
        ;

        $this->assertFalse($response->success);
        $this->assertEquals(
            ['challenge-timeout'],
            $response->errorCodes,
        );
        $this->assertEquals(
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
            hostname: 'localhost.test',
        ))
        ->verify('token')
        ;

        $this->assertTrue($response->success);
        $this->assertEquals(
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
            hostname: 'localhost',
        ))
        ->verify('token')
        ;

        $this->assertFalse($response->success);
        $this->assertEquals(
            ['hostname-mismatch'],
            $response->errorCodes,
        );
        $this->assertEquals(
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
            action: 'login',
        ))
        ->verify('token')
        ;

        $this->assertTrue($response->success);
        $this->assertEquals(
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
            action: 'sign_in',
        ))
        ->verify('token')
        ;

        $this->assertFalse($response->success);
        $this->assertEquals(
            ['action-mismatch'],
            $response->errorCodes,
        );
        $this->assertEquals(
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
            cData: 'sessionid-123456789',
        ))
        ->verify('token')
        ;

        $this->assertTrue($response->success);
        $this->assertEquals(
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
            cData: 'sessiondata',
        ))
        ->verify('token')
        ;

        $this->assertFalse($response->success);
        $this->assertEquals(
            ['cdata-mismatch'],
            $response->errorCodes,
        );
        $this->assertEquals(
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
            timeoutSeconds: 15,
            hostname: 'localhost',
            action: 'sign_in',
            cData: 'sessiondata',
        ))
        ->verify('token')
        ;

        $this->assertFalse($response->success);
        $this->assertEquals(
            [
                'challenge-timeout',
                'hostname-mismatch',
                'action-mismatch',
                'cdata-mismatch',
            ],
            $response->errorCodes,
        );
        $this->assertEquals(
            $challengeTs,
            $response->challengeTs,
        );
        $this->assertEquals(
            'localhost.test',
            $response->hostname,
        );
        $this->assertEquals(
            'login',
            $response->action,
        );
        $this->assertEquals(
            'sessionid-123456789',
            $response->cdata,
        );

        $this->assertEquals(
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
            ],
            $response->toArray(),
        );
        $this->assertEquals(
            $httpResponse,
            (string) $response,
        );
    }

    public function testBadClientValidationAndErrors(): void {
        $challengeTs = $this->getChallengeTs('now');
        $httpResponse = '{"success": false, "error-codes": ["test-error"], "hostname": "localhost.test", "challenge_ts": "' . $challengeTs . '", "action": "login", "cdata": "sessionid-123456789"}';

        $response = (new Turnstile(
            client: $this->getMockHttpClientReturn(
                $httpResponse,
            ),
            secretKey: 'secret',
            hostname: 'localhost',
        ))
        ->verify('token')
        ;

        $this->assertFalse($response->success);
        $this->assertEquals(
            [
                'test-error',
                'hostname-mismatch',
            ],
            $response->errorCodes,
        );
        $this->assertEquals(
            'localhost.test',
            $response->hostname,
        );
        $this->assertEquals(
            $challengeTs,
            $response->challengeTs,
        );
        $this->assertEquals(
            'login',
            $response->action,
        );
        $this->assertEquals(
            'sessionid-123456789',
            $response->cdata,
        );

        $this->assertEquals(
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
            ],
            $response->toArray(),
        );
        $this->assertEquals(
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
        $this->assertEquals(
            ['missing-input-response'],
            $response->errorCodes,
        );
        $this->assertEquals(
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
        $this->assertEquals(
            ['invalid-input-response'],
            $response->errorCodes,
        );
        $this->assertEquals(
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

    private function getMockHttpClientReturn(string $response): Client {
        $mock = $this->createMock(ClientInterface::class);
        $psr17Factory = new Psr17Factory();

        $createResponse = $psr17Factory->createResponse(200)
            ->withBody(
                $psr17Factory->createStream(
                    $response,
                ),
            )
        ;

        $mock->expects($this->any())
            ->method('sendRequest')
            ->willReturn($createResponse)
        ;

        return new Client(
            $mock,
            $psr17Factory,
            $psr17Factory,
        );
    }
}

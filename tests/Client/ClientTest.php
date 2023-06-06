<?php

declare(strict_types=1);

namespace TurnstileTests\Client;

use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\{RequestFactoryInterface, StreamFactoryInterface};
use Turnstile\Client\{Client, RequestParameters};
use Turnstile\TurnstileInterface;
use TurnstileTests\Client\Psr18\HttpFactoryInterface;

final class ClientTest extends TestCase {
    /**
     * @var string
     */
    private const SITE_VERIFY_URL = 'https://localhost.test/turnstile/siteverify';

    public function testConstruct(): void {
        $httpClient = $this->createMock(ClientInterface::class);

        $requestFactory = $this->createMock(RequestFactoryInterface::class);
        $streamFactory = $this->createMock(StreamFactoryInterface::class);
        $httpFactory = $this->createMock(HttpFactoryInterface::class);

        $client = new Client(
            $httpClient,
            $requestFactory,
            $streamFactory,
            self::SITE_VERIFY_URL,
        );

        $this->assertEquals(
            $httpClient,
            $client->client,
        );

        $this->assertEquals(
            $requestFactory,
            $client->requestFactory,
        );

        $this->assertEquals(
            $streamFactory,
            $client->streamFactory,
        );

        $this->assertEquals(
            self::SITE_VERIFY_URL,
            $client->siteVerifyUrl,
        );

        $psr17Factory = new Psr17Factory();
        $client = new Client(
            $httpClient,
            $psr17Factory,
        );

        $this->assertEquals(
            $httpClient,
            $client->client,
        );

        $this->assertEquals(
            $psr17Factory,
            $client->requestFactory,
        );

        $this->assertEquals(
            $psr17Factory,
            $client->streamFactory,
        );

        $this->assertEquals(
            TurnstileInterface::SITE_VERIFY_URL,
            $client->siteVerifyUrl,
        );

        $client = new Client(
            $httpFactory,
        );

        $this->assertEquals(
            $httpFactory,
            $client->client,
        );

        $this->assertEquals(
            $httpFactory,
            $client->requestFactory,
        );

        $this->assertEquals(
            $httpFactory,
            $client->streamFactory,
        );

        $this->assertEquals(
            TurnstileInterface::SITE_VERIFY_URL,
            $client->siteVerifyUrl,
        );
    }

    public function testCreateRequest(): void {
        $requestParams = new RequestParameters(
            'secret',
            'response',
            'remoteip',
        );

        $createRequest = $this->getMockHttpClient()->createRequest($requestParams);

        $this->assertEquals(
            'POST',
            $createRequest->getMethod(),
        );

        $this->assertEquals(
            TurnstileInterface::SITE_VERIFY_URL,
            (string) $createRequest->getUri(),
        );

        $this->assertEquals(
            [
                'Host' => ['challenges.cloudflare.com'],
                'Content-Type' => ['application/x-www-form-urlencoded'],
            ],
            $createRequest->getHeaders(),
        );

        $this->assertEquals(
            (string) $requestParams,
            (string) $createRequest->getBody(),
        );
    }

    public function testSendRequest(): void {
        $client = $this->getMockHttpClientReturn(
            '{"success": true}',
        );

        $response = $client->sendRequest(
            new RequestParameters(
                'secret',
                'response',
                'remoteip',
            ),
        );

        $this->assertEquals(
            '{"success": true}',
            $response,
        );
    }

    private function getMockHttpClient(): Client {
        $mock = $this->createMock(ClientInterface::class);
        $psr17Factory = new Psr17Factory();

        $mock->expects($this->any())
            ->method('sendRequest')
            ->willReturn($psr17Factory->createResponse())
        ;

        return new Client(
            $mock,
            $psr17Factory,
            $psr17Factory,
        );
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

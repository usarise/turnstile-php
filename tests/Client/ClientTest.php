<?php

declare(strict_types=1);

namespace TurnstileTests\Client;

use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\{RequestFactoryInterface, ResponseInterface, StreamFactoryInterface, StreamInterface};
use Turnstile\Client\{Client, RequestParameters};
use Turnstile\Exception\InvalidArgumentException;
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

    public function testBadRequestFactory(): void {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Argument #1 ($client) or argument #2 ($requestFactory) must be support implement '
             . RequestFactoryInterface::class,
        );

        new Client(
            $this->createMock(
                ClientInterface::class,
            ),
        );
    }

    public function testBadStreamFactory(): void {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Argument #1 ($client) or argument #2 ($requestFactory) or argument #3 ($streamFactory) must be support implement '
             . StreamFactoryInterface::class,
        );

        new Client(
            $this->createMock(
                ClientInterface::class,
            ),
            $this->createMock(
                RequestFactoryInterface::class,
            ),
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
        $psr17Factory = new Psr17Factory();
        $httpBody = '{"success": true}';

        $createStream = $psr17Factory->createStream(
            $httpBody,
        );

        $createResponse = $psr17Factory->createResponse()->withBody(
            $createStream,
        );

        $httpClientReturn = $this->getMockHttpClientReturn(
            $createStream,
        );

        $response = $httpClientReturn->sendRequest(
            new RequestParameters(
                'secret',
                'response',
                'remoteip',
            ),
        );

        $this->assertInstanceOf(
            ResponseInterface::class,
            $response,
        );
        $this->assertEquals(
            $createResponse,
            $response,
        );
        $this->assertEquals(
            $httpBody,
            (string) $response->getBody(),
        );
        $this->assertEquals(
            200,
            $response->getStatusCode(),
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

    private function getMockHttpClientReturn(StreamInterface $stream): Client {
        $mock = $this->createMock(ClientInterface::class);
        $psr17Factory = new Psr17Factory();

        $createResponse = $psr17Factory->createResponse(200)
            ->withBody(
                $stream,
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

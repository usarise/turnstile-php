<?php

declare(strict_types=1);

namespace TurnstileTests\Client;

use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\{RequestFactoryInterface, ResponseInterface, StreamFactoryInterface};
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
        $httpClient = $this->createStub(ClientInterface::class);

        $requestFactory = $this->createStub(RequestFactoryInterface::class);
        $streamFactory = $this->createStub(StreamFactoryInterface::class);
        $httpFactory = $this->createStub(HttpFactoryInterface::class);

        $client = new Client(
            $httpClient,
            $requestFactory,
            $streamFactory,
            self::SITE_VERIFY_URL,
        );

        $this->assertSame(
            $httpClient,
            $client->httpClient,
        );

        $this->assertSame(
            $requestFactory,
            $client->requestFactory,
        );

        $this->assertSame(
            $streamFactory,
            $client->streamFactory,
        );

        $this->assertSame(
            self::SITE_VERIFY_URL,
            $client->siteVerifyUrl,
        );

        $psr17Factory = new Psr17Factory();
        $client = new Client(
            $httpClient,
            $psr17Factory,
        );

        $this->assertSame(
            $httpClient,
            $client->httpClient,
        );

        $this->assertSame(
            $psr17Factory,
            $client->requestFactory,
        );

        $this->assertSame(
            $psr17Factory,
            $client->streamFactory,
        );

        $this->assertSame(
            TurnstileInterface::SITE_VERIFY_URL,
            $client->siteVerifyUrl,
        );

        $client = new Client(
            $httpFactory,
        );

        $this->assertSame(
            $httpFactory,
            $client->httpClient,
        );

        $this->assertSame(
            $httpFactory,
            $client->requestFactory,
        );

        $this->assertSame(
            $httpFactory,
            $client->streamFactory,
        );

        $this->assertSame(
            TurnstileInterface::SITE_VERIFY_URL,
            $client->siteVerifyUrl,
        );
    }

    public function testBadRequestFactory(): void {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Argument #1 ($httpClient) or argument #2 ($requestFactory) must be support implement '
             . RequestFactoryInterface::class,
        );

        new Client(
            $this->createStub(
                ClientInterface::class,
            ),
        );
    }

    public function testBadStreamFactory(): void {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Argument #1 ($httpClient) or argument #2 ($requestFactory) or argument #3 ($streamFactory) must be support implement '
             . StreamFactoryInterface::class,
        );

        new Client(
            $this->createStub(
                ClientInterface::class,
            ),
            $this->createStub(
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

        $this->assertSame(
            'POST',
            $createRequest->getMethod(),
        );

        $this->assertSame(
            TurnstileInterface::SITE_VERIFY_URL,
            (string) $createRequest->getUri(),
        );

        $this->assertSame(
            [
                'Host' => ['challenges.cloudflare.com'],
                'Content-Type' => ['application/x-www-form-urlencoded'],
            ],
            $createRequest->getHeaders(),
        );

        $this->assertSame(
            (string) $requestParams,
            (string) $createRequest->getBody(),
        );
    }

    public function testSendRequest(): void {
        $httpBody = '{"success": true}';
        $httpClientReturn = $this->getMockHttpClientReturn($httpBody);

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
        $this->assertSame(
            $httpBody,
            (string) $response->getBody(),
        );
        $this->assertSame(
            200,
            $response->getStatusCode(),
        );
    }

    private function getMockHttpClient(): Client {
        $stub = $this->createStub(ClientInterface::class);
        $psr17Factory = new Psr17Factory();

        $stub
            ->method('sendRequest')
            ->willReturn($psr17Factory->createResponse())
        ;

        return new Client(
            $stub,
            $psr17Factory,
            $psr17Factory,
        );
    }

    private function getMockHttpClientReturn(string $httpBody): Client {
        $stub = $this->createStub(ClientInterface::class);
        $psr17Factory = new Psr17Factory();

        $createResponse = $psr17Factory->createResponse(200)
            ->withBody(
                $psr17Factory->createStream(
                    $httpBody,
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

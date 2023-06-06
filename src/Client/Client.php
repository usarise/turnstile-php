<?php

declare(strict_types=1);

namespace Turnstile\Client;

use Psr\Http\Client\ClientInterface as HttpClientInterface;
use Psr\Http\Message\{RequestFactoryInterface, RequestInterface, StreamFactoryInterface};
use Turnstile\TurnstileInterface;

final class Client {
    public readonly RequestFactoryInterface $requestFactory;
    public readonly StreamFactoryInterface $streamFactory;

    public function __construct(
        public readonly HttpClientInterface $client,
        ?RequestFactoryInterface $requestFactory = null,
        ?StreamFactoryInterface $streamFactory = null,
        public readonly string $siteVerifyUrl = TurnstileInterface::SITE_VERIFY_URL,
    ) {
        $requestFactory ??= $client;
        $streamFactory ??= $requestFactory;

        $this->requestFactory = $requestFactory;
        $this->streamFactory = $streamFactory;
    }

    public function createRequest(RequestBody $requestBody): RequestInterface {
        return $this->requestFactory
            ->createRequest('POST', $this->siteVerifyUrl)
            ->withHeader('Content-Type', 'application/x-www-form-urlencoded')
            ->withBody(
                $this->streamFactory->createStream(
                    (string) $requestBody,
                ),
            )
        ;
    }

    public function sendRequest(RequestBody $requestBody): string {
        return (string) $this->client->sendRequest(
            $this->createRequest(
                $requestBody,
            ),
        )
        ->getBody()
        ;
    }
}

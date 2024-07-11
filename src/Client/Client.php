<?php

declare(strict_types=1);

namespace Turnstile\Client;

use Psr\Http\Client\ClientInterface as PsrHttpClientInterface;
use Psr\Http\Message\{RequestFactoryInterface, RequestInterface, StreamFactoryInterface};
use Turnstile\{TurnstileException , TurnstileInterface};

final class Client {
    public readonly RequestFactoryInterface $requestFactory;
    public readonly StreamFactoryInterface $streamFactory;

    public function __construct(
        public readonly PsrHttpClientInterface $client,
        ?RequestFactoryInterface $requestFactory = null,
        ?StreamFactoryInterface $streamFactory = null,
        public readonly string $siteVerifyUrl = TurnstileInterface::SITE_VERIFY_URL,
    ) {
        $requestFactory ??= $client;
        $streamFactory ??= $requestFactory;

        if (!$requestFactory instanceof RequestFactoryInterface) {
            throw new TurnstileException(
                'Argument #1 ($client) or argument #2 ($requestFactory) must be support implement ' .
                RequestFactoryInterface::class,
            );
        }

        if (!$streamFactory instanceof StreamFactoryInterface) {
            throw new TurnstileException(
                'Argument #1 ($client) or argument #2 ($requestFactory) or argument #3 ($streamFactory) must be support implement ' .
                StreamFactoryInterface::class,
            );
        }

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

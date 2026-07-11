<?php

declare(strict_types=1);

namespace Turnstile\Client\Abstract;

use Psr\Http\Message\ResponseInterface;

/**
 * @api
 */
abstract class Response implements \Stringable {
    /**
     * @param array<string, mixed> $jsonDecode
     */
    public function __construct(
        public readonly ?ResponseInterface $httpResponse = null,
        protected readonly array $jsonDecode = [],
        protected readonly string $httpBody = '',
    ) {}

    abstract public static function decode(ResponseInterface $httpResponse): static;

    /**
     * @return array<string, mixed>
     */
    final public function toArray(bool $strict = false): array {
        return match ($strict) {
            true => \array_slice(
                array: get_object_vars($this),
                offset: 3,
            ),
            default => $this->jsonDecode,
        };
    }

    final public function __toString(): string {
        return $this->httpBody;
    }
}

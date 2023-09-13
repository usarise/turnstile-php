<?php

declare(strict_types=1);

namespace Turnstile\Client;

abstract class ResponseBase implements \Stringable {
    /**
     * @param array<string, mixed> $jsonDecode
     */
    public function __construct(
        protected readonly array $jsonDecode = [],
        protected readonly string $httpBody = '',
    ) {}

    abstract public static function decode(string $httpResponse): static;

    /**
     * @return array<string, mixed>
     */
    final public function toArray(bool $strict = false): array {
        return match ($strict) {
            true => \array_slice(
                array: get_object_vars($this),
                offset: 2,
            ),
            default => $this->jsonDecode,
        };
    }

    final public function __toString(): string {
        return $this->httpBody;
    }
}

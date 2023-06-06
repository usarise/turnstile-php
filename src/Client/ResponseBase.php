<?php

declare(strict_types=1);

namespace Turnstile\Client;

abstract class ResponseBase implements \Stringable {
    public function __construct(
        protected readonly string $httpBody = '',
    ) {
    }

    abstract public static function decode(string $httpResponse): static;

    /**
     * @return array<string, mixed>
     */
    final public function toArray(): array {
        return \array_slice(
            array: get_object_vars($this),
            offset: 1,
        );
    }

    final public function __toString(): string {
        return $this->httpBody;
    }
}

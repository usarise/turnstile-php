<?php

declare(strict_types=1);

namespace Turnstile\Client;

abstract class RequestBody implements \Stringable {
    /**
     * @return array<string, string>
     */
    final public function toArray(): array {
        return array_filter(
            get_object_vars($this),
            static fn($var): bool => $var !== null,
        );
    }

    final public function __toString(): string {
        return http_build_query(
            data: $this->toArray(),
            arg_separator: '&',
        );
    }
}

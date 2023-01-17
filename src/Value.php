<?php

declare(strict_types=1);

namespace Conia\Boiler;

use Conia\Boiler\Error\RuntimeException;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;
use Throwable;

class Value implements ValueInterface
{
    public function __construct(protected readonly mixed $value)
    {
    }

    public function __toString(): string
    {
        return htmlspecialchars(
            (string)$this->value,
            ENT_QUOTES | ENT_SUBSTITUTE,
            'UTF-8',
        );
    }

    public function __get(string $name): mixed
    {
        try {
            /**
             * @psalm-suppress MixedPropertyFetch
             *
             * Wrapper::wrap checks types
             */
            return Wrapper::wrap($this->value->{$name});
        } catch (Throwable) {
            throw new RuntimeException('No such property');
        }
    }

    public function __set(string $name, mixed $value): void
    {
        try {
            $this->value->{$name} = $value;

            return;
        } catch (Throwable) {
            throw new RuntimeException('No such property');
        }
    }

    public function __call(string $name, array $args): mixed
    {
        if (is_callable([$this->value, $name])) {
            return Wrapper::wrap($this->value->{$name}(...$args));
        }

        throw new RuntimeException('No such method');
    }

    public function __invoke(mixed ...$args): mixed
    {
        if (is_callable($this->value)) {
            return Wrapper::wrap(($this->value)(...$args));
        }

        throw new RuntimeException('No such method');
    }

    public function unwrap(): mixed
    {
        return $this->value;
    }

    public function clean(
        HtmlSanitizerConfig $config = null,
        bool $removeEmptyLines = true
    ): string {
        return Sanitizer::clean((string)$this->value, $config, $removeEmptyLines);
    }

    public function empty(): bool
    {
        return empty($this->value);
    }
}

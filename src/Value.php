<?php

declare(strict_types=1);

namespace Conia\Boiler;

use \Throwable;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;
use Conia\Boiler\Error\{NoSuchMethod, NoSuchProperty};


class Value implements ValueInterface
{
    public function __construct(protected readonly mixed $value)
    {
    }

    public function raw(): mixed
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
        return empty((string)$this->value);
    }

    public function __toString(): string
    {
        return htmlspecialchars((string)$this->value);
    }

    public function __get(string $name): mixed
    {
        try {
            return Wrapper::wrap($this->value->{$name});
        } catch (Throwable) {
            throw new NoSuchProperty('Property does not exists on the wrapped value');
        }
    }

    public function __set(string $name, mixed $value): void
    {
        try {
            $this->value->{$name} = $value;
            return;
        } catch (Throwable) {
            throw new NoSuchProperty('Property does not exists on the wrapped value');
        }
    }

    public function __call(string $name, array $args): mixed
    {
        if (is_callable([$this->value, $name])) {
            return Wrapper::wrap($this->value->$name(...$args));
        }

        throw new NoSuchMethod('Method does not exists on the wrapped value');
    }
}

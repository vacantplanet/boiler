<?php

declare(strict_types=1);

namespace VacantPlanet\Boiler\Proxy;

use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;
use Throwable;
use VacantPlanet\Boiler\Exception\RuntimeException;
use VacantPlanet\Boiler\Sanitizer;
use VacantPlanet\Boiler\Wrapper;

/** @psalm-api */
class Proxy implements ProxyInterface
{
    public function __construct(protected readonly mixed $value) {}

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

    public function strip(null|array|string $allowed = null): string
    {
        /**
         * As of now (early 2023), psalm does not support the
         * type array as arguments to strip_tags's $allowed_tags.
         *
         * @psalm-suppress PossiblyInvalidArgument
         */
        return strip_tags((string)$this->value, $allowed);
    }

    public function clean(
        ?HtmlSanitizerConfig $config = null,
        bool $removeEmptyLines = true
    ): string {
        return Sanitizer::clean((string)$this->value, $config, $removeEmptyLines);
    }

    public function empty(): bool
    {
        return empty($this->value);
    }
}

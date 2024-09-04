<?php

declare(strict_types=1);

namespace VacantPlanet\Boiler\Proxy;

use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;
use VacantPlanet\Boiler\Exception\RuntimeException;
use VacantPlanet\Boiler\Sanitizer;
use VacantPlanet\Boiler\Wrapper;

/** @psalm-api */
class ValueProxy implements ProxyInterface
{
	public function __construct(protected readonly mixed $value) {}

	public function __toString(): string
	{
		return htmlspecialchars(
			(string) $this->value,
			ENT_QUOTES | ENT_SUBSTITUTE,
			'UTF-8',
		);
	}

	public function __get(string $name): mixed
	{
		if (is_object($this->value) && property_exists($this->value, $name)) {
			return Wrapper::wrap($this->value->{$name});
		}

		throw new RuntimeException('No such property');
	}

	public function __set(string $name, mixed $value): void
	{
		if (is_object($this->value) && property_exists($this->value, $name)) {
			$this->value->{$name} = $value;

			return;
		}

		throw new RuntimeException('No such property');
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

	/**
	 * @param array<array-key, string>|null|string $allowed
	 */
	public function strip(null|array|string $allowed = null): string
	{
		return strip_tags((string) $this->value, $allowed);
	}

	public function clean(
		?HtmlSanitizerConfig $config = null,
	): string {
		return (new Sanitizer($config))->clean((string) $this->value);
	}

	public function empty(): bool
	{
		return empty($this->value);
	}
}

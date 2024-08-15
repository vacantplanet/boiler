<?php

declare(strict_types=1);

namespace VacantPlanet\Boiler\Proxy;

use ArrayAccess;
use Countable;
use Iterator;
use VacantPlanet\Boiler\Exception\OutOfBoundsException;
use VacantPlanet\Boiler\Exception\RuntimeException;
use VacantPlanet\Boiler\Exception\UnexpectedValueException;
use VacantPlanet\Boiler\Wrapper;

/**
 * @psalm-api
 *
 * @psalm-type ArrayCallable = callable(mixed, mixed):int
 * @psalm-type FilterCallable = callable(mixed):mixed
 *
 * @template-implements ArrayAccess<array-key, mixed>
 * @template-implements Iterator<mixed>
 *
 * @psalm-suppress MixedArrayOffset -- ArrayValue is meant to hold mixed values accessed by mixed keys
 */
class ArrayProxy implements ArrayAccess, Iterator, Countable, ProxyInterface
{
	private int $position;
	private array $keys;

	public function __construct(private array $array)
	{
		$this->array = $array;
		$this->keys = array_keys($array);
		$this->position = 0;
	}

	public function unwrap(): array
	{
		return $this->array;
	}

	public function rewind(): void
	{
		$this->position = 0;
	}

	public function current(): mixed
	{
		return Wrapper::wrap($this->array[$this->key()]);
	}

	public function key(): mixed
	{
		return $this->keys[$this->position];
	}

	public function next(): void
	{
		$this->position++;
	}

	public function valid(): bool
	{
		return isset($this->keys[$this->position]);
	}

	/** @param array-key $offset */
	public function offsetExists(mixed $offset): bool
	{
		// isset is significantly faster than array_key_exists but
		// returns false when the value exists but is null.
		return isset($this->array[$offset]) || array_key_exists($offset, $this->array);
	}

	/** @param array-key $offset */
	public function offsetGet(mixed $offset): mixed
	{
		if ($this->offsetExists($offset)) {
			return Wrapper::wrap($this->array[$offset]);
		}

		if (is_numeric($offset)) {
			$key = (string) $offset;
		} else {
			$key = "'{$offset}'";
		}

		throw new OutOfBoundsException("Undefined array key {$key}");
	}

	public function offsetSet(mixed $offset, mixed $value): void
	{
		if (is_int($offset)) {
			$this->array[$offset] = $value;
		} else {
			$this->array[] = $value;
		}
	}

	public function offsetUnset(mixed $offset): void
	{
		unset($this->array[$offset]);
	}

	public function count(): int
	{
		return count($this->array);
	}

	/** @param array-key $key */
	public function exists(mixed $key): bool
	{
		return array_key_exists($key, $this->array);
	}

	public function merge(array|self $array): self
	{
		return new self(array_merge(
			$this->array,
			$array instanceof self ? $array->unwrap() : $array,
		));
	}

	/** @psalm-param ArrayCallable $callable */
	public function map(callable $callable): self
	{
		return new self(array_map($callable, $this->array));
	}

	/** @psalm-param FilterCallable $callable */
	public function filter(callable $callable): self
	{
		return new self(array_filter($this->array, $callable));
	}

	/** @psalm-param ArrayCallable $callable */
	public function reduce(callable $callable, mixed $initial = null): mixed
	{
		return Wrapper::wrap(array_reduce($this->array, $callable, $initial));
	}

	/** @psalm-param ArrayCallable $callable */
	public function sorted(string $mode = '', ?callable $callable = null): self
	{
		$mode = strtolower(trim($mode));

		if (str_starts_with($mode, 'u')) {
			if (!is_callable($callable)) {
				throw new RuntimeException('No callable provided for user defined sorting');
			}

			return $this->usort($this->array, $mode, $callable);
		}

		return $this->sort($this->array, $mode);
	}

	protected function sort(array $array, string $mode): self
	{
		match ($mode) {
			'' => sort($array),
			'ar' => arsort($array),
			'a' => asort($array),
			'kr' => krsort($array),
			'k' => ksort($array),
			'r' => rsort($array),
			default => throw new UnexpectedValueException("Sort mode '{$mode}' not supported"),
		};

		return new self($array);
	}

	/** @psalm-param ArrayCallable $callable */
	protected function usort(array $array, string $mode, callable $callable): self
	{
		match ($mode) {
			'ua' => uasort($array, $callable),
			'u' => usort($array, $callable),
			default => throw new UnexpectedValueException("Sort mode '{$mode}' not supported"),
		};

		return new self($array);
	}
}

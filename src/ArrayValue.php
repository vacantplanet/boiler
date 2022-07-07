<?php

declare(strict_types=1);

namespace Conia\Boiler;

use \ArrayAccess;
use \Iterator;
use \Countable;
use \ErrorException;


class ArrayValue implements ArrayAccess, Iterator, Countable, ValueInterface
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

    function rewind(): void
    {
        $this->position = 0;
    }

    function current(): mixed
    {
        return Wrapper::wrap($this->array[$this->key()]);
    }

    function key(): mixed
    {
        return $this->keys[$this->position];
    }

    function next(): void
    {
        ++$this->position;
    }

    function valid(): bool
    {
        return isset($this->keys[$this->position]);
    }

    public function offsetExists(mixed $offset): bool
    {
        // isset is significantly faster than array_key_exists but
        // returns false when the value exists but is null.
        return isset($this->array[$offset]) || array_key_exists($offset, $this->array);
    }

    public function offsetGet(mixed $offset): mixed
    {
        if ($this->offsetExists($offset)) {
            return Wrapper::wrap($this->array[$offset]);
        } else {
            if (is_numeric($offset)) {
                $key = (string)$offset;
            } else {
                $key = "'$offset'";
            }

            throw new ErrorException("Undefined array key $key");
        };
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        if ($offset) {
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
}

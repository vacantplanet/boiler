<?php

declare(strict_types=1);

namespace VacantPlanet\Boiler\Proxy;

use Iterator;
use IteratorIterator;
use VacantPlanet\Boiler\Wrapper;

/**
 * @psalm-api
 *
 * @template-covariant TKey
 * @template-covariant TValue
 *
 * @template TIterator as \Traversable<TKey, TValue>
 *
 * @template-extends IteratorIterator<TKey, TValue, TIterator>
 */
class IteratorProxy extends IteratorIterator implements ProxyInterface
{
    public function current(): mixed
    {
        $value = parent::current();

        return Wrapper::wrap($value);
    }

    public function unwrap(): Iterator
    {
        return $this->getInnerIterator();
    }

    public function toArray(): ArrayProxy
    {
        return new ArrayProxy(iterator_to_array($this->getInnerIterator()));
    }
}

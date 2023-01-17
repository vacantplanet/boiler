<?php

declare(strict_types=1);

namespace Conia\Boiler;

use Iterator;
use IteratorIterator;

/**
 * @template-covariant TKey
 * @template-covariant TValue
 *
 * @template TIterator as \Traversable<TKey, TValue>
 *
 * @template-extends IteratorIterator<TKey, TValue, TIterator>
 */
class IteratorValue extends IteratorIterator implements ValueInterface
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

    public function toArray(): ArrayValue
    {
        return new ArrayValue(iterator_to_array($this->getInnerIterator()));
    }
}

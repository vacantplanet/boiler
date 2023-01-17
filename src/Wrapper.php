<?php

declare(strict_types=1);

namespace Conia\Boiler;

use Traversable;

class Wrapper
{
    public static function wrap(mixed $value): mixed
    {
        if (is_string($value)) {
            return new Value($value);
        }
        if ($value instanceof ValueInterface) {
            // Don't wrap already wrapped values again
            return $value;
        }
        if (is_numeric($value)) {
            return $value;
        }
        if (is_array($value)) {
            return new ArrayValue($value);
        }
        if ($value instanceof Traversable) {
            return new IteratorValue($value);
        }
        if (is_null($value)) {
            return $value;
        }

        return new Value($value);
    }
}

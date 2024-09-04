<?php

declare(strict_types=1);

namespace VacantPlanet\Boiler;

use Traversable;
use VacantPlanet\Boiler\Proxy\ArrayProxy;
use VacantPlanet\Boiler\Proxy\IteratorProxy;
use VacantPlanet\Boiler\Proxy\ProxyInterface;
use VacantPlanet\Boiler\Proxy\ValueProxy;

class Wrapper
{
	public static function wrap(mixed $value): mixed
	{
		if (is_scalar($value)) {
			if (is_string($value)) {
				return new ValueProxy($value);
			}

			return $value;
		}

		if ($value instanceof ProxyInterface) {
			// Don't wrap already wrapped values again
			return $value;
		}

		if (is_array($value)) {
			return new ArrayProxy($value);
		}

		if ($value instanceof Traversable) {
			return new IteratorProxy($value);
		}

		if (is_null($value)) {
			return null;
		}

		return new ValueProxy($value);
	}
}

<?php

declare(strict_types=1);

namespace VacantPlanet\Boiler;

use Traversable;
use VacantPlanet\Boiler\Proxy\ArrayProxy;
use VacantPlanet\Boiler\Proxy\IteratorProxy;
use VacantPlanet\Boiler\Proxy\Proxy;
use VacantPlanet\Boiler\Proxy\ProxyInterface;

class Wrapper
{
	public static function wrap(mixed $value): mixed
	{
		if (is_scalar($value) && !is_string($value)) {
			return $value;
		}

		if (is_string($value)) {
			return new Proxy($value);
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

		return new Proxy($value);
	}
}

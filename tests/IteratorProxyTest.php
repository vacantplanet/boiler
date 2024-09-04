<?php

declare(strict_types=1);

namespace VacantPlanet\Boiler\Tests;

use VacantPlanet\Boiler\Proxy\ArrayProxy;
use VacantPlanet\Boiler\Proxy\IteratorProxy;
use VacantPlanet\Boiler\Proxy\ValueProxy;

final class IteratorProxyTest extends TestCase
{
	public function testIteratorProxyWrapping(): void
	{
		$iterator = (function () {
			yield 1;

			yield 'string';

			yield [1, 2];

			yield (function () {
				yield 1;
			})();
		})();

		$iterval = new IteratorProxy($iterator);
		$new = [];

		foreach ($iterval as $val) {
			$new[] = $val;
		}

		$this->assertSame(1, $new[0]);
		$this->assertInstanceOf(ValueProxy::class, $new[1]);
		$this->assertInstanceOf(ArrayProxy::class, $new[2]);
		$this->assertInstanceOf(IteratorProxy::class, $new[3]);
	}

	public function testIteratorProxyUnwrap(): void
	{
		$iterator = (function () {
			yield 1;
		})();

		$iterval = new IteratorProxy($iterator);

		$this->assertSame($iterator, $iterval->unwrap());
	}

	public function testIteratorProxyToArray(): void
	{
		$iterator = (function () {
			yield 1;

			yield 2;
		})();

		$iterval = new IteratorProxy($iterator);

		$this->assertSame([1, 2], $iterval->toArray()->unwrap());
	}
}

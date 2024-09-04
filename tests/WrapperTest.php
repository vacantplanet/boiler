<?php

declare(strict_types=1);

namespace VacantPlanet\Boiler\Tests;

use Traversable;
use VacantPlanet\Boiler\Proxy\ArrayProxy;
use VacantPlanet\Boiler\Proxy\IteratorProxy;
use VacantPlanet\Boiler\Proxy\ValueProxy;
use VacantPlanet\Boiler\Wrapper;

final class WrapperTest extends TestCase
{
	public function testWrapNumber(): void
	{
		$this->assertSame(13, Wrapper::wrap(13));
		$this->assertSame(1.13, Wrapper::wrap(1.13));
	}

	public function testWrapString(): void
	{
		$this->assertInstanceOf(ValueProxy::class, Wrapper::wrap('string'));
	}

	public function testWrapArray(): void
	{
		$warray = Wrapper::wrap([1, 2, 3]);

		$this->assertInstanceOf(ArrayProxy::class, $warray);
		$this->assertSame(false, is_array($warray));
		$this->assertSame(true, is_array($warray->unwrap()));
		$this->assertSame(3, count($warray));
	}

	public function testWrapIterator(): void
	{
		$iterator = (function () {
			yield 1;
		})();
		$witerator = Wrapper::wrap($iterator);

		$this->assertInstanceOf(IteratorProxy::class, $witerator);
		$this->assertInstanceOf(Traversable::class, $witerator->unwrap());
		$this->assertSame(true, is_iterable($witerator->unwrap()));
	}

	public function testWrapObject(): void
	{
		$obj = new class {};

		$this->assertInstanceOf(ValueProxy::class, Wrapper::wrap($obj));
	}

	public function testWrapStringable(): void
	{
		$obj = new class {
			public function __toString(): string
			{
				return '';
			}
		};

		$this->assertInstanceOf(ValueProxy::class, Wrapper::wrap($obj));
	}

	public function testNestingWrapping(): void
	{
		$value = new ValueProxy('string');

		$this->assertInstanceOf(ValueProxy::class, Wrapper::wrap($value));
		$this->assertSame('string', Wrapper::wrap($value)->unwrap());
		$this->assertSame(true, is_string(Wrapper::wrap($value)->unwrap()));
		$this->assertInstanceOf(ValueProxy::class, Wrapper::wrap($value));
	}
}

<?php

declare(strict_types=1);

namespace VacantPlanet\Boiler\Tests;

use VacantPlanet\Boiler\Exception\OutOfBoundsException;
use VacantPlanet\Boiler\Exception\RuntimeException;
use VacantPlanet\Boiler\Exception\UnexpectedValueException;
use VacantPlanet\Boiler\Proxy\ArrayProxy;
use VacantPlanet\Boiler\Proxy\IteratorProxy;
use VacantPlanet\Boiler\Proxy\ValueProxy;

final class ArrayProxyTest extends TestCase
{
	public function testCount(): void
	{
		$arrval = new ArrayProxy([1, 2, 3]);

		$this->assertSame(3, count($arrval));
	}

	public function testUnwrap(): void
	{
		$arrval = new ArrayProxy(['string', 2]);

		$this->assertSame(['string', 2], $arrval->unwrap());
	}

	public function testHelperExists(): void
	{
		$arrval = new ArrayProxy([1, 2]);

		$this->assertSame(true, $arrval->exists(0));
		$this->assertSame(true, $arrval->exists(1));
		$this->assertSame(false, $arrval->exists(2));
		$this->assertSame(false, $arrval->exists('test'));

		$arrval = new ArrayProxy([1, 'test' => 2]);

		$this->assertSame(true, $arrval->exists(0));
		$this->assertSame(false, $arrval->exists(1));
		$this->assertSame(false, $arrval->exists(2));
		$this->assertSame(true, $arrval->exists('test'));
	}

	public function testHelperMerge(): void
	{
		$arrval1 = new ArrayProxy([1, 2]);
		$arrval2 = new ArrayProxy([3, 4]);

		$this->assertSame([1, 2, 3, 4], $arrval1->merge($arrval2)->unwrap());
		$this->assertSame([1, 2, 5, 6], $arrval1->merge([5, 6])->unwrap());
		$this->assertSame(6, $arrval1->merge([5, 6])[3]);
		$this->assertInstanceOf(ValueProxy::class, $arrval1->merge([5, 'string'])[3]);
	}

	public function testHelperMap(): void
	{
		$arrval = new ArrayProxy(['str1', 'str2']);

		$this->assertSame(['str1plus', 'str2plus'], $arrval->map(fn($v) => $v . 'plus')->unwrap());
	}

	public function testHelperFilter(): void
	{
		$arrval = new ArrayProxy([1, 3, 4, 2]);

		$this->assertSame([1, 2], array_values($arrval->filter(fn($v) => $v < 3)->unwrap()));
	}

	public function testHelperReduce(): void
	{
		$arrval = new ArrayProxy([1, 3, 4, 2]);

		$this->assertSame(10, $arrval->reduce(fn($c, $v) => $c + $v, 0));

		$arrval = new ArrayProxy(['a', 'b', 'c']);

		$this->assertSame('abc', $arrval->reduce(fn($c, $v) => $c . $v, '')->unwrap());
	}

	public function testHelperSorted(): void
	{
		$arrval = new ArrayProxy([1, 3, 4, 2]);
		$this->assertSame([1, 2, 3, 4], $arrval->sorted()->unwrap());

		$arrval = new ArrayProxy(['a' => 3, 'b' => 1, 'c' => 2]);
		$this->assertSame([1, 2, 3], $arrval->sorted()->unwrap());
		$this->assertSame([1, 2, 3], $arrval->sorted('  ')->unwrap());
		$this->assertSame([3, 2, 1], $arrval->sorted('r')->unwrap());
		$this->assertSame(['b' => 1, 'c' => 2, 'a' => 3], $arrval->sorted('a')->unwrap());
		$this->assertSame(['a' => 3, 'c' => 2, 'b' => 1], $arrval->sorted('ar')->unwrap());
		// Check if original value is preserved
		$this->assertSame(['a' => 3, 'b' => 1, 'c' => 2], $arrval->unwrap());

		$arrval = new ArrayProxy(['b' => 3, 'c' => 1, 'a' => 2]);
		$this->assertSame(['a' => 2, 'b' => 3, 'c' => 1], $arrval->sorted('k')->unwrap());
		$this->assertSame(['c' => 1, 'b' => 3, 'a' => 2], $arrval->sorted('kr')->unwrap());
	}

	public function testHelperSortedThrows(): void
	{
		$this->throws(UnexpectedValueException::class);

		$arrval = new ArrayProxy(['B', 'a']);
		$arrval->sorted('t');
	}

	public function testHelperSortedUserdefined(): void
	{
		$arrval = new ArrayProxy(['B', 'a', 'C', 'c', 'A', 'b']);
		$this->assertSame(
			['a', 'A', 'B', 'b', 'C', 'c'],
			$arrval->sorted(
				'u',
				function ($a, $b) {
					if (strtolower($a) > strtolower($b)) {
						return 1;
					}

					if (strtolower($a) < strtolower($b)) {
						return -1;
					}

					return 0;
				},
			)->unwrap(),
		);

		$this->assertSame(
			[1 => 'a', 4 => 'A', 0 => 'B', 5 => 'b', 2 => 'C', 3 => 'c'],
			$arrval->sorted(
				'ua',
				function ($a, $b) {
					if (strtolower($a) > strtolower($b)) {
						return 1;
					}

					if (strtolower($a) < strtolower($b)) {
						return -1;
					}

					return 0;
				},
			)->unwrap(),
		);
	}

	public function testHelperSortedUserdefinedThrows(): void
	{
		$this->throws(UnexpectedValueException::class);

		$arrval = new ArrayProxy(['B', 'a']);
		$arrval->sorted('ut', fn($a, $b) => strtolower($a) > strtolower($b));
	}

	public function testHelperSortedUserdefinedThrowsNoCallable(): void
	{
		$this->throws(RuntimeException::class);

		$arrval = new ArrayProxy(['B', 'a']);
		$arrval->sorted('u');
	}

	public function testArrayAccess(): void
	{
		$arrval = new ArrayProxy([1, 2, 'key' => 3]);

		$this->assertSame(1, $arrval[0]);
		$this->assertSame(2, $arrval[1]);
		$this->assertSame(3, $arrval['key']);
	}

	public function testIteration(): void
	{
		$arrval = new ArrayProxy([1, 2, 3]);
		$new = [];

		foreach ($arrval as $val) {
			$new[] = $val + 2;
		}

		$this->assertSame([3, 4, 5], $new);
	}

	public function testNullValue(): void
	{
		$arrval = new ArrayProxy([1, null]);

		$this->assertSame(1, $arrval[0]);
		$this->assertSame(null, $arrval[1]);
	}

	public function testSetValue(): void
	{
		$arrval = new ArrayProxy([1, 2, 3]);
		$arrval[3] = 44;
		$arrval[] = 55;

		$this->assertSame([1, 2, 3, 44, 55], $arrval->unwrap());
	}

	public function testUnsetValue(): void
	{
		$arrval = new ArrayProxy([1, 2, 3]);
		unset($arrval[1]);

		$this->assertSame([0 => 1, 2 => 3], $arrval->unwrap());
	}

	public function testWrappedArrayAccess(): void
	{
		$obj = new class {};
		$stringable = new class {
			public function __toString(): string
			{
				return '';
			}
		};
		$iterator = (function () {
			yield 1;
		})();
		$arrval = new ArrayProxy(['string', $obj, $stringable, [1, 2], $iterator]);

		$this->assertInstanceOf(ValueProxy::class, $arrval[0]);
		$this->assertInstanceOf(ValueProxy::class, $arrval[1]);
		$this->assertInstanceOf(ValueProxy::class, $arrval[2]);
		$this->assertInstanceOf(ArrayProxy::class, $arrval[3]);
		$this->assertInstanceOf(IteratorProxy::class, $arrval[4]);
	}

	public function testWrappedIteration(): void
	{
		$obj = new class {};
		$stringable = new class {
			public function __toString(): string
			{
				return '';
			}
		};
		$iterator = (function () {
			yield 1;
		})();
		$arrval = new ArrayProxy(['string', $obj, $stringable, [1, 2], $iterator]);

		$new = [];

		foreach ($arrval as $val) {
			$new[] = $val::class;
		}

		$this->assertSame([
			ValueProxy::class,
			ValueProxy::class,
			ValueProxy::class,
			ArrayProxy::class,
			IteratorProxy::class,
		], $new);
	}

	public function testNestedHahns(): void
	{
		$arrval = new ArrayProxy([['first'], ['second', 'third']]);

		$this->assertInstanceOf(ArrayProxy::class, $arrval[0]);
		$this->assertInstanceOf(ValueProxy::class, $arrval[0][0]);
		$this->assertInstanceOf(ArrayProxy::class, $arrval[1]);
		$this->assertInstanceOf(ValueProxy::class, $arrval[1][0]);
		$this->assertInstanceOf(ValueProxy::class, $arrval[1][1]);
	}

	public function testUndefinedNumericKey(): void
	{
		$this->throws(OutOfBoundsException::class, 'Undefined array key 4');

		$arrval = new ArrayProxy([1, 2, 3]);
		$arrval[4];
	}

	public function testUndefinedArrayKey(): void
	{
		$this->throws(OutOfBoundsException::class, "Undefined array key 'key'");

		$arrval = new ArrayProxy([1, 2, 3]);
		$arrval['key'];
	}
}

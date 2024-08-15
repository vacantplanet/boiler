<?php

declare(strict_types=1);

use VacantPlanet\Boiler\Proxy\ArrayProxy;
use VacantPlanet\Boiler\Proxy\IteratorProxy;
use VacantPlanet\Boiler\Proxy\Proxy;

test('Wrapping', function () {
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

	expect($new[0])->toBe(1);
	expect($new[1])->toBeInstanceOf(Proxy::class);
	expect($new[2])->toBeInstanceOf(ArrayProxy::class);
	expect($new[3])->toBeInstanceOf(IteratorProxy::class);
});

test('Unwrap', function () {
	$iterator = (function () {
		yield 1;
	})();

	$iterval = new IteratorProxy($iterator);

	expect($iterval->unwrap())->toBe($iterator);
});

test('To array', function () {
	$iterator = (function () {
		yield 1;

		yield 2;
	})();

	$iterval = new IteratorProxy($iterator);

	expect($iterval->toArray()->unwrap())->toBe([1, 2]);
});

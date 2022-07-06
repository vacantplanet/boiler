<?php

declare(strict_types=1);

use Conia\Boiler\{ArrayValue, IteratorValue, Value};


test('Wrapping', function () {
    $iterator = (function () {
        yield 1;
        yield 'string';
        yield [1, 2];
        yield (function () {
            yield 1;
        })();
    })();

    $iterval = new IteratorValue($iterator);
    $new = [];

    foreach ($iterval as $val) {
        $new[] = $val;
    }

    expect($new[0])->toBe(1);
    expect($new[1])->toBeInstanceOf(Value::class);
    expect($new[2])->toBeInstanceOf(ArrayValue::class);
    expect($new[3])->toBeInstanceOf(IteratorValue::class);
});


test('Raw', function () {
    $iterator = (function () {
        yield 1;
    })();

    $iterval = new IteratorValue($iterator);

    expect($iterval->raw())->toBe($iterator);
});

<?php

declare(strict_types=1);

use Conia\Boiler\{ArrayValue, IteratorValue, Value};


test('Count', function () {
    $arrval = new ArrayValue([1, 2, 3]);

    expect(count($arrval))->toBe(3);
});


test('Unwrap', function () {
    $arrval = new ArrayValue(['string', 2]);

    expect($arrval->unwrap())->toBe(['string', 2]);
});


test('Helper ::exists', function () {
    $arrval = new ArrayValue([1, 2]);

    expect($arrval->exists(0))->toBe(true);
    expect($arrval->exists(1))->toBe(true);
    expect($arrval->exists(2))->toBe(false);
    expect($arrval->exists('test'))->toBe(false);


    $arrval = new ArrayValue([1, 'test' => 2]);

    expect($arrval->exists(0))->toBe(true);
    expect($arrval->exists(1))->toBe(false);
    expect($arrval->exists(2))->toBe(false);
    expect($arrval->exists('test'))->toBe(true);
});


test('Helper ::merge', function () {
    $arr1 = new ArrayValue([1, 2]);
    $arr2 = new ArrayValue([3, 4]);

    expect($arr1->merge($arr2)->unwrap())->toBe([1, 2, 3, 4]);
    expect($arr1->merge([5, 6])->unwrap())->toBe([1, 2, 5, 6]);
    expect($arr1->merge([5, 6])[3])->toBe(6);
    expect($arr1->merge([5, 'string'])[3])->toBeInstanceOf(Value::class);
});


test('Array access', function () {
    $arrval = new ArrayValue([1, 2, 'key' => 3]);

    expect($arrval[0])->toBe(1);
    expect($arrval[1])->toBe(2);
    expect($arrval['key'])->toBe(3);
});


test('Iteration', function () {
    $arrval = new ArrayValue([1, 2, 3]);
    $new = [];

    foreach ($arrval as $val) {
        $new[] = $val + 2;
    }

    expect($new)->toBe([3, 4, 5]);
});


test('Null value', function () {
    $arrval = new ArrayValue([1, null]);

    expect($arrval[0])->toBe(1);
    expect($arrval[1])->toBe(null);
});


test('Set value', function () {
    $arrval = new ArrayValue([1, 2, 3]);
    $arrval[3] = 44;
    $arrval[] = 55;

    expect($arrval->unwrap())->toBe([1, 2, 3, 44, 55]);
});


test('Unset value', function () {
    $arrval = new ArrayValue([1, 2, 3]);
    unset($arrval[1]);

    expect($arrval->unwrap())->toBe([0 => 1, 2 => 3]);
});


test('Wrapped array access', function () {
    $obj = new class()
    {
    };
    $stringable = new class()
    {
        public function __toString(): string
        {
            return '';
        }
    };
    $iterator = (function () {
        yield 1;
    })();
    $arrval = new ArrayValue(['string', $obj, $stringable, [1, 2], $iterator]);

    expect($arrval[0])->toBeInstanceOf(Value::class);
    expect($arrval[1])->toBeInstanceOf(Value::class);
    expect($arrval[2])->toBeInstanceOf(Value::class);
    expect($arrval[3])->toBeInstanceOf(ArrayValue::class);
    expect($arrval[4])->toBeInstanceOf(IteratorValue::class);
});


test('Wrapped iteration', function () {
    $obj = new class()
    {
    };
    $stringable = new class()
    {
        public function __toString(): string
        {
            return '';
        }
    };
    $iterator = (function () {
        yield 1;
    })();
    $arrval = new ArrayValue(['string', $obj, $stringable, [1, 2], $iterator]);

    $new = [];

    foreach ($arrval as $val) {
        $new[] = $val::class;
    }

    expect($new)->toBe([
        Value::class,
        Value::class,
        Value::class,
        ArrayValue::class,
        IteratorValue::class,
    ]);
});


test('Nested', function () {
    $arrval = new ArrayValue([['first'], ['second', 'third']]);

    expect($arrval[0])->toBeInstanceOf(ArrayValue::class);
    expect($arrval[0][0])->toBeInstanceOf(Value::class);
    expect($arrval[1])->toBeInstanceOf(ArrayValue::class);
    expect($arrval[1][0])->toBeInstanceOf(Value::class);
    expect($arrval[1][1])->toBeInstanceOf(Value::class);
});


test('Undefined numeric key', function () {
    $arrval = new ArrayValue([1, 2, 3]);
    $arrval[4];
})->throws(ErrorException::class, 'Undefined array key 4');


test('Undefined array key', function () {
    $arrval = new ArrayValue([1, 2, 3]);
    $arrval['key'];
})->throws(ErrorException::class, "Undefined array key 'key'");

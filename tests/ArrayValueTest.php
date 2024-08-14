<?php

declare(strict_types=1);

use VacantPlanet\Boiler\Proxy\ArrayProxy;
use VacantPlanet\Boiler\Exception\OutOfBoundsException;
use VacantPlanet\Boiler\Exception\RuntimeException;
use VacantPlanet\Boiler\Exception\UnexpectedValueException;
use VacantPlanet\Boiler\Proxy\IteratorProxy;
use VacantPlanet\Boiler\Proxy\Proxy;

test('Count', function () {
    $arrval = new ArrayProxy([1, 2, 3]);

    expect(count($arrval))->toBe(3);
});


test('Unwrap', function () {
    $arrval = new ArrayProxy(['string', 2]);

    expect($arrval->unwrap())->toBe(['string', 2]);
});


test('Helper ::exists', function () {
    $arrval = new ArrayProxy([1, 2]);

    expect($arrval->exists(0))->toBe(true);
    expect($arrval->exists(1))->toBe(true);
    expect($arrval->exists(2))->toBe(false);
    expect($arrval->exists('test'))->toBe(false);


    $arrval = new ArrayProxy([1, 'test' => 2]);

    expect($arrval->exists(0))->toBe(true);
    expect($arrval->exists(1))->toBe(false);
    expect($arrval->exists(2))->toBe(false);
    expect($arrval->exists('test'))->toBe(true);
});


test('Helper ::merge', function () {
    $arrval1 = new ArrayProxy([1, 2]);
    $arrval2 = new ArrayProxy([3, 4]);

    expect($arrval1->merge($arrval2)->unwrap())->toBe([1, 2, 3, 4]);
    expect($arrval1->merge([5, 6])->unwrap())->toBe([1, 2, 5, 6]);
    expect($arrval1->merge([5, 6])[3])->toBe(6);
    expect($arrval1->merge([5, 'string'])[3])->toBeInstanceOf(Proxy::class);
});


test('Helper ::map', function () {
    $arrval = new ArrayProxy(['str1', 'str2']);

    expect($arrval->map(fn ($v) => $v . 'plus')->unwrap())->toBe(['str1plus', 'str2plus']);
});


test('Helper ::filter', function () {
    $arrval = new ArrayProxy([1, 3, 4, 2]);

    expect(array_values($arrval->filter(fn ($v) => $v < 3)->unwrap()))->toBe([1, 2]);
});


test('Helper ::reduce', function () {
    $arrval = new ArrayProxy([1, 3, 4, 2]);

    expect($arrval->reduce(fn ($c, $v) => $c + $v, 0))->toBe(10);

    $arrval = new ArrayProxy(['a', 'b', 'c']);

    expect($arrval->reduce(fn ($c, $v) => $c . $v, '')->unwrap())->toBe('abc');
});


test('Helper ::sorted', function () {
    $arrval = new ArrayProxy([1, 3, 4, 2]);
    expect($arrval->sorted()->unwrap())->toBe([1, 2, 3, 4]);

    $arrval = new ArrayProxy(['a' => 3, 'b' => 1, 'c' => 2]);
    expect($arrval->sorted()->unwrap())->toBe([1, 2, 3]);
    expect($arrval->sorted('  ')->unwrap())->toBe([1, 2, 3]);
    expect($arrval->sorted('r')->unwrap())->toBe([3, 2, 1]);
    expect($arrval->sorted('a')->unwrap())->toBe(['b' => 1, 'c' => 2, 'a' => 3]);
    expect($arrval->sorted('ar')->unwrap())->toBe(['a' => 3, 'c' => 2, 'b' => 1]);
    // Check if original value is preserved
    expect($arrval->unwrap())->toBe(['a' => 3, 'b' => 1, 'c' => 2]);

    $arrval = new ArrayProxy(['b' => 3, 'c' => 1, 'a' => 2]);
    expect($arrval->sorted('k')->unwrap())->toBe(['a' => 2, 'b' => 3, 'c' => 1]);
    expect($arrval->sorted('kr')->unwrap())->toBe(['c' => 1, 'b' => 3, 'a' => 2]);
});


test('Helper ::sorted throws', function () {
    $arrval = new ArrayProxy(['B', 'a']);
    $arrval->sorted('t');
})->throws(UnexpectedValueException::class);


test('Helper ::sorted userdefined', function () {
    $arrval = new ArrayProxy(['B', 'a', 'C', 'c', 'A', 'b']);
    expect($arrval->sorted(
        'u',
        function ($a, $b) {
            if (strtolower($a) > strtolower($b)) {
                return 1;
            }

            if (strtolower($a) < strtolower($b)) {
                return -1;
            }

            return 0;
        }
    )->unwrap())->toBe(['a', 'A', 'B', 'b', 'C', 'c']);

    expect($arrval->sorted(
        'ua',
        function ($a, $b) {
            if (strtolower($a) > strtolower($b)) {
                return 1;
            }

            if (strtolower($a) < strtolower($b)) {
                return -1;
            }

            return 0;
        }
    )->unwrap())->toBe([1 => 'a', 4 => 'A', 0 => 'B', 5 => 'b', 2 => 'C', 3 => 'c']);
});


test('Helper ::sorted userdefined throws', function () {
    $arrval = new ArrayProxy(['B', 'a']);
    $arrval->sorted('ut', fn ($a, $b) => strtolower($a) > strtolower($b));
})->throws(UnexpectedValueException::class);


test('Helper ::sorted userdefined throws no callable', function () {
    $arrval = new ArrayProxy(['B', 'a']);
    $arrval->sorted('u');
})->throws(RuntimeException::class);

test('Array access', function () {
    $arrval = new ArrayProxy([1, 2, 'key' => 3]);

    expect($arrval[0])->toBe(1);
    expect($arrval[1])->toBe(2);
    expect($arrval['key'])->toBe(3);
});


test('Iteration', function () {
    $arrval = new ArrayProxy([1, 2, 3]);
    $new = [];

    foreach ($arrval as $val) {
        $new[] = $val + 2;
    }

    expect($new)->toBe([3, 4, 5]);
});


test('Null value', function () {
    $arrval = new ArrayProxy([1, null]);

    expect($arrval[0])->toBe(1);
    expect($arrval[1])->toBe(null);
});


test('Set value', function () {
    $arrval = new ArrayProxy([1, 2, 3]);
    $arrval[3] = 44;
    $arrval[] = 55;

    expect($arrval->unwrap())->toBe([1, 2, 3, 44, 55]);
});


test('Unset value', function () {
    $arrval = new ArrayProxy([1, 2, 3]);
    unset($arrval[1]);

    expect($arrval->unwrap())->toBe([0 => 1, 2 => 3]);
});


test('Wrapped array access', function () {
    $obj = new class () {
    };
    $stringable = new class () {
        public function __toString(): string
        {
            return '';
        }
    };
    $iterator = (function () {
        yield 1;
    })();
    $arrval = new ArrayProxy(['string', $obj, $stringable, [1, 2], $iterator]);

    expect($arrval[0])->toBeInstanceOf(Proxy::class);
    expect($arrval[1])->toBeInstanceOf(Proxy::class);
    expect($arrval[2])->toBeInstanceOf(Proxy::class);
    expect($arrval[3])->toBeInstanceOf(ArrayProxy::class);
    expect($arrval[4])->toBeInstanceOf(IteratorProxy::class);
});


test('Wrapped iteration', function () {
    $obj = new class () {
    };
    $stringable = new class () {
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

    expect($new)->toBe([
        Proxy::class,
        Proxy::class,
        Proxy::class,
        ArrayProxy::class,
        IteratorProxy::class,
    ]);
});


test('Nested', function () {
    $arrval = new ArrayProxy([['first'], ['second', 'third']]);

    expect($arrval[0])->toBeInstanceOf(ArrayProxy::class);
    expect($arrval[0][0])->toBeInstanceOf(Proxy::class);
    expect($arrval[1])->toBeInstanceOf(ArrayProxy::class);
    expect($arrval[1][0])->toBeInstanceOf(Proxy::class);
    expect($arrval[1][1])->toBeInstanceOf(Proxy::class);
});


test('Undefined numeric key', function () {
    $arrval = new ArrayProxy([1, 2, 3]);
    $arrval[4];
})->throws(OutOfBoundsException::class, 'Undefined array key 4');


test('Undefined array key', function () {
    $arrval = new ArrayProxy([1, 2, 3]);
    $arrval['key'];
})->throws(OutOfBoundsException::class, "Undefined array key 'key'");

<?php

declare(strict_types=1);

use Conia\Boiler\Value;
use Conia\Boiler\Error\{NoSuchMethod, NoSuchProperty};


test('String value', function () {
    $html = '<b onclick="func()">boiler</b>';
    $value = new Value($html);

    expect((string)$value)->toBe('&lt;b onclick=&quot;func()&quot;&gt;boiler&lt;/b&gt;');
    expect($value->raw())->toBe($html);
    expect($value->clean())->toBe('<b>boiler</b>');
    expect($value->empty())->toBe(false);

    $value = new Value('');

    expect($value->empty())->toBe(true);
});


test('Stringable value', function () {
    $stringable = new class()
    {
        public string $value = 'test';

        public function __toString(): string
        {
            return '<b>boiler</b>';
        }

        public function testMethod(): string
        {
            return $this->value . $this->value;
        }
    };
    $value = new Value($stringable);

    expect((string)$value)->toBe('&lt;b&gt;boiler&lt;/b&gt;');
    expect($value->raw())->toBe($stringable);
    expect($value->value)->toBeInstanceOf(Value::class);
    expect((string)$value->value)->toBe('test');
    $value->value = 'boiler';
    expect((string)$value->value)->toBe('boiler');
    expect($value->testMethod())->toBeInstanceOf(Value::class);
    expect((string)$value->testMethod())->toBe('boilerboiler');
});


test('Stringable value :: getter throws', function () {
    $stringable = new class()
    {
        public function __toString(): string
        {
            return '';
        }
    };
    $value = new Value($stringable);
    $value->test;
})->throws(NoSuchProperty::class);


test('Stringable value :: setter throws', function () {
    $stringable = new class()
    {
        public function __toString(): string
        {
            return '';
        }

        public function __set(string $n, mixed $v): void
        {
            if ($n && $v === null) throw new ValueError();
        }
    };
    $value = new Value($stringable);
    $value->test = null;
})->throws(NoSuchProperty::class);


test('Stringable value :: method call throws', function () {
    $stringable = new class()
    {
        public function __toString(): string
        {
            return '';
        }
    };
    $value = new Value($stringable);
    $value->test();
})->throws(NoSuchMethod::class);

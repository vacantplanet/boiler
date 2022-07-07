<?php

declare(strict_types=1);

use Conia\Boiler\Value;
use Conia\Boiler\Error\{NoSuchMethod, NoSuchProperty};


test('String', function () {
    $html = '<b onclick="func()">boiler</b>';
    $value = new Value($html);

    expect((string)$value)->toBe('&lt;b onclick=&quot;func()&quot;&gt;boiler&lt;/b&gt;');
    expect($value->raw())->toBe($html);
    expect($value->clean())->toBe('<b>boiler</b>');
    expect($value->empty())->toBe(false);

    $value = new Value('');

    expect($value->empty())->toBe(true);
});


test('Stringable', function () {
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


test('Object :: valid', function () {
    $object = new class()
    {
        public function html(): string
        {
            return '<b>boiler</b><script></script>';
        }

        public function __invoke(string $s): string
        {
            return '<i>' . $s . '</i>';
        }
    };
    $value = new Value($object);

    expect((string)$value->html())->toBe('&lt;b&gt;boiler&lt;/b&gt;&lt;script&gt;&lt;/script&gt;');
    expect($value->html()->clean())->toBe('<b>boiler</b>');
    expect((string)$value('test'))->toBe('&lt;i&gt;test&lt;/i&gt;');
});


test('Object :: not invokable', function () {
    $object = new class()
    {
    };
    $value = new Value($object);

    $value();
})->throws(NoSuchMethod::class, 'not callable');


test('Closure', function () {
    $closure = function (): string {
        return '<b>boiler</b><script></script>';
    };
    $value = new Value($closure);

    expect((string)$value())->toBe('&lt;b&gt;boiler&lt;/b&gt;&lt;script&gt;&lt;/script&gt;');
    expect($value()->clean())->toBe('<b>boiler</b>');
});


test('Getter throws 1', function () {
    $value = new Value('test');
    $value->test;
})->throws(NoSuchProperty::class);


test('Getter throws 2', function () {
    $obj = new class()
    {
    };
    $value = new Value($obj);
    $value->test;
})->throws(NoSuchProperty::class);


test('Setter throws 1', function () {
    $value = new Value('test');
    $value->test = null;
})->throws(NoSuchProperty::class);


test('Setter throws 2', function () {
    $obj = new class()
    {
        public function __set(string $n, mixed $v): void
        {
            if ($n && $v === null) {
                throw new ValueError();
            }
        }
    };
    $value = new Value($obj);
    $value->test = null;
})->throws(NoSuchProperty::class);


test('Method call throws', function () {
    $value = new Value('test');
    $value->test();
})->throws(NoSuchMethod::class);

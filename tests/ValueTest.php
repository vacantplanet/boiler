<?php

declare(strict_types=1);

use Conia\Boiler\Value;
use Conia\Boiler\Error\{NoSuchMethod, NoSuchProperty};


test('Value::unwrap', function () {
    expect((new Value('<b>boiler</b>'))->unwrap())->toBe('<b>boiler</b>');
});


test('Value::clean', function () {
    expect((new Value('<b onclick="function()">boiler</b>'))->clean())->toBe('<b>boiler</b>');
});


test('Value::empty', function () {
    expect((new Value(''))->empty())->toBe(true);
    expect((new Value('test'))->empty())->toBe(false);
    expect((new Value(null))->empty())->toBe(true);
});


test('String', function () {
    $html = '<b onclick="func()">boiler</b>';
    $value = new Value($html);

    expect((string)$value)->toBe('&lt;b onclick=&quot;func()&quot;&gt;boiler&lt;/b&gt;');
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
    expect($value->unwrap())->toBe($stringable);
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

<?php

declare(strict_types=1);

namespace VacantPlanet\Boiler\Tests;

use PHPUnit\Framework\Attributes\TestDox;
use VacantPlanet\Boiler\Exception\RuntimeException;
use VacantPlanet\Boiler\Proxy\Proxy;
use ValueError;

final class ProxyTest extends TestCase
{
	public function testProxyUnwrap(): void
	{
		$this->assertSame('<b>boiler</b>', (new Proxy('<b>boiler</b>'))->unwrap());
	}

	public function testProxyStrip(): void
	{
		$this->assertSame('boiler<br>plate', (new Proxy('<b>boiler<br>plate</b>'))->strip('<br>'));
		$this->assertSame('boiler<br>plate', (new Proxy('<b>boiler<br>plate</b>'))->strip(['br']));
		$this->assertSame('boiler<br>plate', (new Proxy('<b>boiler<br>plate</b>'))->strip(['<br>']));
		$this->assertSame('boilerplate', (new Proxy('<b>boiler<br>plate</b>'))->strip(null));
		$this->assertSame('boilerplate', (new Proxy('<b>boiler<br>plate</b>'))->strip());
	}

	public function testProxyClean(): void
	{
		$this->assertSame('<b>boiler</b>', (new Proxy('<b onclick="function()">boiler</b>'))->clean());
	}

	public function testProxyEmpty(): void
	{
		$this->assertSame(true, (new Proxy(''))->empty());
		$this->assertSame(false, (new Proxy('test'))->empty());
		$this->assertSame(true, (new Proxy(null))->empty());
	}

	public function testStringValue(): void
	{
		$html = '<b onclick="func()">boiler</b>';
		$value = new Proxy($html);

		$this->assertSame('&lt;b onclick=&quot;func()&quot;&gt;boiler&lt;/b&gt;', (string) $value);
	}

	public function testStringableValue(): void
	{
		$stringable = new class {
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
		$value = new Proxy($stringable);

		$this->assertSame('&lt;b&gt;boiler&lt;/b&gt;', (string) $value);
		$this->assertSame($stringable, $value->unwrap());
		$this->assertInstanceOf(Proxy::class, $value->value);
		$this->assertSame('test', (string) $value->value);
		$value->value = 'boiler';
		$this->assertSame('boiler', (string) $value->value);
		$this->assertInstanceOf(Proxy::class, $value->testMethod());
		$this->assertSame('boilerboiler', (string) $value->testMethod());
	}

	public function testObjectValid(): void
	{
		$object = new class {
			public function __invoke(string $s): string
			{
				return '<i>' . $s . '</i>';
			}

			public function html(): string
			{
				return '<b>boiler</b><script></script>';
			}
		};
		$value = new Proxy($object);

		$this->assertSame('&lt;b&gt;boiler&lt;/b&gt;&lt;script&gt;&lt;/script&gt;', (string) $value->html());
		$this->assertSame('<b>boiler</b>', $value->html()->clean());
		$this->assertSame('&lt;i&gt;test&lt;/i&gt;', (string) $value('test'));
	}

	public function testObjectNotInvokable(): void
	{
		$this->throws(RuntimeException::class, 'No such method');

		$object = new class {};
		$value = new Proxy($object);

		$value();
	}

	public function testClosureValue(): void
	{
		$closure = function (): string {
			return '<b>boiler</b><script></script>';
		};
		$value = new Proxy($closure);

		$this->assertSame('&lt;b&gt;boiler&lt;/b&gt;&lt;script&gt;&lt;/script&gt;', (string) $value());
		$this->assertSame('<b>boiler</b>', $value()->clean());
	}

	#[TestDox('Getter throws I')]
	public function testGetterThrowsI(): void
	{
		$this->throws(RuntimeException::class, 'No such property');

		$value = new Proxy('test');
		$value->test;
	}

	#[TestDox('Getter throws II')]
	public function testGetterThrowsII(): void
	{
		$this->throws(RuntimeException::class, 'No such property');

		$obj = new class {};
		$value = new Proxy($obj);
		$value->test;
	}

	#[TestDox('Setter throws I')]
	public function testSetterThrowsI(): void
	{
		$this->throws(RuntimeException::class, 'No such property');

		$value = new Proxy('test');
		$value->test = null;
	}

	#[TestDox('Setter throws II')]
	public function testSetterThrowsII(): void
	{
		$this->throws(RuntimeException::class, 'No such property');

		$obj = new class {
			public function __set(string $n, mixed $v): void
			{
				if ($n && $v === null) {
					throw new ValueError();
				}
			}
		};
		$value = new Proxy($obj);
		$value->test = null;
	}

	public function testMethodCallThrows(): void
	{
		$this->throws(RuntimeException::class, 'No such method');

		$value = new Proxy('test');
		$value->test();
	}
}

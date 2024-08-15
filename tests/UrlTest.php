<?php

declare(strict_types=1);

namespace VacantPlanet\Boiler\Tests;

use VacantPlanet\Boiler\Exception\RuntimeException;
use VacantPlanet\Boiler\Url;

final class UrlTest extends TestCase
{
	public function testAllSegments(): void
	{
		$url = Url::clean(
			"http://user @ example. com :pss@example.com:81/mypath/   " .
			" myfile.html?a=\"chuck schuldiner\"&b[]=2&b[]\n\n\n=3&z=666#symbolic",
		);

		$this->assertSame(
			'http://user%20@%20example.%20com%20%3Apss@example.com:81/mypath/++++myfile.html' .
				'?a=%22chuck+schuldiner%22&b%5B0%5D=2&b%5B1%5D=3&z=666#symbolic',
			$url,
		);
	}

	public function testAlreadyCleanWithoutAnything(): void
	{
		$url = Url::clean('http://example.com/');

		$this->assertSame('http://example.com/', $url);
	}

	public function testFailedParsing(): void
	{
		$this->throws(RuntimeException::class, 'Invalid Url');

		Url::clean('scheme://example_login:!#Password?@ZZZ@127.0.0.1/some_path');
	}
}

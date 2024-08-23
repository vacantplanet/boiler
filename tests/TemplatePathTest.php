<?php

declare(strict_types=1);

namespace VacantPlanet\Boiler\Tests;

use PHPUnit\Framework\Attributes\TestDox;
use VacantPlanet\Boiler\Exception\LookupException;
use VacantPlanet\Boiler\TemplatePath;

final class TemplatePathTest extends TestCase
{
	public function testInitialization(): void
	{
		$tp = new TemplatePath($this::DEFAULT_DIR, 'simple');

		$this->assertSame(true, $tp->isValid());
		$this->assertStringEndsWith('tests/templates/default/simple.php', $tp->path());
		$this->assertSame('', $tp->error());
	}

	#[TestDox('Initialization with PHP extension')]
	public function testInitializationWithPhpExtension(): void
	{
		$tp = new TemplatePath($this::DEFAULT_DIR, 'simple.php');

		$this->assertSame(true, $tp->isValid());
		$this->assertStringEndsWith('tests/templates/default/simple.php', $tp->path());
		$this->assertSame('', $tp->error());
	}

	public function testInitializationWithCustomExtension(): void
	{
		$tp = new TemplatePath($this::DEFAULT_DIR, 'extension.tpl');

		$this->assertSame(true, $tp->isValid());
		$this->assertStringEndsWith('tests/templates/default/extension.tpl', $tp->path());
		$this->assertSame('', $tp->error());
	}

	public function testFailingInitializationEmptyDirectoryString(): void
	{
		$tp = new TemplatePath('', 'simple');

		$this->assertSame(false, $tp->isValid());
		$this->assertSame('Template directory must not be an empty string', $tp->error());
	}

	public function testFailingInitializationNonExistentDirectory(): void
	{
		$tp = new TemplatePath('doesnotexist', 'simple');

		$this->assertSame(false, $tp->isValid());
		$this->assertSame("Template directory not found: 'doesnotexist'", $tp->error());
	}

	public function testFailingOutsideOfTemplateDirectory(): void
	{
		$tp = new TemplatePath($this::DEFAULT_DIR, '../unreachable');

		$this->assertSame(false, $tp->isValid());
		$this->assertStringStartsWith('Template resides outside of root directory', $tp->error());
	}

	public function testErrorWhenAccessingInvalidPath(): void
	{
		$this->throws(LookupException::class, 'Error while accessing path');

		$tp = new TemplatePath('', 'simple');
		$tp->path();
	}
}

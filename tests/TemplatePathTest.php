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
		$tplp = new TemplatePath($this::DEFAULT_DIR, 'simple');

		$this->assertSame(true, $tplp->isValid());
		$this->assertStringEndsWith('tests/templates/default/simple.php', $tplp->path());
		$this->assertSame('', $tplp->error());
	}

	#[TestDox('Initialization with PHP extension')]
	public function testInitializationWithPhpExtension(): void
	{
		$tplp = new TemplatePath($this::DEFAULT_DIR, 'simple.php');

		$this->assertSame(true, $tplp->isValid());
		$this->assertStringEndsWith('tests/templates/default/simple.php', $tplp->path());
		$this->assertSame('', $tplp->error());
	}

	public function testInitializationWithCustomExtension(): void
	{
		$tplp = new TemplatePath($this::DEFAULT_DIR, 'extension.tpl');

		$this->assertSame(true, $tplp->isValid());
		$this->assertStringEndsWith('tests/templates/default/extension.tpl', $tplp->path());
		$this->assertSame('', $tplp->error());
	}

	public function testFailingInitializationEmptyDirectoryString(): void
	{
		$tplp = new TemplatePath('', 'simple');

		$this->assertSame(false, $tplp->isValid());
		$this->assertSame('Template directory must not be an empty string', $tplp->error());
	}

	public function testFailingInitializationNonExistentDirectory(): void
	{
		$tplp = new TemplatePath('doesnotexist', 'simple');

		$this->assertSame(false, $tplp->isValid());
		$this->assertSame("Template directory not found: 'doesnotexist'", $tplp->error());
	}

	public function testFailingOutsideOfTemplateDirectory(): void
	{
		$tplp = new TemplatePath($this::DEFAULT_DIR, '../unreachable');

		$this->assertSame(false, $tplp->isValid());
		$this->assertStringStartsWith('Template resides outside of root directory', $tplp->error());
	}

	public function testErrorWhenAccessingInvalidPath(): void
	{
		$this->throws(LookupException::class, 'Error while accessing path');

		$tplp = new TemplatePath('', 'simple');
		$tplp->path();
	}
}

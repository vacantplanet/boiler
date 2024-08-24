<?php

declare(strict_types=1);

namespace VacantPlanet\Boiler\Tests;

use VacantPlanet\Boiler\Proxy\Proxy;
use VacantPlanet\Boiler\Template;
use VacantPlanet\Boiler\TemplateContext;
use VacantPlanet\Boiler\Tests\TestCase;

final class TemplateContextTest extends TestCase
{
	private string $templates;
	private Template $template;

	protected function setUp(): void
	{
		$this->templates = __DIR__ . '/templates/default/';
		$path = $this->templates . 'simple.php';
		$this->template = new Template($path);
	}

	public function testGetContext(): void
	{
		$tmplContext = new TemplateContext($this->template, [
			'value1' => 'Value 1', 'value2' => '<i>Value 2</i>', 'value3' => 3,
		], [], true);
		$context = $tmplContext->context();

		$this->assertInstanceOf(Proxy::class, $context['value1']);
		$this->assertSame('Value 1', (string) $context['value1']);
		$this->assertInstanceOf(Proxy::class, $context['value2']);
		$this->assertSame('&lt;i&gt;Value 2&lt;/i&gt;', (string) $context['value2']);
		$this->assertSame(3, $context['value3']);
	}

	public function testAddingToContext(): void
	{
		$tmplContext = new TemplateContext($this->template, ['value1' => 'Value 1'], [], true);
		$value2 = $tmplContext->add('value2', '<i>Value 2</i>');
		$context = $tmplContext->context();

		$this->assertInstanceOf(Proxy::class, $context['value1']);
		$this->assertSame('Value 1', (string) $context['value1']);
		$this->assertInstanceOf(Proxy::class, $context['value2']);
		$this->assertSame('&lt;i&gt;Value 2&lt;/i&gt;', (string) $context['value2']);
		$this->assertInstanceOf(Proxy::class, $value2);
		$this->assertSame('&lt;i&gt;Value 2&lt;/i&gt;', (string) $value2);
	}
}

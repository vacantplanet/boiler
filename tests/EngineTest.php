<?php

declare(strict_types=1);

namespace VacantPlanet\Boiler\Tests;

use ParseError;
use PHPUnit\Framework\Attributes\TestDox;
use VacantPlanet\Boiler\Engine;
use VacantPlanet\Boiler\Exception\LogicException;
use VacantPlanet\Boiler\Exception\LookupException;
use VacantPlanet\Boiler\Exception\RuntimeException;
use VacantPlanet\Boiler\Exception\UnexpectedValueException;
use VacantPlanet\Boiler\Proxy\Proxy;
use VacantPlanet\Boiler\Tests\TestCase;

final class EngineTest extends TestCase
{
	#[TestDox('Directory does not exist I')]
	public function testDirectoryDoesNotExistI(): void
	{
		$this->throws(LookupException::class, 'doesnotexist');

		new Engine('./doesnotexist');
	}

	#[TestDox('Directory does not exist II')]
	public function testDirectoryDoesNotExistII(): void
	{
		$this->throws(LookupException::class, 'doesnotexist');

		new Engine([TestCase::DEFAULT_DIR, './doesnotexist']);
	}

	public function testSimpleRendering(): void
	{
		$engine = new Engine(TestCase::DEFAULT_DIR, ['obj' => $this->obj()]);

		$this->assertSame(
			'<h1>boiler</h1><p>rocks</p>',
			$this->fullTrim($engine->render('simple', ['text' => 'rocks'])),
		);
	}

	public function testSimpleScalarValueRendering(): void
	{
		$engine = new Engine(TestCase::DEFAULT_DIR, ['obj' => $this->obj()]);

		$this->assertSame(
			'<p>13</p><p>1</p><p>13.73</p><p></p><p>&lt;script&gt;&lt;/script&gt;</p>',
			$this->fullTrim($engine->render('scalar', [
				'int' => 13,
				'float' => 13.73,
				'null' => null,
				'bool' => true,
				'string' => '<script></script>',
			])),
		);
	}

	public function testSimpleRenderingNamespaced(): void
	{
		$engine = new Engine($this->namespaced(), ['obj' => $this->obj()]);

		$this->assertSame(
			'<h1>boiler</h1><p>rocks</p>',
			$this->fullTrim($engine->render('namespace:simple', ['text' => 'rocks'])),
		);
	}

	public function testExtensionGiven(): void
	{
		$engine = new Engine(self::DEFAULT_DIR, ['obj' => $this->obj()]);

		$this->assertSame('<p></p>', $this->fullTrim($engine->render('extension.tpl')));
	}

	public function testUnwrapRendering(): void
	{
		$engine = new Engine(self::DEFAULT_DIR);

		$this->assertSame(
			'&lt;b&gt;boiler&lt;/b&gt;<b>boiler</b>',
			$engine->render('unwrap', ['html' => '<b>boiler</b>']),
		);
	}

	public function testSwitchOffAutoescapingByDefault(): void
	{
		$engine = new Engine(self::DEFAULT_DIR, autoescape: false);

		$this->assertSame(
			'<b>noautoescape</b>',
			$engine->render('noautoescape', ['html' => '<b>noautoescape</b>']),
		);
	}

	public function testSwitchOffAutoescapingWhenCallingRender(): void
	{
		$engine = new Engine(self::DEFAULT_DIR, autoescape: true);

		$this->assertSame(
			'<b>nodefaultautoescape</b>',
			$engine->render(
				'noautoescape',
				['html' => '<b>nodefaultautoescape</b>'],
				autoescape: false,
			),
		);
	}

	public function testUnwrapRenderingWithStringable(): void
	{
		$engine = new Engine($this->templates());

		$this->assertSame('&lt;b&gt;boiler&lt;/b&gt;<b>boiler</b>', $engine->render(
			'unwrap',
			['html' => new class {
				public function __toString(): string
				{
					return '<b>boiler</b>';
				}
			}],
		));
	}

	public function testRenderingWithStringable(): void
	{
		$engine = new Engine($this->templates());
		$stringable = new class {
			public string $test = 'test';

			public function __toString(): string
			{
				return '<b>boiler</b>';
			}

			public function testMethod(string $value): string
			{
				return $value . $value;
			}
		};

		$this->assertSame(
			'&lt;b&gt;boiler&lt;/b&gt;<b>boiler</b>testmantasmantas',
			$this->fullTrim(
				$engine->render('stringable', ['html' => $stringable]),
			),
		);
	}

	public function testCleanRendering(): void
	{
		$engine = new Engine($this->templates());

		$this->assertSame(
			'<b>boiler</b>',
			$engine->render(
				'clean',
				['html' => '<script src="/evil.js"></script><b>boiler</b>'],
			),
		);
	}

	public function testArrayRendering(): void
	{
		$engine = new Engine($this->templates());

		$this->assertSame(
			'&lt;b&gt;1&lt;/b&gt;&lt;b&gt;2&lt;/b&gt;&lt;b&gt;3&lt;/b&gt;',
			trim($engine->render(
				'iter',
				['arr' => ['<b>1</b>', '<b>2</b>', '<b>3</b>']],
			)),
		);
	}

	public function testHelperFunctionRendering(): void
	{
		$engine = new Engine($this->templates(), ['obj' => $this->obj()]);

		$this->assertSame(
			'&lt;script&gt;&lt;script&gt;<b>clean</b>',
			$this->fullTrim($engine->render('helper')),
		);
	}

	public function testEmptyHelperMethod(): void
	{
		$engine = new Engine($this->templates());

		$this->assertSame(
			'&lt;b&gt;not empty&lt;/b&gt;',
			$this->fullTrim($engine->render(
				'empty',
				['empty' => '', 'notempty' => '<b>not empty</b>'],
			)),
		);
	}

	public function testEscapeAlreadyWrappedProxy(): void
	{
		$engine = new Engine($this->templates());

		$this->assertSame(
			'<p>&lt;b&gt;wrapped&lt;/b&gt;</p>',
			$this->fullTrim($engine->render(
				'escapevalue',
				['wrapped' => '<b>wrapped</b>'],
			)),
		);
	}

	public function testIteratorRendering(): void
	{
		$engine = new Engine($this->templates());

		$iter = function () {
			$a = ['<b>2</b>', '<b>3</b>', '<b>4</b>'];

			foreach ($a as $i) {
				yield $i;
			}
		};

		$this->assertSame(
			'&lt;b&gt;2&lt;/b&gt;&lt;b&gt;3&lt;/b&gt;&lt;b&gt;4&lt;/b&gt;',
			trim($engine->render(
				'iter',
				['arr' => $iter()],
			)),
		);
	}

	public function testComplexNestedRendering(): void
	{
		$engine = new Engine(
			$this->templates(),
			['obj' => $this->obj()],
		);

		$iter = function () {
			$a = [13.73, 'String II', 1];

			foreach ($a as $i) {
				yield $i;
			}
		};

		$context = [
			'title' => 'Boiler App',
			'headline' => 'Boiler App',
			'array' => [
				'<b>sanitize</b>' => [
					1, 'String', new class {
						public function __toString(): string
						{
							return '<p>Object</p>';
						}
					},
				],
				666 => $iter(),
			],
			'html' => '<p>HTML</p>',
		];
		$result = $this->fullTrim($engine->render('complex', $context));
		$compare = '<!DOCTYPE html><html lang="en"><head><title>Boiler App</title>' .
			'<meta name="keywords" content="boiler"></head><body>' .
			'<h1>Boiler App</h1><table><tr><td>&lt;b&gt;sanitize&lt;/b&gt;</td><td>1</td><td>String</td>' .
			'<td>&lt;p&gt;Object&lt;/p&gt;</td></tr><tr><td>666</td><td>13.73</td><td>String II</td>' .
			'<td>1</td></tr></table><p>HTML</p></body></html>';

		$this->assertSame($compare, $result);
	}

	public function testSingleLayout(): void
	{
		$engine = new Engine($this->templates());

		$this->assertSame(
			'<body><p>boiler</p><p>boiler</p></body>',
			$this->fullTrim($engine->render(
				'uselayout',
				['text' => 'boiler'],
			)),
		);
	}

	public function testNonExistentLayoutWithoutExtension(): void
	{
		$this->throws(LookupException::class, 'doesnotexist');

		$engine = new Engine($this->templates());

		$engine->render('nonexistentlayout');
	}

	public function testNonExistentLayoutWithExtension(): void
	{
		$this->throws(LookupException::class, 'doesnotexist');

		$engine = new Engine($this->templates());

		$engine->render('nonexistentlayoutext');
	}

	public function testStackedLayout(): void
	{
		$engine = new Engine($this->templates());

		$this->assertSame(
			'<body><div class="stackedsecond"><div class="stackedfirst">' .
				'<p>boiler</p></div></div><p>boiler</p></body>',
			$this->fullTrim($engine->render(
				'usestacked',
				['text' => 'boiler'],
			)),
		);
	}

	public function testMultilpleLayoutsError(): void
	{
		$this->throws(RuntimeException::class, 'layout already set');

		(new Engine($this->templates()))->render('multilayout');
	}

	public function testSectionRendering(): void
	{
		$engine = new Engine($this->templates());

		$this->assertSame(
			'<div><p>boiler</p>boiler</div><ul><li>boiler</li></ul>',
			$this->fullTrim($engine->render('addsection', ['text' => 'boiler'])),
		);
	}

	public function testRenderSectionWithDefaultValue(): void
	{
		$engine = new Engine($this->templates());

		$this->assertSame(
			'<p>default value</p>',
			$this->fullTrim($engine->render('addsectiondefault', [])),
		);
	}

	public function testAppendPrependToSection(): void
	{
		$engine = new Engine($this->templates());

		$this->assertSame(
			'<script src="/prepend.js"></script>' .
				'<script src="/assign.js"></script>' .
				'<script src="/append.js"></script>',
			$this->fullTrim($engine->render('appendprepend', ['path' => '/assign.js'])),
		);
	}

	public function testAppendPrependToSectionWithDefaultValueAndOrder(): void
	{
		$engine = new Engine($this->templates());

		$this->assertSame(
			'<prepend-first><prepend><default><append><append-last>',
			$this->fullTrim($engine->render(
				'appendprependdefault',
				['path' => '/assign.js'],
			)),
		);
	}

	public function testNestedSectionsError(): void
	{
		$this->throws(LogicException::class);

		$engine = new Engine($this->templates());

		$engine->render('nestedsections');
	}

	public function testClosingUnopenedSectionError(): void
	{
		$this->throws(LogicException::class);

		$engine = new Engine($this->templates());

		$engine->render('closeunopened');
	}

	public function testMissingSectionRendering(): void
	{
		$engine = new Engine($this->templates());

		$this->assertSame(
			'<div><p>boiler</p>boiler</div><p>no list</p>',
			$this->fullTrim($engine->render('nosection', ['text' => 'boiler'])),
		);
	}

	public function testInsertRendering(): void
	{
		$engine = new Engine($this->templates());

		$this->assertSame(
			'<p>Boiler</p><p>73</p><p>Boiler</p><p>23</p><p>Overwrite</p><p>13</p>',
			$this->fullTrim($engine->render('insert', ['text' => 'Boiler', 'int' => 73])),
		);
	}

	public function testTemplateInSubDirectory(): void
	{
		$engine = new Engine($this->templates());

		$this->assertSame(
			'<h2>Boiler</h2>',
			$this->fullTrim($engine->render('sub/home', ['text' => 'Boiler'])),
		);
	}

	public function testAdditionalTemplateDirectories(): void
	{
		$engine = new Engine(
			$this->templates($this->additional()),
			['obj' => $this->obj()],
		);

		$this->assertSame(
			'<h1>boiler</h1><p>rocks</p>',
			$this->fullTrim($engine->render('simple', ['text' => 'rocks'])),
		);
		$this->assertSame(
			'<span>Additional</span>',
			$this->fullTrim($engine->render('additional', ['text' => 'Additional'])),
		);
	}

	public function testAdditionalTemplateDirectoriesNamespaced(): void
	{
		$engine = new Engine($this->namespaced($this->additional()));

		$this->assertSame(
			'<h2>Boiler</h2>',
			$this->fullTrim($engine->render('namespace:sub/home', ['text' => 'Boiler'])),
		);
		$this->assertSame(
			'<span>Additional</span>',
			$this->fullTrim($engine->render('additional:additional', ['text' => 'Additional'])),
		);
	}

	public function testAdditionalTemplateDirectoriesShadowing(): void
	{
		$engine = new Engine($this->namespaced());

		$this->assertSame(
			'<h2>Boiler</h2>',
			$this->fullTrim($engine->render('sub/home', ['text' => 'Boiler'])),
		);

		$engine = new Engine($this->namespaced($this->additional()));

		$this->assertSame(
			'<h1>Sub Boiler</h1>',
			$this->fullTrim($engine->render('sub/home', ['text' => 'Boiler'])),
		);
		$this->assertSame(
			'<h2>Boiler</h2>',
			$this->fullTrim($engine->render('namespace:sub/home', ['text' => 'Boiler'])),
		);
		$this->assertSame(
			'<h1>Sub Boiler</h1>',
			$this->fullTrim($engine->render('additional:sub/home', ['text' => 'Boiler'])),
		);
	}

	public function testExistsHelper(): void
	{
		$engine = new Engine($this->templates());

		$this->assertSame(true, $engine->exists('simple'));
		$this->assertSame(false, $engine->exists('wrongindex'));
	}

	#[TestDox('Config error wrong template format I')]
	public function testConfigErrorWrongTemplateFormatI(): void
	{
		$this->throws(LookupException::class, 'Invalid template format');

		$engine = new Engine($this->templates());

		$engine->render('default:sub:index');
	}

	#[TestDox('Config error wrong template format II')]
	public function testConfigErrorWrongTemplateFormatII(): void
	{
		$this->throws(LookupException::class, 'Invalid template format');

		$engine = new Engine($this->templates());

		$engine->render(':default.php');
	}

	#[TestDox('Config error wrong template format III')]
	public function testConfigErrorWrongTemplateFormatIII(): void
	{
		$this->throws(LookupException::class, 'Invalid template format');

		$engine = new Engine($this->templates());

		$engine->render('default.php:');
	}

	#[TestDox('Config error wrong template format IV')]
	public function testConfigErrorWrongTemplateFormatIV(): void
	{
		$this->throws(UnexpectedValueException::class, 'invalid or empty');

		$engine = new Engine($this->templates());

		$engine->render('');
	}

	#[TestDox('Config error wrong template format V')]
	public function testConfigErrorWrongTemplateFormatV(): void
	{
		$this->throws(UnexpectedValueException::class, 'invalid or empty');

		$engine = new Engine($this->templates());

		$engine->render("\0");
	}

	public function testRenderErrorMissingTemplate(): void
	{
		$this->throws(LookupException::class, 'not found');

		$engine = new Engine($this->templates());

		$engine->render('nonexistent');
	}

	public function testNamespaceDoesNotExist(): void
	{
		$this->throws(LookupException::class, 'Template namespace');

		$engine = new Engine($this->namespaced());

		$engine->render('doesnotexist:sub/home');
	}

	#[TestDox('Render error template outside root directory I')]
	public function testRenderErrorTemplateOutsideRootDirectoryI(): void
	{
		$this->throws(LookupException::class, 'not found');

		$engine = new Engine($this->templates());

		$engine->render('.././../.././../etc/passwd');
	}

	#[TestDox('Render error template outside root directory II')]
	public function testRenderErrorTemplateOutsideRootDirectoryII(): void
	{
		$this->throws(LookupException::class, 'outside');

		$engine = new Engine($this->templates());

		$engine->render('../unreachable');
	}

	public function testRenderErrorParseError(): void
	{
		$this->throws(ParseError::class);

		$engine = new Engine($this->templates());

		$engine->render('failing');
	}

	public function testCustomTemplateMethod(): void
	{
		$engine = new Engine($this->templates());
		$engine->registerMethod('upper', function (Proxy $value): Proxy {
			return new Proxy(strtoupper($value->unwrap()));
		});

		$this->assertSame(
			'<h2>BOILER</h2>',
			$this->fullTrim($engine->render('method', ['text' => 'Boiler'])),
		);
	}

	public function testUnknownCustomMethod(): void
	{
		$this->throws(UnexpectedValueException::class, 'upper');

		$engine = new Engine($this->templates());

		$engine->render('unknownmethod');
	}
}

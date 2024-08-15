<?php

declare(strict_types=1);

namespace VacantPlanet\Boiler\Tests;

use ParseError;
use VacantPlanet\Boiler\Engine;
use VacantPlanet\Boiler\Exception\LogicException;
use VacantPlanet\Boiler\Exception\LookupException;
use VacantPlanet\Boiler\Exception\RuntimeException;
use VacantPlanet\Boiler\Exception\UnexpectedValueException;
use VacantPlanet\Boiler\Proxy\Proxy;
use VacantPlanet\Boiler\Tests\TestCase;

final class EngineTest extends TestCase
{
	public function testDirectoryDoesNotExistI(): void
	{
		$this->throws(LookupException::class, 'doesnotexist');

		new Engine('./doesnotexist');
	}

	public function testDirectoryDoesNotExistII(): void
	{
		$this->throws(LookupException::class, 'doesnotexist');

		new Engine([TestCase::DEFAULT_DIR, './doesnotexist']);
	}

	public function testSimpleRendering(): void
	{
		$engine = new Engine(TestCase::DEFAULT_DIR, ['obj' => $this->obj()]);

		expect(
			$this->fullTrim($engine->render('simple', ['text' => 'rocks'])),
		)->toBe('<h1>boiler</h1><p>rocks</p>');
	}

	public function testSimpleScalarValueRendering(): void
	{
		$engine = new Engine(TestCase::DEFAULT_DIR, ['obj' => $this->obj()]);

		expect(
			$this->fullTrim($engine->render('scalar', [
				'int' => 13,
				'float' => 13.73,
				'null' => null,
				'bool' => true,
				'string' => '<script></script>',
			])),
		)->toBe('<p>13</p><p>1</p><p>13.73</p><p></p><p>&lt;script&gt;&lt;/script&gt;</p>');
	}

	public function testSimpleRenderingNamespaced(): void
	{
		$engine = new Engine($this->namespaced(), ['obj' => $this->obj()]);

		expect(
			$this->fullTrim($engine->render('namespace:simple', ['text' => 'rocks'])),
		)->toBe('<h1>boiler</h1><p>rocks</p>');
	}

	public function testExtensionGiven(): void
	{
		$engine = new Engine(self::DEFAULT_DIR, ['obj' => $this->obj()]);

		expect($this->fullTrim($engine->render('extension.tpl')))->toBe('<p></p>');
	}

	public function testUnwrapRendering(): void
	{
		$engine = new Engine(self::DEFAULT_DIR);

		expect($engine->render('unwrap', [
			'html' => '<b>boiler</b>',
		]))->toBe('&lt;b&gt;boiler&lt;/b&gt;<b>boiler</b>');
	}

	public function testSwitchOffAutoescapingByDefault(): void
	{
		$engine = new Engine(self::DEFAULT_DIR, autoescape: false);

		expect($engine->render('noautoescape', [
			'html' => '<b>noautoescape</b>',
		]))->toBe('<b>noautoescape</b>');
	}

	public function testSwitchOffAutoescapingWhenCallingRender(): void
	{
		$engine = new Engine(self::DEFAULT_DIR, autoescape: true);

		expect($engine->render(
			'noautoescape',
			['html' => '<b>nodefaultautoescape</b>'],
			autoescape: false,
		))->toBe('<b>nodefaultautoescape</b>');
	}

	public function testUnwrapRenderingWithStringable(): void
	{
		$engine = new Engine($this->templates());

		expect($engine->render('unwrap', [
			'html' => new class {
				public function __toString(): string
				{
					return '<b>boiler</b>';
				}
			},
		]))->toBe('&lt;b&gt;boiler&lt;/b&gt;<b>boiler</b>');
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

		expect($this->fullTrim($engine->render('stringable', [
			'html' => $stringable,
		])))->toBe('&lt;b&gt;boiler&lt;/b&gt;<b>boiler</b>testmantasmantas');
	}

	public function testCleanRendering(): void
	{
		$engine = new Engine($this->templates());

		expect($engine->render('clean', [
			'html' => '<script src="/evil.js"></script><b>boiler</b>',
		]))->toBe('<b>boiler</b>');
	}

	public function testArrayRendering(): void
	{
		$engine = new Engine($this->templates());

		expect(trim($engine->render('iter', [
			'arr' => ['<b>1</b>', '<b>2</b>', '<b>3</b>'],
		])))->toBe('&lt;b&gt;1&lt;/b&gt;&lt;b&gt;2&lt;/b&gt;&lt;b&gt;3&lt;/b&gt;');
	}

	public function testHelperFunctionRendering(): void
	{
		$engine = new Engine($this->templates(), ['obj' => $this->obj()]);

		expect($this->fullTrim($engine->render('helper')))->toBe(
			'&lt;script&gt;&lt;script&gt;<b>clean</b>',
		);
	}

	public function testEmptyHelperMethod(): void
	{
		$engine = new Engine($this->templates());

		expect($this->fullTrim($engine->render('empty', [
			'empty' => '',
			'notempty' => '<b>not empty</b>',
		])))->toBe('&lt;b&gt;not empty&lt;/b&gt;');
	}

	public function testEscapeAlreadyWrappedProxy(): void
	{
		$engine = new Engine($this->templates());

		expect($this->fullTrim($engine->render('escapevalue', [
			'wrapped' => '<b>wrapped</b>',
		])))->toBe(
			'<p>&lt;b&gt;wrapped&lt;/b&gt;</p>',
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

		expect(trim($engine->render('iter', [
			'arr' => $iter(),
		])))->toBe('&lt;b&gt;2&lt;/b&gt;&lt;b&gt;3&lt;/b&gt;&lt;b&gt;4&lt;/b&gt;');
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
			'url' => 'https://example.com/boiler  /app  ',
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
		$compare = '<!DOCTYPE html><html lang="en"><head><title>Boiler App</title><link rel="stylesheet" ' .
			'href="https://example.com/boiler++/app++"><meta name="keywords" content="boiler"></head><body>' .
			'<h1>Boiler App</h1><table><tr><td>&lt;b&gt;sanitize&lt;/b&gt;</td><td>1</td><td>String</td>' .
			'<td>&lt;p&gt;Object&lt;/p&gt;</td></tr><tr><td>666</td><td>13.73</td><td>String II</td>' .
			'<td>1</td></tr></table><p>HTML</p></body></html>';

		expect($result)->toBe($compare);
	}

	public function testSingleLayout(): void
	{
		$engine = new Engine($this->templates());

		expect($this->fullTrim($engine->render('uselayout', [
			'text' => 'boiler',
		])))->toBe('<body><p>boiler</p><p>boiler</p></body>');
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

		expect($this->fullTrim($engine->render('usestacked', [
			'text' => 'boiler',
		])))->toBe(
			'<body><div class="stackedsecond"><div class="stackedfirst">' .
				'<p>boiler</p></div></div><p>boiler</p></body>',
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

		expect($this->fullTrim($engine->render('addsection', [
			'text' => 'boiler',
		])))->toBe('<div><p>boiler</p>boiler</div><ul><li>boiler</li></ul>');
	}

	public function testRenderSectionWithDefaultValue(): void
	{
		$engine = new Engine($this->templates());

		expect(
			$this->fullTrim($engine->render('addsectiondefault', [])),
		)->toBe('<p>default value</p>');
	}

	public function testAppendPrependToSection(): void
	{
		$engine = new Engine($this->templates());

		expect($this->fullTrim($engine->render('appendprepend', [
			'path' => '/assign.js',
		])))->toBe(
			'<script src="/prepend.js"></script>' .
				'<script src="/assign.js"></script>' .
				'<script src="/append.js"></script>',
		);
	}

	public function testAppendPrependToSectionWithDefaultValueAndOrder(): void
	{
		$engine = new Engine($this->templates());

		expect($this->fullTrim($engine->render('appendprependdefault', [
			'path' => '/assign.js',
		])))->toBe('<prepend-first><prepend><default><append><append-last>');
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

		expect($this->fullTrim($engine->render('nosection', [
			'text' => 'boiler',
		])))->toBe('<div><p>boiler</p>boiler</div><p>no list</p>');
	}

	public function testInsertRendering(): void
	{
		$engine = new Engine($this->templates());

		expect($this->fullTrim($engine->render('insert', [
			'text' => 'Boiler',
			'int' => 73,
		])))->toBe('<p>Boiler</p><p>73</p><p>Boiler</p><p>23</p><p>Overwrite</p><p>13</p>');
	}

	public function testTemplateInSubDirectory(): void
	{
		$engine = new Engine($this->templates());

		expect($this->fullTrim($engine->render('sub/home', [
			'text' => 'Boiler',
		])))->toBe('<h2>Boiler</h2>');
	}

	public function testAdditionalTemplateDirectories(): void
	{
		$engine = new Engine(
			$this->templates($this->additional()),
			['obj' => $this->obj()],
		);

		expect($this->fullTrim($engine->render('simple', [
			'text' => 'rocks',
		])))->toBe('<h1>boiler</h1><p>rocks</p>');
		expect($this->fullTrim($engine->render('additional', [
			'text' => 'Additional',
		])))->toBe('<span>Additional</span>');
	}

	public function testAdditionalTemplateDirectoriesNamespaced(): void
	{
		$engine = new Engine($this->namespaced($this->additional()));

		expect($this->fullTrim($engine->render('namespace:sub/home', [
			'text' => 'Boiler',
		])))->toBe('<h2>Boiler</h2>');
		expect($this->fullTrim($engine->render('additional:additional', [
			'text' => 'Additional',
		])))->toBe('<span>Additional</span>');
	}

	public function testAdditionalTemplateDirectoriesShadowing(): void
	{
		$engine = new Engine($this->namespaced());

		expect($this->fullTrim($engine->render('sub/home', [
			'text' => 'Boiler',
		])))->toBe('<h2>Boiler</h2>');

		$engine = new Engine($this->namespaced($this->additional()));

		expect($this->fullTrim($engine->render('sub/home', [
			'text' => 'Boiler',
		])))->toBe('<h1>Sub Boiler</h1>');
		expect($this->fullTrim($engine->render('namespace:sub/home', [
			'text' => 'Boiler',
		])))->toBe('<h2>Boiler</h2>');
		expect($this->fullTrim($engine->render('additional:sub/home', [
			'text' => 'Boiler',
		])))->toBe('<h1>Sub Boiler</h1>');
	}

	public function testExistsHelper(): void
	{
		$engine = new Engine($this->templates());

		expect($engine->exists('simple'))->toBe(true);
		expect($engine->exists('wrongindex'))->toBe(false);
	}

	public function testConfigErrorWrongTemplateFormatI(): void
	{
		$this->throws(LookupException::class, 'Invalid template format');

		$engine = new Engine($this->templates());

		$engine->render('default:sub:index');
	}

	public function testConfigErrorWrongTemplateFormatII(): void
	{
		$this->throws(LookupException::class, 'Invalid template format');

		$engine = new Engine($this->templates());

		$engine->render(':default.php');
	}

	public function testConfigErrorWrongTemplateFormatIII(): void
	{
		$this->throws(LookupException::class, 'Invalid template format');

		$engine = new Engine($this->templates());

		$engine->render('default.php:');
	}

	public function testConfigErrorWrongTemplateFormatIV(): void
	{
		$this->throws(UnexpectedValueException::class, 'invalid or empty');

		$engine = new Engine($this->templates());

		$engine->render('');
	}

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

	public function testRenderErrorTemplateOutsideRootDirectoryI(): void
	{
		$this->throws(LookupException::class, 'not found');

		$engine = new Engine($this->templates());

		$engine->render('.././../.././../etc/passwd');
	}

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

		expect($this->fullTrim($engine->render('method', [
			'text' => 'Boiler',
		])))->toBe('<h2>BOILER</h2>');
	}

	public function testUnknownCustomMethod(): void
	{
		$this->throws(UnexpectedValueException::class, 'upper');

		$engine = new Engine($this->templates());

		$engine->render('unknownmethod');
	}
}

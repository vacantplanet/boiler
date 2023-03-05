<?php

declare(strict_types=1);

use Conia\Boiler\Template;
use Conia\Boiler\TemplateContext;
use Conia\Boiler\Tests\TestCase;
use Conia\Boiler\Value;

uses(TestCase::class);

beforeEach(function () {
    $ds = DIRECTORY_SEPARATOR;
    $this->templates = __DIR__ . $ds . 'templates' . $ds . 'default' . $ds;
    $path = $this->templates . 'simple.php';
    $this->template = new Template($path);
});

test('Get context', function () {
    $tmplContext = new TemplateContext($this->template, [
        'value1' => 'Value 1', 'value2' => '<i>Value 2</i>', 'value3' => 3,
    ], [], true);
    $context = $tmplContext->context();

    expect($context['value1'])->toBeInstanceOf(Value::class);
    expect((string)$context['value1'])->toBe('Value 1');
    expect($context['value2'])->toBeInstanceOf(Value::class);
    expect((string)$context['value2'])->toBe('&lt;i&gt;Value 2&lt;/i&gt;');
    expect($context['value3'])->toBe(3);
});

test('Adding to context', function () {
    $tmplContext = new TemplateContext($this->template, ['value1' => 'Value 1'], [], true);
    $tmplContext->add('value2', '<i>Value 2</i>');
    $context = $tmplContext->context();

    expect($context['value1'])->toBeInstanceOf(Value::class);
    expect((string)$context['value1'])->toBe('Value 1');
    expect($context['value2'])->toBeInstanceOf(Value::class);
    expect((string)$context['value2'])->toBe('&lt;i&gt;Value 2&lt;/i&gt;');
});

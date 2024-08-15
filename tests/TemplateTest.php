<?php

declare(strict_types=1);

use VacantPlanet\Boiler\Exception\LookupException;
use VacantPlanet\Boiler\Proxy\Proxy;
use VacantPlanet\Boiler\Template;
use VacantPlanet\Boiler\Tests\TestCase;
use VacantPlanet\Boiler\Tests\WhitelistBase;
use VacantPlanet\Boiler\Tests\Whitelisted;

uses(TestCase::class);

beforeEach(function () {
    $ds = DIRECTORY_SEPARATOR;
    $this->templates = __DIR__ . $ds . 'templates' . $ds . 'default' . $ds;
});

test('Standalone rendering', function () {
    $path = $this->templates . 'simple.php';
    $template = new Template($path);

    expect($this->fullTrim($template->render([
        'obj' => $this->obj(),
        'text' => 'rocks',
    ])))->toBe('<h1>boiler</h1><p>rocks</p>');
});

test('Whitelisting', function () {
    $path = $this->templates . 'whitelist.php';
    $template = new Template($path);

    expect($this->fullTrim($template->render(
        [
            'wl' => new Whitelisted(),
            'content' => 'test',
        ],
        [Whitelisted::class],
    )))->toBe('<h1>headline</h1><p>test</p>');
});

test('Whitelisting with base class', function () {
    $path = $this->templates . 'whitelist.php';
    $template = new Template($path);

    expect($this->fullTrim($template->render(
        [
            'wl' => new Whitelisted(),
            'content' => 'test',
        ],
        [WhitelistBase::class],
    )))->toBe('<h1>headline</h1><p>test</p>');
});

test('Not whitelisted', function () {
    $path = $this->templates . 'whitelist.php';
    $template = new Template($path);

    expect($this->fullTrim($template->render([
        'wl' => new Whitelisted(),
        'content' => 'test',
    ])))->toBe('&lt;h1&gt;headline&lt;/h1&gt;&lt;p&gt;test&lt;/p&gt;');
});

test('Standalone with layout', function () {
    $path = $this->templates . 'uselayout.php';
    $template = new Template($path);

    expect($this->fullTrim($template->render([
        'text' => 'standalone',
    ])))->toBe('<body><p>standalone</p><p>standalone</p></body>');
});

test('Overwrite layout context', function () {
    $template = new Template($this->templates . 'overridelayoutcontext.php');

    expect($this->fullTrim($template->render([
        'text' => 'Boiler 1',
        'text2' => 'Boiler 2',
    ])))->toBe('<body><p>Boiler 1</p><p>Boiler 2</p><p>changed</p><p>Boiler 2</p></body>');
});

test('Non-existent layout without extension', function () {
    $template = new Template($this->templates . 'nonexistentlayout.php');

    $template->render();
})->throws(LookupException::class, 'Template not found: doesnotexist');

test('Non-existent layout with extension', function () {
    $template = new Template($this->templates . 'nonexistentlayoutext.php');

    $template->render();
})->throws(LookupException::class, 'Template not found: doesnotexist.php');

test('Custom template method', function () {
    $template = new Template($this->templates . 'method.php');
    $template->registerMethod('upper', function (Proxy $value): Proxy {
        return new Proxy(strtoupper($value->unwrap()));
    });

    expect($this->fullTrim($template->render([
        'text' => 'Boiler',
    ])))->toBe('<h2>BOILER</h2>');
});

test('Non-existent template without extension', function () {
    $template = new Template($this->templates . 'nonexistent');

    $template->render();
})->throws(LookupException::class, 'Template not found');

test('Directory not found', function () {
    $template = new Template('/__nonexistent_boiler_dir__/template.php');

    $template->render();
})->throws(LookupException::class, 'Template directory does not exist');

test('Empty path', function () {
    $template = new Template('');

    $template->render();
})->throws(LookupException::class, 'No directory given or');

test('Render error', function () {
    $template = new Template($this->templates . 'rendererror.php');

    $template->render();
})->throws(RuntimeException::class, 'Render error:');

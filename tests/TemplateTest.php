<?php

declare(strict_types=1);

use Conia\Boiler\Error\TemplateNotFound;
use Conia\Boiler\{Template, Value};
use Conia\Boiler\Tests\TestCase;


uses(TestCase::class);


beforeEach(function () {
    $ds = DIRECTORY_SEPARATOR;
    $this->templates = __DIR__ . $ds . 'templates' . $ds . 'default' . $ds;
});


test('Standalone rendering', function () {
    $path =  $this->templates . 'simple.php';
    $template = new Template($path);

    expect($this->fullTrim($template->render([
        'obj' => $this->obj(),
        'text' => 'rocks',
    ])))->toBe('<h1>boiler</h1><p>rocks</p>');
});


test('Standalone with layout', function () {
    $path =  $this->templates . 'uselayout.php';
    $template = new Template($path);

    expect($this->fullTrim($template->render([
        'text' => 'standalone'
    ])))->toBe('<body><p>standalone</p><p>standalone</p></body>');
});


test('Non-existent layout', function () {
    $template = new Template($this->templates . 'nonexistentlayout.php');

    $template->render();
})->throws(TemplateNotFound::class, 'doesnotexist');



test('Custom template method', function () {
    $template = new Template($this->templates . 'method.php');
    $template->registerMethod('upper', function (Value $value): Value {
        return new Value(strtoupper($value->unwrap()));
    });

    expect($this->fullTrim($template->render([
        'text' => 'Boiler'
    ])))->toBe('<h2>BOILER</h2>');
});

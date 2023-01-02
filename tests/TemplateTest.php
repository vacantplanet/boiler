<?php

declare(strict_types=1);

use Conia\Boiler\Error\LookupException;
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
    $template->registerMethod('upper', function (Value $value): Value {
        return new Value(strtoupper($value->unwrap()));
    });

    expect($this->fullTrim($template->render([
        'text' => 'Boiler'
    ])))->toBe('<h2>BOILER</h2>');
});


test('Overwrite layout context I', function () {
    $template = new Template($this->templates . 'overridelayoutcontext.php');

    expect($this->fullTrim($template->render([
        'text' => 'Boiler 1',
        'text2' => 'Boiler 2',
    ])))->toBe('<body><p>Boiler 1</p><p>Boiler 2</p><p>Boiler 2</p></body>');
});


test('Overwrite layout context II', function () {
    $template = new Template($this->templates . 'overridelayouterror.php');

    $template->render(['text' => 'Boiler 1', 'text2' => 'Boiler 2']);
})->throws(RuntimeException::class, 'Undefined variable');


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

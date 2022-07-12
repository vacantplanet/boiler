<?php

declare(strict_types=1);

use Conia\Boiler\Error\MethodNotAllowed;
use Conia\Boiler\{TextTemplate};
use Conia\Boiler\Tests\TestCase;


uses(TestCase::class);


test('Directory does not exist I', function () {
    $code = '<b><?= $text ?></b>';
    $template = new TextTemplate($code);

    expect($this->fullTrim($template->render([
        'text' => 'code',
    ])))->toBe('<b>code</b>');
});


test('Method not allowed :: layout', function () {
    $template = new TextTemplate('<?= $this->layout("test");');

    $template->render();
})->throws(MethodNotAllowed::class);


test('Method not allowed :: insert', function () {
    $template = new TextTemplate('<?= $this->insert("test");');

    $template->render();
})->throws(MethodNotAllowed::class);


test('Method not allowed :: begin', function () {
    $template = new TextTemplate('<?= $this->begin("test");');

    $template->render();
})->throws(MethodNotAllowed::class);


test('Method not allowed :: append', function () {
    $template = new TextTemplate('<?= $this->append("test");');

    $template->render();
})->throws(MethodNotAllowed::class);


test('Method not allowed :: prepend', function () {
    $template = new TextTemplate('<?= $this->prepend("test");');

    $template->render();
})->throws(MethodNotAllowed::class);


test('Method not allowed :: end', function () {
    $template = new TextTemplate('<?= $this->end("test");');

    $template->render();
})->throws(MethodNotAllowed::class);


test('Method not allowed :: section', function () {
    $template = new TextTemplate('<?= $this->section("test");');

    $template->render();
})->throws(MethodNotAllowed::class);


test('Method not allowed :: has', function () {
    $template = new TextTemplate('<?= $this->has("test");');

    $template->render();
})->throws(MethodNotAllowed::class);

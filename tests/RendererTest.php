<?php

declare(strict_types=1);

use Conia\Boiler\Exception\LookupException;
use Conia\Boiler\Exception\RendererException;
use Conia\Boiler\Renderer;
use Conia\Boiler\Tests\TestCase;
use Conia\Boiler\Tests\Whitelisted;
use Conia\Chuck\Config;

uses(TestCase::class);

test('Html (array of template dirs)', function () {
    $renderer = new Renderer(
        $this->factory(),
        $this->templates(),
        ['config' => new Config('boiler')],
        [],
        true,
    );
    $response = $renderer->response(['text' => 'numbers', 'arr' => [1, 2, 3]], 'renderer');

    $hasContentType = false;
    foreach ($response->getHeaders() as $key => $value) {
        if ($key === 'Content-Type' && $value[0] === 'text/html') {
            $hasContentType = true;
        }
    }

    expect($hasContentType)->toBe(true);
    expect((string)$response->getBody())->toBe("<h1>boiler</h1>\n<p>numbers</p><p>1</p><p>2</p><p>3</p>");
});


test('Html (template dir as string)', function () {
    $renderer = new Renderer(
        $this->factory(),
        TestCase::ROOT_DIR . '/default',
        ['config' => new Config('boiler')],
        [],
        true,
    );
    $response = $renderer->response(['text' => 'numbers', 'arr' => [1, 2, 3]], 'renderer');

    expect((string)$response->getBody())->toBe("<h1>boiler</h1>\n<p>numbers</p><p>1</p><p>2</p><p>3</p>");
});


test('Whitelisting', function () {
    $renderer = new Renderer(
        $this->factory(),
        TestCase::ROOT_DIR . '/default',
        [],
        [Whitelisted::class],
        true,
    );
    $response = $renderer->response(['wl' => new Whitelisted(), 'content' => 'test'], 'whitelist');

    expect((string)$response->getBody())->toBe('<h1>headline</h1><p>test</p>');
});


test('Change content-type (named parameter)', function () {
    $renderer = new Renderer($this->factory(), $this->templates(), [], [], true);
    $response = $renderer->response([], 'plain', contentType: 'application/xhtml+xml');

    $hasContentType = false;
    foreach ($response->getHeaders() as $key => $value) {
        if ($key === 'Content-Type' && $value[0] === 'application/xhtml+xml') {
            $hasContentType = true;
        }
    }

    expect($hasContentType)->toBe(true);
    expect((string)$response->getBody())->toBe("<p>plain</p>\n");
});


test('Iterator', function () {
    // Pass iterator
    $iter = function () {
        yield 'text' => 'characters';

        yield 'arr' => ['a', 'b', 'c'];
    };
    $renderer = new Renderer(
        $this->factory(),
        $this->templates(),
        ['config' => new Config('boiler')],
        [],
        true,
    );
    $response = $renderer->response($iter(), 'renderer');

    expect((string)$response->getBody())->toBe("<h1>boiler</h1>\n<p>characters</p><p>a</p><p>b</p><p>c</p>");
});


test('Template missing', function () {
    (new Renderer($this->factory(), $this->templates()))->response([], 'missing');
})->throws(LookupException::class);


test('Template dirs missing', function () {
    (new Renderer($this->factory(), []))->response([], 'renderer');
})->throws(RendererException::class);


test('Wrong context', function () {
    $renderer = new Renderer($this->factory(), $this->templates());
    $renderer->response(new stdClass(), 'renderer');
})->throws(RendererException::class);


test('No template given', function () {
    $renderer = new Renderer($this->factory(), $this->templates());
    $renderer->response([]);
})->throws(RendererException::class);

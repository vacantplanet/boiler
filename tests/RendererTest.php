<?php

declare(strict_types=1);

use Conia\Boiler\Error\LookupException;
use Conia\Boiler\Error\RendererException;
use Conia\Boiler\Renderer;
use Conia\Boiler\Tests\TestCase;
use Conia\Chuck\Config;

uses(TestCase::class);

test('Template Renderer :: html (array of template dirs)', function () {
    $renderer = new Renderer(
        $this->templates(),
        $this->factory(),
        true,
        ['config' => new Config('boiler')],
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


test('Template Renderer :: html (template dir as string)', function () {
    $renderer = new Renderer(
        TestCase::ROOT_DIR . '/default',
        $this->factory(),
        true,
        ['config' => new Config('boiler')],
    );
    $response = $renderer->response(['text' => 'numbers', 'arr' => [1, 2, 3]], 'renderer');

    expect((string)$response->getBody())->toBe("<h1>boiler</h1>\n<p>numbers</p><p>1</p><p>2</p><p>3</p>");
});


test('Template Renderer :: change content-type (named parameter)', function () {
    $renderer = new Renderer($this->templates(), $this->factory(), true, []);
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


test('Template Renderer :: iterator', function () {
    // Pass iterator
    $iter = function () {
        yield 'text' => 'characters';

        yield 'arr' => ['a', 'b', 'c'];
    };
    $renderer = new Renderer(
        $this->templates(),
        $this->factory(),
        true,
        ['config' => new Config('boiler')],
    );
    $response = $renderer->response($iter(), 'renderer');
    expect((string)$response->getBody())->toBe("<h1>boiler</h1>\n<p>characters</p><p>a</p><p>b</p><p>c</p>");
});


test('Template Renderer :: template missing', function () {
    (new Renderer($this->templates(), $this->factory()))->response([], 'missing');
})->throws(LookupException::class);


test('Template Renderer :: template dirs missing', function () {
    (new Renderer([], $this->factory()))->response([], 'renderer');
})->throws(RendererException::class);


test('Template Renderer :: wrong context', function () {
    $renderer = new Renderer($this->templates(), $this->factory());
    $renderer->response(new stdClass(), 'renderer');
})->throws(RendererException::class);


test('Template Renderer :: no template given', function () {
    $renderer = new Renderer($this->templates(), $this->factory());
    $renderer->response([]);
})->throws(RendererException::class);

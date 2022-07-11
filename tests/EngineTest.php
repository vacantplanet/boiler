<?php

declare(strict_types=1);

use Conia\Boiler\Error\{InvalidTemplateFormat, TemplateNotFound};
use Conia\Boiler\{Engine, Template, Value};
use Conia\Boiler\Tests\TestCase;


uses(TestCase::class);


test('Directory does not exist I', function () {
    new Engine('./doesnotexist');
})->throws(ValueError::class, 'doesnotexist');


test('Directory does not exist II', function () {
    new Engine([TestCase::DEFAULT_DIR, './doesnotexist']);
})->throws(ValueError::class, 'doesnotexist');


test('Simple rendering', function () {
    $engine = new Engine(TestCase::DEFAULT_DIR, ['obj' => $this->obj()]);

    expect(
        $this->fullTrim($engine->render('simple', ['text' => 'rocks']))
    )->toBe('<h1>boiler</h1><p>rocks</p>');
});


test('Simple rendering (namespaced)', function () {
    $engine = new Engine($this->namespaced(), ['obj' => $this->obj()]);

    expect(
        $this->fullTrim($engine->render('namespace:simple', ['text' => 'rocks']))
    )->toBe('<h1>boiler</h1><p>rocks</p>');
});


test('Extension given', function () {
    $engine = new Engine(self::DEFAULT_DIR, ['obj' => $this->obj()]);

    expect($this->fullTrim($engine->render('extension.tpl')))->toBe('<p></p>');
});


test('Unwrap rendering', function () {
    $engine = new Engine(self::DEFAULT_DIR);

    expect($engine->render('unwrap', [
        'html' => '<b>boiler</b>',
    ]))->toBe("&lt;b&gt;boiler&lt;/b&gt;<b>boiler</b>");
});


test('Switch off autoescaping by default', function () {
    $engine = new Engine(self::DEFAULT_DIR, autoescape: false);

    expect($engine->render('noautoescape', [
        'html' => '<b>noautoescape</b>',
    ]))->toBe("<b>noautoescape</b>");
});


test('Switch off autoescaping when calling render', function () {
    $engine = new Engine(self::DEFAULT_DIR, autoescape: true);

    expect($engine->render(
        'noautoescape',
        ['html' => '<b>nodefaultautoescape</b>'],
        autoescape: false,
    ))->toBe("<b>nodefaultautoescape</b>");
});


test('Unwrap rendering with Stringable', function () {
    $engine = new Engine($this->templates());

    expect($engine->render('unwrap', [
        'html' => new class()
        {
            public function __toString(): string
            {
                return '<b>boiler</b>';
            }
        },
    ]))->toBe("&lt;b&gt;boiler&lt;/b&gt;<b>boiler</b>");
});


test('Rendering with Stringable', function () {
    $engine = new Engine($this->templates());
    $stringable = new class()
    {
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
    ])))->toBe("&lt;b&gt;boiler&lt;/b&gt;<b>boiler</b>testmantasmantas");
});


test('Clean rendering', function () {
    $engine = new Engine($this->templates());

    expect($engine->render('clean', [
        'html' => '<script src="/evil.js"></script><b>boiler</b>',
    ]))->toBe("<b>boiler</b>");
});


test('Array rendering', function () {
    $engine = new Engine($this->templates());

    expect(trim($engine->render('iter', [
        'arr' => ['<b>1</b>', '<b>2</b>', '<b>3</b>']
    ])))->toBe('&lt;b&gt;1&lt;/b&gt;&lt;b&gt;2&lt;/b&gt;&lt;b&gt;3&lt;/b&gt;');
});


test('Helper function rendering', function () {
    $engine = new Engine($this->templates(), ['obj' => $this->obj()]);

    expect($this->fullTrim($engine->render('helper')))->toBe(
        '&lt;script&gt;&lt;script&gt;<b>clean</b>'
    );
});


test('Empty helper method', function () {
    $engine = new Engine($this->templates());

    expect($this->fullTrim($engine->render('empty', [
        'empty' => '',
        'notempty' => '<b>not empty</b>',
    ])))->toBe('&lt;b&gt;not empty&lt;/b&gt;');
});


test('Iterator rendering', function () {
    $engine = new Engine($this->templates());

    $iter = function () {
        $a = ['<b>2</b>', '<b>3</b>', '<b>4</b>'];
        foreach ($a as $i) {
            yield $i;
        }
    };

    expect(trim($engine->render('iter', [
        'arr' => $iter()
    ])))->toBe('&lt;b&gt;2&lt;/b&gt;&lt;b&gt;3&lt;/b&gt;&lt;b&gt;4&lt;/b&gt;');
});


test('Complex nested rendering', function () {
    $engine = new Engine(
        $this->templates(),
        ['obj' => $this->obj()]
    );

    $iter = function () {
        $a = [13.73, "String II", 1];
        foreach ($a as $i) {
            yield $i;
        }
    };

    $context = [
        'title' => 'Boiler App',
        'headline' => 'Boiler App',
        'url' => 'https://example.com/boiler     /app    ',
        'array' => [
            '<b>sanitize</b>' => [
                1, "String", new class()
                {
                    public function __toString(): string
                    {
                        return '<p>Object</p>';
                    }
                }
            ],
            666 => $iter(),
        ],
        'html' => '<p>HTML</p>',
    ];
    $result = $this->fullTrim($engine->render('complex', $context));
    $compare = '<!DOCTYPE html><html lang="en"><head><title>Boiler App</title><link rel="stylesheet" ' .
        'href="https://example.com/boiler/app"><meta name="keywords" content="boiler"></head><body>' .
        '<h1>Boiler App</h1><table><tr><td>&lt;b&gt;sanitize&lt;/b&gt;</td><td>1</td><td>String</td>' .
        '<td>&lt;p&gt;Object&lt;/p&gt;</td></tr><tr><td>666</td><td>13.73</td><td>String II</td>' .
        '<td>1</td></tr></table><p>HTML</p></body></html>';

    expect($result)->toBe($compare);
});


test('Single layout', function () {
    $engine = new Engine($this->templates());

    expect($this->fullTrim($engine->render('uselayout', [
        'text' => 'boiler'
    ])))->toBe('<body><p>boiler</p><p>boiler</p></body>');
});


test('Stacked layout', function () {
    $engine = new Engine($this->templates());

    expect($this->fullTrim($engine->render('usestacked', [
        'text' => 'boiler'
    ])))->toBe(
        '<body><div class="stackedsecond"><div class="stackedfirst">' .
            '<p>boiler</p></div></div><p>boiler</p></body>'
    );
});


test('Multilple layouts error', function () {
    (new Engine($this->templates()))->render('multilayout');
})->throws(RuntimeException::class, 'layout already set');


test('Section rendering', function () {
    $engine = new Engine($this->templates());

    expect($this->fullTrim($engine->render('addsection', [
        'text' => 'boiler'
    ])))->toBe('<div><p>boiler</p>boiler</div><ul><li>boiler</li></ul>');
});


test('Append/prepend sections', function () {
    $engine = new Engine($this->templates());

    expect($this->fullTrim($engine->render('appendprepend', [
        'path' => '/assign.js'
    ])))->toBe(
        '<script src="/prepend.js"></script>' .
            '<script src="/assign.js"></script>' .
            '<script src="/append.js"></script>'
    );
});


test('Nested sections error', function () {
    $engine = new Engine($this->templates());

    $engine->render('nestedsections');
})->throws(LogicException::class);


test('Closing unopened section error', function () {
    $engine = new Engine($this->templates());

    $engine->render('closeunopened');
})->throws(LogicException::class);


test('Missing section rendering', function () {
    $engine = new Engine($this->templates());

    expect($this->fullTrim($engine->render('nosection', [
        'text' => 'boiler'
    ])))->toBe('<div><p>boiler</p>boiler</div><p>no list</p>');
});


test('Insert rendering', function () {
    $engine = new Engine($this->templates());

    expect($this->fullTrim($engine->render('insert', [
        'text' => 'Boiler'
    ])))->toBe('<p>Boiler</p><p>Templates</p>');
});


test('Template in sub directory', function () {
    $engine = new Engine($this->templates());

    expect($this->fullTrim($engine->render('sub/home', [
        'text' => 'Boiler'
    ])))->toBe('<h2>Boiler</h2>');
});


test('Additional template directories', function () {
    $engine = new Engine(
        $this->templates($this->additional()),
        ['obj' => $this->obj()]
    );

    expect($this->fullTrim($engine->render('simple', [
        'text' => 'rocks'
    ])))->toBe('<h1>boiler</h1><p>rocks</p>');
    expect($this->fullTrim($engine->render('additional', [
        'text' => 'Additional'
    ])))->toBe('<span>Additional</span>');
});


test('Additional template directories namespaced', function () {
    $engine = new Engine($this->namespaced($this->additional()));

    expect($this->fullTrim($engine->render('namespace:sub/home', [
        'text' => 'Boiler'
    ])))->toBe('<h2>Boiler</h2>');
    expect($this->fullTrim($engine->render('additional:additional', [
        'text' => 'Additional'
    ])))->toBe('<span>Additional</span>');
});


test('Additional template directories shadowing', function () {
    $engine = new Engine($this->namespaced());

    expect($this->fullTrim($engine->render('sub/home', [
        'text' => 'Boiler'
    ])))->toBe('<h2>Boiler</h2>');

    $engine = new Engine($this->namespaced($this->additional()));

    expect($this->fullTrim($engine->render('sub/home', [
        'text' => 'Boiler'
    ])))->toBe('<h1>Sub Boiler</h1>');
    expect($this->fullTrim($engine->render('namespace:sub/home', [
        'text' => 'Boiler'
    ])))->toBe('<h2>Boiler</h2>');
    expect($this->fullTrim($engine->render('additional:sub/home', [
        'text' => 'Boiler'
    ])))->toBe('<h1>Sub Boiler</h1>');
});


test('Exists helper', function () {
    $engine = new Engine($this->templates());

    expect($engine->exists('simple'))->toBe(true);
    expect($engine->exists('wrongindex'))->toBe(false);
});


test('Config error :: wrong template format I', function () {
    $engine = new Engine($this->templates());

    $engine->render('default:sub:index');
})->throws(InvalidTemplateFormat::class, 'Invalid template format');


test('Config error :: wrong template format II', function () {
    $engine = new Engine($this->templates());

    $engine->render('');
})->throws(ValueError::class, 'No template');


test('Render error :: missing template', function () {
    $engine = new Engine($this->templates());

    $engine->render('nonexistent');
})->throws(TemplateNotFound::class, 'not found');


test('Render error :: template outside root directory I', function () {
    $engine = new Engine($this->templates());

    $engine->render('.././../.././../etc/passwd');
})->throws(TemplateNotFound::class, 'not found');


test('Render error :: template outside root directory II', function () {
    $engine = new Engine($this->templates());

    $engine->render('../unreachable');
})->throws(TemplateNotFound::class, 'outside');


test('Render error :: parse error', function () {
    $engine = new Engine($this->templates());

    $engine->render('failing');
})->throws(ParseError::class);


test('Custom template method', function () {
    $engine = new Engine($this->templates());
    $engine->registerMethod('upper', function (Value $value): Value {
        return new Value(strtoupper($value->unwrap()));
    });

    expect($this->fullTrim($engine->render('method', [
        'text' => 'Boiler'
    ])))->toBe('<h2>BOILER</h2>');
});


test('Unknown custom method', function () {
    $engine = new Engine($this->templates());

    $engine->render('unknownmethod');
})->throws(ValueError::class, 'upper');

Boiler Template Engine
======================

Boiler is a native PHP 8.1 template engine in the vein of *[Plates](https://platesphp.com/)*,
from which it is heavily inspired. Like Plates, it also does not introduce a new template 
language and instead uses PHP itself. You simply use the PHP statements you already know.

The main differences to *Plates* are:

* It automatically escapes strings and [Stringable](https://www.php.net/manual/en/class.stringable.php) values. This is optional. You can turn it off globally or for single render calls.
* The template context is global. That means all values passed to the main template are 
  available in all included parts, like [sections](sections.md), [inserts](inserts.md) or
  [layouts](layouts.md).

## Features

* Autoescaping: Prevents XSS from untrusted user input.
* A simple API. Only one class, the [Engine](engine.md), is needed.
* Template [inheritance](layouts.md) and [inclusion](inserts.md).
* You use plain PHP in your templates. No need to learn another syntax.
* Reasonable performance.

## Installation

```shell
composer require conia/boiler
```


## Quick start

Assuming the following directory structure ...

```text
path
`-- to
    `-- templates
        `-- page.php
```

... and the content of the file `/path/to/templates/page.php` to be:
    
    <p>ID <?= $id ?></p>

Now create a `Engine` instance and render the template:

```php
use Conia\Boiler\Engine;

$engine = new Engine('/path/to/templates');
$html = $engine->render('page', ['id' => 13]);

assert($html == '<p>ID 13</p>');
```

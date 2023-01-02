Boiler
======

Boiler is a native PHP 8.1 template engine in the vein of [Plates](https://platesphp.com/), which
uses PHP itself instead of a custom template language.

> :warning: **Note**: This template engine is under active development, some of the listed features are still experimental and subject to change. Large parts of the documentation are missing. 

Notable differences:

* It auto escapes strings and [Stringable](https://www.php.net/manual/en/class.stringable.php) values.
* The template context, i. e. all variables available in the template, is global.


## Installation

    composer require conia/boiler


## Quick start

Assuming the following directory structure ...

    path
    `-- to
        `-- templates
           `-- page.php

... and the content of the file `/path/to/templates/page.php` to be:
    
    <p>ID <?= $id ?></p>

Now create a `Engine` instance and render the template:

    use Conia\Boiler\Engine;

    $engine = new Engine('/path/to/templates');
    $html = $engine->render('page', ['id' => 13]);

    assert($html == '<p>ID 13</p>');

## Run the tests

    pest --coverage && \
        psalm --no-cache --show-info=true && \
        phpcs -s -p --ignore=tests/templates src tests


## License

Boiler is released under the MIT [license](LICENSE.md).

Copyright © 2022 ebene fünf GmbH. All rights reserved.

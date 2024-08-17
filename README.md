# Boiler

[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg)](LICENSE.md)
[![Codacy Badge](https://app.codacy.com/project/badge/Grade/cd2e266bcfb14b21b8ce11ddaafe4f06)](https://app.codacy.com/gh/vacantplanet/boiler/dashboard?utm_source=gh&utm_medium=referral&utm_content=&utm_campaign=Badge_grade)
[![Codacy Badge](https://app.codacy.com/project/badge/Coverage/cd2e266bcfb14b21b8ce11ddaafe4f06)](https://app.codacy.com/gh/vacantplanet/boiler/dashboard?utm_source=gh&utm_medium=referral&utm_content=&utm_campaign=Badge_coverage)
[![Psalm level](https://shepherd.dev/github/vacantplanet/boiler/level.svg?)](https://vacantplanet.dev/boiler)
[![Psalm coverage](https://shepherd.dev/github/vacantplanet/boiler/coverage.svg?)](https://shepherd.dev/github/vacantplanet/boiler)

Boiler is a native >=PHP 8.2 template engine in the vein of
[Plates](https://platesphp.com/), which uses PHP itself instead of a custom
template language.

> :warning: **Note**: This template engine is under active development, some of
> the listed features are still experimental and subject to change. Large parts
> of the documentation are missing.

Notable differences:

* It auto escapes strings and
  [Stringable](https://www.php.net/manual/en/class.stringable.php) values.
* The template context, i. e. all variables available in the template, is
  global.

## Installation

```console
composer require vacantplanet/boiler
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

```php
<p>ID <?= $id ?></p>
```

Now create a `Engine` instance and render the template:

```php
use VacantPlanet\Boiler\Engine;

$engine = new Engine('/path/to/templates');
$html = $engine->render('page', ['id' => 13]);

assert($html == '<p>ID 13</p>');
```

## Run the tests

```console
phpunit --testdox && \
	psalm --no-cache --show-info=true && \
	phpcs -s -p --ignore=tests/templates src tests
```

## License

Boiler is released under the MIT [license](LICENSE.md).

Copyright © 2022-2024 ebene fünf GmbH. All rights reserved.

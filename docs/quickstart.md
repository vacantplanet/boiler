---
title: Quick Start
---
Quick Start
===========

Install Boiler via Composer:

```shell
composer require conia/boiler
```

Then create a directory where your PHP templates reside. 
Assuming the following directory structure ...

```text
path
`-- to
    `-- templates
```

... create the file `/path/to/templates/page.php` with the content:
    
    <p>ID <?= $id ?></p>

Now create a `Engine` instance and render the template:

```php
use Conia\Boiler\Engine;

$engine = new Engine('/path/to/templates');
$html = $engine->render('page', ['id' => 13]);

assert($html == '<p>ID 13</p>');
```


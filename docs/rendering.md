---
title: Rendering Templates
---
Rendering Templates
===================

After you created an [`\VacantPlanet\Boiler\Engine`](engine.md) object, you can render
templates using its `render()` method.

Throughout this page we assume the following directory structure ...

```text
path
`-- to
	|-- templates
	|   |-- subdir
	|   |   `-- subtemplate.php
	|   |-- blog.php
	|   |-- layout.php
	|   |-- more.php
	|   `-- page.php
	|
	`-- theme
		|-- blog.php
		`-- additional.php
```

... and the [`Engine`](engine.md) initialized in this way:

	$engine = new \VacantPlanet\Boiler\Engine(
		[
			'theme' => '/path/to/theme',
			'templates' => '/path/to/templates',
		],
		defaults: [
			'titleSuffix' => ' - Boiler Template Engine',
		],
		autoescape: true,
	);


### Simple rendering

To render the template *page.php* from the filesystem tree above, you
reference it using the name *page* with or without the file extension.

	$html = $engine->render('page');

	// or
	$html = $engine->render('page.php');

If you like to use a custom file extension add it to the name:

	$engine->render('page.tpl');

Values available to the template must be provided as associative array:

	$html = $engine->render('page', [
		'title' => 'The Title',
		'content' => 'The content of the page.',
	]);

If the *page.php* template would look like this:

	<body>
		<h1><?= $title ?></h1>
		<div><?= $content ?></div>
	</body>

The result of the `render` call above would be:

	<body>
		<h1>The Title</h1>
		<div>The content of the page.</div>
	</body>

### Templates in subdirectories

	$html = $engine->render('subdir/subtemplate', ['value' =>  13]);

### Template overrides

If a template with the same name is available in more than one of your 
template directories, the first one found is used. In our example above,
there is a *blog* template in both `templates` and `theme`. As `theme`
is the first entry in the array passed to [`Engine`](engine.md), *blog*
from this directory is used by default:

	// renders /path/to/theme/blog.php
	$engine->render('blog', ['value' => 13]);

This can for example be used to implement themeable or customizable templates
where you provide a default set of templates which can later be partially 
or completely overriden by a theme or similar.

You can force to render *blog* from the `templates` directory if you use 
namespaces. See the next section on how this is accomplished.

### Namespaces

In our engine instantiation example above we pass an associative array with
the template directories to the constructor. The keys of the array serve as namespaces.
To render a template from a specific namespace, locate it using the
namespace followed bei a colon followed by the template name:

	$html = $engine->render('templates:blog', ['value' =>  13]);

	// A template in a subdirectory
	$html = $engine->render('templates:subdir/subtemplate', ['value' =>  13]);

This way you can prevent template overriding explained in the section before.


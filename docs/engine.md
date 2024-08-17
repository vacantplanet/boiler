# The Engine

The `Engine` is the Boiler's central object and usually the only one you have
to manually instatiate. It is used to locate and load templates from the file
system.

Throughout this page we assume the following directory structure:

```text
path
`-- to
	|-- templates
	`-- additional
```

## Creating the `Engine` instance

To create an Engine instance, you simply pass one or more paths to directories
where your templates are located. Additionally, you can optionally set default
values that are available for all your templates, or you can globally disable
the autoescaping feature.

### Using a single template directory

The only required parameter of the constructor is the path to a directory where
your templates reside:

```php
$engine = new \VacantPlanet\Boiler\Engine('/path/to/templates');
```

If the directory does not exists, Boiler throws
a `\VacantPlanet\Boiler\Exception\DirectoryNotFound` exception.

### Using multiple directories

If you have multiple directories, pass them in an array:

```php
$engine = new \VacantPlanet\Boiler\Engine(['/path/to/templates', '/path/to/additional']);
```

**Note**: The directories are searched in order.

Using the example above: If a template cannot be located in
`/path/to/additional`, Boiler tries to find it in `/path/to/additional` and so
on.

### Using namespaces

You can use namespaces to later be able to address a specific directory. Pass
the list of directories as associative array where the keys serve as
namespaces:

```php
$engine = new \VacantPlanet\Boiler\Engine([
	'first' => '/path/to/templates', 
	'second' => '/path/to/additional'
]);
```

Check [*Rendering Templates*](rendering.md) to see it in action.

### Adding default values

You can assign default values which are available in all templates:

```php
$engine = new \VacantPlanet\Boiler\Engine('/path/to/dir', ['value' => 'default value']);
```

### Turning off autoescaping

If you don't want to use the autoescaping feature, e. g. to improve the
performance of your application, you can turn it off globally:

```php
$engine = new \VacantPlanet\Boiler\Engine('/path/to/dir', [], false);

// better:
$engine = new \VacantPlanet\Boiler\Engine('/path/to/dir', autoescape: false);
```

## Rendering Templates

You simplic call the `render` method and pass the name/path of the template and
optionally an array of values (the context) which will be available as
variables in the template.

```php
$engine->render('template');

// with context
$engine->render('template', ['value1' => 1, 'value2' => 2]);
```

See [*Rendering Templates*](rendering.md) for more information.

## Adding custom template methods

Custom methods can be accessed in templates using `$this` (See [*Rendering
Templates*](rendering.md)). To a add a method you pass a[`Closure` or anonymous
function](https://www.php.net/manual/en/functions.anonymous.php) to
`registerMethod`:

```php
$engine->registerMethod('upper', function (string $value): string {
	return strtoupper($value);
});
```

## Other useful Engine methods

### Check if a template exists

To check if a template exists before rendering it, use the method `exists`:

```php
if ($engine->exists('template')) {
	$engine->render('template');
}
```

### Get the file system path of a template

```php
$filePath = $engine->getFile('template');
```

### Get a template instance without rendering it

```php
$template = $engine->template('template');

assert($template instanceof \VacantPlanet\Boiler\Template);
```

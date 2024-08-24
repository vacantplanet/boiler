<?php

declare(strict_types=1);

namespace VacantPlanet\Boiler;

use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;
use VacantPlanet\Boiler\Proxy\Proxy;
use VacantPlanet\Boiler\Proxy\ProxyInterface;

/**
 * @psalm-api
 *
 * This class contains all methods which are accessible in template
 * on $this. For example `$this->esc`, or `$this->layout`. So
 * we have to suppress the phpmd warning as we exceed the default
 * max value of 10 public methods.
 *
 * The methods that can be used in the template with $this are included
 * in this class. `$this->esc` or `$this->layout`, for example.
 * Hence, it is necessary to suppress the phpmd warning because we have
 * exceeded the default maximum of 10 public methods.
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class TemplateContext
{
	private const ESCAPE_FLAGS = ENT_QUOTES | ENT_SUBSTITUTE;
	private const ESCAPE_ENCODING = 'UTF-8';

	/**
	 * @psalm-param list<class-string> $whitelist
	 */
	public function __construct(
		protected readonly Template $template,
		protected array $context,
		public readonly array $whitelist,
		public readonly bool $autoescape,
	) {}

	public function __call(string $name, array $args): mixed
	{
		$callable = $this->template->getMethods()->get($name);

		return $callable(...$args);
	}

	public function context(array $values = []): array
	{
		return array_map(
			[$this, 'wrapIf'],
			array_merge($this->context, $values),
		);
	}

	public function add(string $key, mixed $value): mixed
	{
		$this->context[$key] = $value;

		return Wrapper::wrap($value);
	}

	public function esc(
		Proxy|string $value,
		int $flags = self::ESCAPE_FLAGS,
		string $encoding = self::ESCAPE_ENCODING,
	): string {
		if ($value instanceof Proxy) {
			return htmlspecialchars((string) $value->unwrap(), $flags, $encoding);
		}

		return htmlspecialchars($value, $flags, $encoding);
	}

	public function clean(
		string $value,
		?HtmlSanitizerConfig $config = null,
	): string {
		return Sanitizer::clean($value, $config);
	}

	/**
	 * @psalm-param non-empty-string $path
	 */
	public function layout(string $path, ?array $context = null): void
	{
		$this->template->setLayout(new LayoutValue($path, $context));
	}

	/**
	 * Includes another template into the current template.
	 *
	 * If no context is passed it shares the context of the calling template.
	 *
	 * @psalm-param non-empty-string $path
	 */
	public function insert(string $path, array $context = []): void
	{
		$path = $this->template->engine->getFile($path);
		$template = new Template(
			$path,
			sections: $this->template->sections,
			engine: $this->template->engine,
		);

		echo $this->autoescape ?
			$template->render($this->context($context), $this->whitelist) :
			$template->renderUnescaped($this->context($context), $this->whitelist);
	}

	public function begin(string $name): void
	{
		$this->template->sections->begin($name);
	}

	public function append(string $name): void
	{
		$this->template->sections->append($name);
	}

	public function prepend(string $name): void
	{
		$this->template->sections->prepend($name);
	}

	public function end(): void
	{
		$this->template->sections->end();
	}

	public function section(string $name, string $default = ''): string
	{
		if (func_num_args() > 1) {
			return $this->template->sections->getOr($name, $default);
		}

		return $this->template->sections->get($name);
	}

	public function has(string $name): bool
	{
		return $this->template->sections->has($name);
	}

	protected function wrapIf(mixed $value): mixed
	{
		if ($value instanceof ProxyInterface) {
			return $value;
		}

		if (is_object($value)) {
			foreach ($this->whitelist as $whitelisted) {
				if (
					$value::class === $whitelisted
					|| is_subclass_of($value::class, $whitelisted)
				) {
					return $value;
				}
			}
		}

		return Wrapper::wrap($value);
	}
}

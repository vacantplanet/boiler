<?php

declare(strict_types=1);

namespace VacantPlanet\Boiler;

use Throwable;
use VacantPlanet\Boiler\Exception\LookupException;
use VacantPlanet\Boiler\Exception\RuntimeException;

/**
 * @psalm-api
 */
class Template
{
	use RegistersMethod;

	public readonly Engine $engine;
	public readonly Sections $sections;
	protected ?LayoutValue $layout = null;
	protected CustomMethods $customMethods;

	public function __construct(
		public readonly string $path,
		?Sections $sections = null,
		?Engine $engine = null,
	) {
		$this->sections = $sections ?: new Sections();
		$this->customMethods = new CustomMethods();

		if ($engine === null) {
			$dir = dirname($path);

			if ($dir === '' || $path === '') {
				throw new LookupException('No directory given or empty path');
			}

			$this->engine = new Engine($dir, true, [], []);

			if (!is_file($path)) {
				throw new LookupException('Template not found: ' . $path);
			}

			return;
		}

		$this->engine = $engine;
	}

	/**
	 * @psalm-param list<class-string> $whitelist
	 */
	public function render(array $context = [], array $whitelist = []): string
	{
		return $this->renderTemplate($context, $whitelist, autoescape: $this->engine->autoescape);
	}

	/**
	 * @psalm-param list<class-string> $whitelist
	 */
	public function renderEscaped(array $context = [], array $whitelist = []): string
	{
		return $this->renderTemplate($context, $whitelist, autoescape: true);
	}

	/**
	 * @psalm-param list<class-string> $whitelist
	 */
	public function renderUnescaped(array $context = [], array $whitelist = []): string
	{
		return $this->renderTemplate($context, $whitelist, autoescape: false);
	}

	/**
	 * Defines a layout template that will be wrapped around this instance.
	 *
	 * Typically it’s placed at the top of the file.
	 */
	public function setLayout(LayoutValue $layout): void
	{
		if ($this->layout === null) {
			$this->layout = $layout;

			return;
		}

		throw new RuntimeException('Template error: layout already set');
	}

	public function layout(): ?LayoutValue
	{
		return $this->layout;
	}

	public function setCustomMethods(CustomMethods $customMethods): void
	{
		$this->customMethods = $customMethods;
	}

	/**
	 * @psalm-param list<class-string> $whitelist
	 */
	protected function renderTemplate(array $context, array $whitelist, bool $autoescape): string
	{
		$content = $this->getContent($context, $whitelist, $autoescape);

		if ($this instanceof Layout) {
			return $content->content;
		}

		return $this->renderLayouts(
			$this,
			$content->templateContext,
			$whitelist,
			$content->content,
			$autoescape,
		);
	}

	/** @psalm-param list<class-string> $whitelist */
	protected function templateContext(array $context, array $whitelist, bool $autoescape): TemplateContext
	{
		return new TemplateContext($this, $context, $whitelist, $autoescape);
	}

	/**
	 * @psalm-param list<class-string> $whitelist
	 * @SuppressWarnings(PHPMD.LongVariable)
	 * @SuppressWarnings(PHPMD.CamelCaseVariableName)
	 */
	protected function getContent(array $context, array $whitelist, bool $autoescape): Content
	{
		$templateContext = $this->templateContext($context, $whitelist, $autoescape);

		$load = function (string $templatePath, array $context = []): void {
			// Hide $templatePath. Could be overwritten if $context['templatePath'] exists.
			$____template_path____ = $templatePath;

			extract($context);

			/** @psalm-suppress UnresolvableInclude */
			include $____template_path____;
		};

		/** @var callable */
		$load = $load->bindTo($templateContext);
		$level = ob_get_level();

		try {
			ob_start();

			$load(
				$this->path,
				$autoescape ?
					$templateContext->context() :
					$context,
			);

			$content = ob_get_clean();

			return new Content($content, $templateContext);
		} catch (Throwable $e) {
			throw new ($e::class)(
				"Template rendering error ({$this->path}): " . $e->getMessage(),
				$e->getCode(),
				$e,
			);
		} finally {
			while (ob_get_level() > $level) {
				ob_end_clean();
			}
		}
	}

	/** @psalm-param list<class-string> $whitelist */
	protected function renderLayouts(
		Template $template,
		TemplateContext $context,
		array $whitelist,
		string $content,
		bool $autoescape,
	): string {
		while ($layout = $template->layout()) {
			$file = $template->engine->getFile($layout->layout);
			$template = new Layout(
				$file,
				$content,
				$this->sections,
				$template->engine,
			);

			$layoutContext = is_null($layout->context)
				? $context->context()
				: $context->context($layout->context);

			$content = $template->renderTemplate($layoutContext, $whitelist, $autoescape);
		}

		return $content;
	}
}

<?php

declare(strict_types=1);

namespace Conia\Boiler;

use \RuntimeException;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;


class BoundTemplate
{
    private const ESCAPE_FLAGS = ENT_QUOTES | ENT_SUBSTITUTE;
    private const ESCAPE_ENCODING = 'UTF-8';

    public function __construct(
        protected readonly Template $template,
        public readonly array $context,
        public readonly bool $autoescape,
    ) {
        error_log($template->path);
    }

    public function context(array $values = []): array
    {
        return array_map(
            function ($value): mixed {
                return Wrapper::wrap($value);
            },
            array_merge($this->context, $values)
        );
    }

    public function e(
        string|Value $value,
        int $flags = self::ESCAPE_FLAGS,
        string $encoding = self::ESCAPE_ENCODING,
    ): string {
        if ($value instanceof Value) {
            return htmlspecialchars($value->unwrap(), $flags, $encoding);
        }

        return htmlspecialchars($value, $flags, $encoding);
    }

    public function escape(
        string $value,
        int $flags = self::ESCAPE_FLAGS,
        string $encoding = self::ESCAPE_ENCODING,
    ): string {
        return $this->e($value, $flags, $encoding);
    }

    public function clean(
        string $value,
        HtmlSanitizerConfig $config = null,
        bool $removeEmptyLines = true,
    ): string {
        return Sanitizer::clean($value, $config, $removeEmptyLines);
    }

    public function url(string $value): string
    {
        return filter_var($value, FILTER_SANITIZE_URL);
    }

    public function layout(string $path): void
    {
        $this->template->setLayout($path);
    }

    public function getLayout(): string
    {
        if ($this->layout !== null) {
            return $this->layout;
        }

        throw new RuntimeException('Template error: layout not set');
    }

    /**
     * Includes another template into the current template
     *
     * If no context is passed it shares the context of the calling template.
     */
    public function insert(string $path, array $context = []): void
    {
        $path = $this->template->getIncludePath($path);
        $template = new Template($path, $this->template->engine);

        if (func_num_args() > 1) {
            echo $template->render($context, $this->autoescape);
        } else {
            echo $template->render($this->context, $this->autoescape);
        }
    }

    public function begin(string $name): void
    {
        $this->template->beginSection($name);
    }

    public function append(string $name): void
    {
        $this->template->appendSection($name);
    }

    public function prepend(string $name): void
    {
        $this->template->prependSection($name);
    }

    public function end(): void
    {
        $this->template->endSection();
    }

    public function section(string $name): string
    {
        return $this->template->getSection($name);
    }

    public function hasSection(string $name): bool
    {
        return $this->template->hasSection($name);
    }

    public function __call(string $name, array $args): mixed
    {
        $callable = $this->template->getMethods()->get($name);

        return $callable(...$args);
    }
}

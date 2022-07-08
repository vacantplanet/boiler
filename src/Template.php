<?php

declare(strict_types=1);

namespace Conia\Boiler;

use \RuntimeException;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;


class Template
{
    protected ?string $layout = null;

    public function __construct(
        protected readonly Engine $engine,
        public readonly string $path,
        public readonly array $context,
    ) {
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

    public function escape(string $value): string
    {
        return htmlspecialchars($value);
    }

    public function e(string $value): string
    {
        return htmlspecialchars($value);
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

    /**
     * Defines a layout template that will be wrapped around this instance.
     *
     * Typically it’s placed at the top of the file.
     */
    public function layout(string $moniker): void
    {
        if ($this->layout === null) {
            $this->layout = $moniker;

            return;
        } else {
            throw new RuntimeException('Template error: layout already set');
        }
    }

    public function hasLayout(): bool
    {
        return $this->layout !== null;
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
    public function insert(string $moniker, array $context = []): void
    {
        if (func_num_args() > 1) {
            echo $this->engine->render($moniker, $context);
        } else {
            echo $this->engine->render($moniker, $this->context);
        }
    }

    public function begin(string $name): void
    {
        $this->engine->beginSection($name);
    }

    public function append(string $name): void
    {
        $this->engine->appendSection($name);
    }

    public function prepend(string $name): void
    {
        $this->engine->prependSection($name);
    }

    public function end(): void
    {
        $this->engine->endSection();
    }

    public function section(string $name): string
    {
        return $this->engine->getSection($name);
    }

    public function hasSection(string $name): bool
    {
        return $this->engine->hasSection($name);
    }

    public function __call(string $name, array $args): mixed
    {
        $callable = $this->engine->getMethods()->get($name);

        return $callable(...$args);
    }
}

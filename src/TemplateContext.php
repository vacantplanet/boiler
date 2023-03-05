<?php

declare(strict_types=1);

namespace Conia\Boiler;

use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;

/** @psalm-api */
class TemplateContext
{
    private const ESCAPE_FLAGS = ENT_QUOTES | ENT_SUBSTITUTE;
    private const ESCAPE_ENCODING = 'UTF-8';

    /**
     * @psalm-param list<class-string> $whitelist
     */
    public function __construct(
        protected readonly Template $template,
        public array $context,
        public readonly array $whitelist,
        public readonly bool $autoescape,
    ) {
    }

    public function __call(string $name, array $args): mixed
    {
        $callable = $this->template->getMethods()->get($name);

        return $callable(...$args);
    }

    public function context(array $values = []): array
    {
        return array_map(
            [$this, 'wrapIf'],
            array_merge($this->context, $values)
        );
    }

    public function add(string $key, mixed $value): mixed
    {
        $this->context[$key] = $value;

        return $this->wrapIf($value);
    }

    public function e(
        string|Value $value,
        int $flags = self::ESCAPE_FLAGS,
        string $encoding = self::ESCAPE_ENCODING,
    ): string {
        if ($value instanceof Value) {
            return htmlspecialchars((string)$value->unwrap(), $flags, $encoding);
        }

        return htmlspecialchars($value, $flags, $encoding);
    }

    public function escape(
        string|Value $value,
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

    public function url(string|Value $value): string
    {
        return Url::clean($value instanceof Value ? (string)$value->unwrap() : $value);
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

        if (func_num_args() > 1) {
            echo $template->render($context, $this->whitelist, $this->autoescape);
        } else {
            echo $template->render($this->context, $this->whitelist, $this->autoescape);
        }
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

    public function section(string $name): string
    {
        return $this->template->sections->get($name);
    }

    public function has(string $name): bool
    {
        return $this->template->sections->has($name);
    }

    protected function wrapIf(mixed $value): mixed
    {
        if ($value instanceof ValueInterface) {
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

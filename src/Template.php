<?php

declare(strict_types=1);

namespace Conia\Boiler;

use \LogicException;
use \RuntimeException;
use \Throwable;
use \ValueError;


class Template
{
    use RegistersMethod;

    protected array $capture = [];
    protected array $sections = [];
    protected ?string $layout = null;

    protected SectionMode $sectionMode = SectionMode::Closed;
    protected CustomMethods $customMethods;

    public function __construct(
        public readonly string $path,
        public readonly ?Engine $engine = null
    ) {
    }

    public function getIncludePath(string $path): string
    {
        if ($this->engine) {
            return $this->engine->getFile($path);
        }

        if (is_file($path)) {
            return $path;
        }

        // Without engine we cannot use the template lookup functionality
        // and therefore try to locate the file relative to this template.

        if (empty(pathinfo($path, PATHINFO_EXTENSION))) {
            $path += '.php';
        }

        $path = realpath(dirname($this->path) . DIRECTORY_SEPARATOR . $path);

        if ($path) {
            return $path;
        }

        throw new ValueError('Included template not found: ' . $path);
    }

    protected function boundTemplate(array $context, bool $autoescape): BoundTemplate
    {
        return new BoundTemplate($this, $context, $autoescape);
    }

    protected function getContent(array $context, bool $autoescape): string
    {
        $bound = $this->boundTemplate($context, $autoescape);

        $load =  function (string $templatePath, array $context = []): void {
            // Hide $templatePath. Could be overwritten if $context['templatePath'] exists.
            $____template_path____ = $templatePath;

            extract($context);

            /** @psalm-suppress UnresolvableInclude */
            include $____template_path____;
        };

        /** @var callable */
        $load = $load->bindTo($bound);
        $level = ob_get_level();

        try {
            ob_start();

            $load(
                $this->path,
                $autoescape ?
                    $bound->context() :
                    $context
            );

            return ob_get_clean();
        } catch (Throwable $e) {
            while (ob_get_level() > $level) {
                ob_end_clean();
            }

            throw $e;
        }
    }

    public function render(array $context = [], bool $autoescape = true): string
    {
        $content = $this->getContent($context, $autoescape);

        if ($this instanceof Layout) {
            return $content;
        }

        $template = $this;

        while ($template->hasLayout()) {
            $template = new Layout(
                $template->getIncludePath($template->layout),
                $content,
                $template->engine,
            );
            $content = $template->render($context, $autoescape);
        }

        return $content;
    }

    protected function openSection(string $name, SectionMode $mode): void
    {
        if ($this->sectionMode !== SectionMode::Closed) {
            throw new LogicException('Nested sections are not allowed');
        }

        $this->sectionMode = $mode;
        $this->capture[] = $name;
        ob_start();
    }

    public function beginSection(string $name): void
    {
        $this->openSection($name, SectionMode::Assign);
    }

    public function appendSection(string $name): void
    {
        $this->openSection($name, SectionMode::Append);
    }

    public function prependSection(string $name): void
    {
        $this->openSection($name, SectionMode::Prepend);
    }

    public function endSection(): void
    {
        $content = ob_get_clean();
        $name = array_pop($this->capture);

        $this->sections[$name] = match ($this->sectionMode) {
            SectionMode::Assign => $content,
            SectionMode::Append => ($this->sections[$name] ?? '') . $content,
            SectionMode::Prepend => $content . ($this->sections[$name] ?? ''),
            SectionMode::Closed => throw new LogicException('No section started'),
        };

        $this->sectionMode = SectionMode::Closed;
    }

    public function getSection(string $name): string
    {
        return $this->sections[$name];
    }

    public function hasSection(string $name): bool
    {
        return isset($this->sections[$name]);
    }

    /**
     * Defines a layout template that will be wrapped around this instance.
     *
     * Typically it’s placed at the top of the file.
     */
    public function setLayout(string $path): void
    {
        if ($this->layout === null) {
            $this->layout = $path;

            return;
        } else {
            throw new RuntimeException('Template error: layout already set');
        }
    }

    public function hasLayout(): bool
    {
        return $this->layout !== null;
    }

    public function setCustomMethods(CustomMethods $customMethods): void
    {
        $this->customMethods = $customMethods;
    }
}

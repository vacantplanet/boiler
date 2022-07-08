<?php

declare(strict_types=1);

namespace Conia\Boiler;

use \LogicException;
use \Throwable;
use \ValueError;
use Conia\Boiler\Error\{InvalidTemplateFormat, TemplateNotFound};


class Engine
{
    protected readonly array $dirs;
    protected array $capture = [];
    protected array $sections = [];
    protected SectionMode $sectionMode = SectionMode::Closed;

    public function __construct(
        string|array $dirs,
        protected readonly array $defaults = [],
        protected readonly bool $autoescape = true,
    ) {
        $this->dirs = $this->prepareDirs($dirs);
    }

    protected function prepareDirs(string|array $dirs): array
    {
        if (is_string($dirs)) {
            return [realpath($dirs) ?: throw new ValueError('Directory does not exist ' . $dirs)];
        }

        return array_map(
            fn ($dir) => realpath($dir) ?: throw new ValueError('Directory does not exist ' . $dir),
            $dirs
        );
    }

    protected function getContent(Template $template, bool $autoescape): string
    {
        $load =  function (string $templatePath, array $context = []): void {
            // Hide $templatePath. Could be overwritten if $context['templatePath'] exists.
            $____template_path____ = $templatePath;

            extract($context);

            /** @psalm-suppress UnresolvableInclude */
            include $____template_path____;
        };

        /** @var callable */
        $load = $load->bindTo($template);
        $level = ob_get_level();

        try {
            ob_start();

            $load(
                $template->path,
                $autoescape ? $template->context() : $template->context
            );

            return ob_get_clean();
        } catch (Throwable $e) {
            while (ob_get_level() > $level) {
                ob_end_clean();
            }

            throw $e;
        }
    }

    protected function renderTemplate(Template $template, bool $autoescape): string
    {
        $content = $this->getContent($template, $autoescape);

        if ($template instanceof Layout) {
            return $content;
        }

        while ($template->hasLayout()) {
            $template = new Layout(
                $this,
                $this->getPath($template->getLayout()),
                $template->context,
                $content
            );
            $content = $this->renderTemplate($template, $autoescape);
        }

        return $content;
    }

    public function render(
        string $moniker,
        array $context = [],
        ?bool $autoescape = null
    ): string {
        if (empty($moniker)) {
            throw new ValueError('No template path given');
        }

        if (is_null($autoescape)) {
            // Use the engine's default value if nothing is passesd
            $autoescape = $this->autoescape;
        }

        $template =  new Template(
            $this,
            $this->getPath($moniker),
            array_merge($this->defaults, $context),
        );

        return $this->renderTemplate($template, $autoescape);
    }

    protected function getPath(string $moniker): string
    {
        if (strpos($moniker, ':') === false) {
            $namespace = null;
            $file = $moniker;
        } else {
            $segments = explode(':', $moniker);
            if (count($segments) == 2) {
                [$namespace, $file] = [$segments[0], $segments[1]];
            } else {
                throw new InvalidTemplateFormat(
                    "Invalid template format: '$moniker'. " .
                        "Use 'namespace:template/path or template/path'."
                );
            }
        }

        $ext = '';

        if (empty(pathinfo($file, PATHINFO_EXTENSION))) {
            $ext = '.php';
        }

        $ds = DIRECTORY_SEPARATOR;

        if ($namespace) {
            $path = realpath($this->dirs[$namespace] . $ds . $file . $ext);

            if (is_file($path)) {
                return $path;
            }
        } else {
            foreach ($this->dirs as $dir) {
                $path = realpath($dir . $ds . $file . $ext);

                if ($path && is_file($path)) {
                    if (!str_starts_with($path, $dir)) {
                        throw new TemplateNotFound(
                            'Template is outside of root directory: ' . $path
                        );
                    }

                    return $path;
                }
            }
        }

        throw new TemplateNotFound("Template '$moniker' not found");
    }

    public function exists(string $moniker): bool
    {
        try {
            $this->getPath($moniker);
            return true;
        } catch (TemplateNotFound) {
            return false;
        }
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
}

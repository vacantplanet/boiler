<?php

declare(strict_types=1);

namespace Conia\Boiler;

use \ValueError;
use \Throwable;
use Conia\Boiler\Error\{InvalidTemplateFormat, TemplateNotFound};


class Engine
{
    protected readonly array $dirs;
    protected array $capture = [];
    protected array $sections = [];

    public function __construct(
        string|array $dirs,
        protected readonly array $defaults = []
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

    protected function renderTemplate(Template $template, array $context): string
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
        $error = null;

        ob_start();

        try {
            $load($template->path, $template->context());
        } catch (Throwable $e) {
            $error = $e;
        }

        $content = ob_get_contents();
        ob_end_clean();

        if ($error !== null) {
            throw $error;
        }

        if ($template->hasLayout()) {
            $layout = new Layout(
                $this,
                $this->getPath($template->getLayout()),
                $context,
                $content
            );
            $content = $this->renderTemplate($layout, $context);
        }

        return $content;
    }

    public function render(string $moniker, array $context = []): string
    {
        if (empty($moniker)) {
            throw new ValueError('No template path given');
        }

        $template =  new Template(
            $this,
            $this->getPath($moniker),
            array_merge($this->defaults, $context),
        );

        return $this->renderTemplate($template, $context);
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

    public function beginSection(string $name): void
    {
        $this->capture[] = $name;
        ob_start();
    }

    public function endSection(): void
    {
        $content = ob_get_clean();
        $name = array_pop($this->capture);
        $this->sections[$name] = $content;
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

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

    public function render(string $moniker, array $context = []): string
    {
        if (empty($moniker)) {
            throw new ValueError('No template path given');
        }

        $error = null;
        $path = $this->getPath($moniker);

        $load =  function (string $templatePath, array $context = []): void {
            // Hide $templatePath. Could be overwritten if $context['templatePath'] exists.
            $____template_path____ = $templatePath;

            extract($context);

            /** @psalm-suppress UnresolvableInclude */
            include $____template_path____;
        };

        $template = $this->createTemplate($moniker, array_merge($this->defaults, $context));

        /** @var callable */
        $load = $load->bindTo($template);

        ob_start();

        try {
            $load($path, $template->context());
        } catch (Throwable $e) {
            $error = $e;
        }

        $content = ob_get_contents();
        ob_end_clean();

        if ($error !== null) {
            throw $error;
        }

        if ($template->hasLayout()) {
            $layout = $template->getLayout();
            $context[$this->getBodyId($layout)] = $content;
            $content = $this->render($layout, $context);
        }

        return $content;
    }

    protected function createTemplate(string $moniker, array $context): Template
    {
        return new Template($this, $moniker, $context);
    }

    public function getBodyId(string $moniker): string
    {
        return hash('xxh3', $moniker);
    }

    protected function getPath(string $template): string
    {
        $segments = explode(':', $template);

        [$namespace, $file] = match (count($segments)) {
            1 => [null, $segments[0]],
            2 => [$segments[0], $segments[1]],
            default => throw new InvalidTemplateFormat(
                "Invalid template format: '$template'. " .
                    "Use 'namespace:template/path or template/path'."
            ),
        };

        $file = trim(strtr($file, '\\', '/'), '/');
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

        throw new TemplateNotFound("Template '$template' not found");
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

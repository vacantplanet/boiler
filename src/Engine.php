<?php

declare(strict_types=1);

namespace Conia\Boiler;

use \ValueError;
use Conia\Boiler\Error\{
    DirectoryNotFound,
    InvalidTemplateFormat,
    TemplateNotFound,
};


class Engine
{
    use RegistersMethod;

    protected readonly array $dirs;

    public function __construct(
        string|array $dirs,
        protected readonly array $defaults = [],
        protected readonly bool $autoescape = true,
    ) {
        $this->dirs = $this->prepareDirs($dirs);
        $this->customMethods = new CustomMethods();
    }

    protected function prepareDirs(string|array $dirs): array
    {
        if (is_string($dirs)) {
            return [realpath($dirs) ?: throw new DirectoryNotFound('Directory does not exist ' . $dirs)];
        }

        return array_map(
            fn ($dir) => realpath($dir) ?: throw new DirectoryNotFound('Directory does not exist ' . $dir),
            $dirs
        );
    }

    public function template(string $path): Template
    {
        if (empty($path)) {
            throw new ValueError('No template path given');
        }

        $template = new Template($this->getFile($path), new Sections(), $this);
        $template->setCustomMethods($this->customMethods);

        return $template;
    }

    public function render(
        string $path,
        array $context = [],
        ?bool $autoescape = null
    ): string {
        if (is_null($autoescape)) {
            // Use the engine's default value if nothing is passed
            $autoescape = $this->autoescape;
        }

        $template = $this->template($path);

        return $template->render(array_merge($this->defaults, $context), $autoescape);
    }

    protected function validateFile(string $dir, string $file): string|false
    {
        $path = $dir . DIRECTORY_SEPARATOR . str_replace(['\\', '/'], DIRECTORY_SEPARATOR, $file);
        $realpath = realpath($path);

        if ($realpath) {
            return $realpath;
        }

        if (empty(pathinfo($path, PATHINFO_EXTENSION))) {
            $ext = '.php';

            return realpath($path . $ext);
        }

        return false;
    }

    public function getFile(string $path): string
    {
        if (strpos($path, ':') === false) {
            $namespace = null;
            $file = $path;
        } else {
            $segments = explode(':', $path);
            if (count($segments) == 2) {
                [$namespace, $file] = [$segments[0], $segments[1]];
            } else {
                throw new InvalidTemplateFormat(
                    "Invalid template format: '$path'. " .
                        "Use 'namespace:template/path or template/path'."
                );
            }
        }

        if ($namespace) {
            $dir = $this->dirs[$namespace];
            $templatePath = $this->validateFile($this->dirs[$namespace], $file);
        } else {
            $templatePath = false;

            foreach ($this->dirs as $dir) {
                $templatePath = $this->validateFile($dir, $file);

                if ($templatePath) {
                    break;
                }
            }
        }

        if (isset($dir) && $templatePath && is_file($templatePath)) {
            if (!str_starts_with($templatePath, $dir)) {
                throw new TemplateNotFound(
                    'Template is outside of root directory: ' . $templatePath
                );
            }

            return $templatePath;
        }

        throw new TemplateNotFound("Template '$path' not found");
    }

    public function exists(string $path): bool
    {
        try {
            $this->getFile($path);
            return true;
        } catch (TemplateNotFound) {
            return false;
        }
    }
}

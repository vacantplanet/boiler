<?php

declare(strict_types=1);

namespace Conia\Boiler;

use Conia\Boiler\Error\LookupException;
use Conia\Boiler\Error\UnexpectedValueException;

/**
 * @psalm-type DirsInput = non-empty-string|list<non-empty-string>|array<non-empty-string, non-empty-string>
 * @psalm-type Dirs = list<string>|array<non-empty-string, non-empty-string>
 */
class Engine
{
    use RegistersMethod;

    /** @var Dirs */
    protected readonly array $dirs;

    /**
     * @psalm-param DirsInput $dirs
     */
    public function __construct(
        string|array $dirs,
        protected readonly array $defaults = [],
        protected readonly bool $autoescape = true,
    ) {
        $this->dirs = $this->prepareDirs($dirs);
        $this->customMethods = new CustomMethods();
    }

    /**
     * @psalm-param DirsInput $dirs
     * @return Dirs
     */
    protected function prepareDirs(string|array $dirs): array
    {
        if (is_string($dirs)) {
            return [realpath($dirs) ?: throw new LookupException('Template directory does not exist ' . $dirs)];
        }

        return array_map(
            fn ($dir) => realpath($dir) ?: throw new LookupException('Template directory does not exist ' . $dir),
            $dirs
        );
    }

    /**
     * @psalm-param non-empty-string|Dirs $dirs
     */
    public function template(string $path): Template
    {
        if (empty($path)) {
            throw new UnexpectedValueException('No template path given');
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
        $path = $dir . DIRECTORY_SEPARATOR . $file;

        if ($realpath = realpath($path . '.php')) {
            return $realpath;
        }

        return realpath($path);
    }

    /** @return list{non-empty-string|null, non-empty-string} */
    protected function getSegments(string $path): array
    {
        if (strpos($path, ':') === false) {
            $path = trim($path);
            assert(!empty($path));

            return [null, $path];
        } else {
            $segments = array_map(fn ($s) => trim($s), explode(':', $path));

            if (count($segments) == 2) {

                if (!empty($segments[0]) && !empty($segments[1])) {
                    return [$segments[0], $segments[1]];
                }

                throw new LookupException(
                    "Invalid template format: '$path'. " .
                        "Use 'namespace:template/path or template/path'."
                );
            } else {
                throw new LookupException(
                    "Invalid template format: '$path'. " .
                        "Use 'namespace:template/path or template/path'."
                );
            }
        }
    }

    /**
     * @psalm-param non-empty-string $path
     * @psalm-return non-empty-string
     */
    public function getFile(string $path): string
    {
        [$namespace, $file] = $this->getSegments($path);

        if ($namespace) {
            $dir = $this->dirs[$namespace] ?? '';
            $templatePath = $this->validateFile($this->dirs[$namespace], $file);
        } else {
            $templatePath = false;

            foreach ($this->dirs as $dir) {
                if ($templatePath = $this->validateFile($dir, $file)) {
                    break;
                }
            }
        }

        if (isset($dir) && $templatePath && is_file($templatePath)) {
            if (!str_starts_with($templatePath, (string)$dir)) {
                throw new LookupException(
                    'Template resides outside of root directory: ' . $templatePath
                );
            }

            return $templatePath;
        }

        throw new LookupException("Template not found: " . $path);
    }

    /**
     * @psalm-param non-empty-string $path
     */
    public function exists(string $path): bool
    {
        try {
            $this->getFile($path);
            return true;
        } catch (LookupException) {
            return false;
        }
    }
}

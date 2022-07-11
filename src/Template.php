<?php

declare(strict_types=1);

namespace Conia\Boiler;

use \RuntimeException;
use \Throwable;
use Conia\Boiler\Error\TemplateNotFound;


class Template
{
    use RegistersMethod;

    protected ?string $layout = null;
    public readonly Sections $sections;
    /** @psalm-suppress PropertyNotSetInConstructor */
    protected CustomMethods $customMethods;

    public function __construct(
        public readonly string $path,
        ?Sections $sections = null,
        public readonly ?Engine $engine = null,
    ) {
        $this->sections = $sections ?: new Sections();
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
            $path .= '.php';
        }

        $includePath = realpath(dirname($this->path) . DIRECTORY_SEPARATOR . $path);

        if ($includePath) {
            return $includePath;
        }

        throw new TemplateNotFound('Included template not found: ' . $path);
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
            /**
             * Psalm reports that $template->layout is possibly null
             * which is already checked via $template->hasLayout().
             *
             * @psalm-suppress PossiblyNullArgument
             * */
            $template = new Layout(
                $template->getIncludePath($template->layout),
                $content,
                $this->sections,
                $template->engine,
            );
            $content = $template->render($context, $autoescape);
        }

        return $content;
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

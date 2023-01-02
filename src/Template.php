<?php

declare(strict_types=1);

namespace Conia\Boiler;

use \Throwable;
use Conia\Boiler\Error\LookupException;
use Conia\Boiler\Error\RuntimeException;


class Template
{
    use RegistersMethod;

    public readonly Engine $engine;
    public readonly Sections $sections;
    protected ?LayoutValue $layout = null;
    /** @psalm-suppress PropertyNotSetInConstructor */
    protected CustomMethods $customMethods;

    /**
     * @psalm-param non-empty-string $path
     */
    public function __construct(
        public readonly string $path,
        ?Sections $sections = null,
        ?Engine $engine = null,
    ) {
        $this->sections = $sections ?: new Sections();
        $this->customMethods = new CustomMethods();

        if ($engine === null) {
            $this->engine = new Engine(dirname($path));

            if (!is_file($path)) {
                throw new LookupException('Template not found: ' . $path);
            }
        } else {
            $this->engine = $engine;
        }
    }

    protected function templateContext(array $context, bool $autoescape): TemplateContext
    {
        return new TemplateContext($this, $context, $autoescape);
    }

    protected function getContent(array $context, bool $autoescape): string
    {
        $templateContext = $this->templateContext($context, $autoescape);

        $load =  function (string $templatePath, array $context = []): void {
            // Hide $templatePath. Could be overwritten if $context['templatePath'] exists.
            $____template_path____ = $templatePath;

            extract($context);

            /** @psalm-suppress UnresolvableInclude */
            include $____template_path____;
        };

        /** @var callable */
        $load = $load->bindTo($templateContext);
        $level = ob_get_level();

        try {
            ob_start();

            $load(
                $this->path,
                $autoescape ?
                    $templateContext->context() :
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

    protected function renderLayouts(
        Template $template,
        array $context,
        string $content,
        bool $autoescape
    ): string {
        while ($layout = $template->layout()) {
            $file = $template->engine->getFile($layout->layout);
            $template = new Layout(
                $file,
                $content,
                $this->sections,
                $template->engine,
            );

            $content = $template->render($layout->context ?: $context, $autoescape);
        }

        return $content;
    }

    public function render(array $context = [], bool $autoescape = true): string
    {
        $content = $this->getContent($context, $autoescape);

        if ($this instanceof Layout) {
            return $content;
        }

        return $this->renderLayouts($this, $context, $content, $autoescape);
    }

    /**
     * Defines a layout template that will be wrapped around this instance.
     *
     * Typically it’s placed at the top of the file.
     */
    public function setLayout(LayoutValue $layout): void
    {
        if ($this->layout === null) {
            $this->layout = $layout;

            return;
        } else {
            throw new RuntimeException('Template error: layout already set');
        }
    }

    public function layout(): ?LayoutValue
    {
        return $this->layout;
    }

    public function setCustomMethods(CustomMethods $customMethods): void
    {
        $this->customMethods = $customMethods;
    }
}

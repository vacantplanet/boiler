<?php

declare(strict_types=1);

namespace Conia\Boiler;

class Layout extends Template
{
    /**
     * @psalm-param non-empty-string $path
     */
    public function __construct(
        string $path,
        protected readonly string $body,
        Sections $sections,
        ?Engine $engine = null,
    ) {
        parent::__construct($path, $sections, $engine);
    }

    /**
     * Used in the layout template to get the content of the wrapped template.
     */
    public function body(): string
    {
        return $this->body;
    }

    protected function templateContext(array $context, bool $autoescape): LayoutContext
    {
        return new LayoutContext($this, $context, $autoescape);
    }
}

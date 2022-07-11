<?php

declare(strict_types=1);

namespace Conia\Boiler;


class Layout extends Template
{
    public function __construct(
        string $path,
        protected readonly string $body,
        ?Engine $engine = null,
    ) {
        parent::__construct($path, $engine);
    }

    protected function boundTemplate(array $context, bool $autoescape): BoundLayout
    {
        return new BoundLayout($this, $context, $autoescape);
    }

    /**
     * Used in the layout template to get the content of the wrapped template
     */
    public function body(): string
    {
        return $this->body;
    }
}

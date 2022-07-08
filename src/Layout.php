<?php

declare(strict_types=1);

namespace Conia\Boiler;


class Layout extends Template
{
    public function __construct(
        Engine $engine,
        string $path,
        array $context,
        protected readonly string $body,
    ) {
        parent::__construct($engine, $path, $context);
    }

    /**
     * Used in the layout template to get the content of the wrapped template
     */
    public function body(): string
    {
        return $this->body;
    }
}

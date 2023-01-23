<?php

declare(strict_types=1);

namespace Conia\Boiler;

class LayoutContext extends TemplateContext
{
    protected Layout $layout;

    public function __construct(
        Layout $template,
        array $context,
        array $whitelist,
        bool $autoescape,
    ) {
        parent::__construct($template, $context, $whitelist, $autoescape);
        $this->layout = $template;
    }

    public function body(): string
    {
        return $this->layout->body();
    }
}

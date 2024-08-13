<?php

declare(strict_types=1);

namespace VacantPlanet\Boiler;

class Content
{
    public function __construct(
        public readonly string $content,
        public readonly TemplateContext $templateContext,
    ) {
    }
}

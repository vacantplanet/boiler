<?php

declare(strict_types=1);

namespace Conia\Boiler;


class LayoutValue
{
    public function __construct(
        public readonly string $layout,
        public readonly ?array $context = null
    ) {
    }
}

<?php

declare(strict_types=1);

namespace Conia\Boiler;

use Conia\Boiler\Error\MethodNotAllowed;


class TextTemplateContext extends TemplateContext
{
    public function layout(string $path): void
    {
        throw new MethodNotAllowed('Layouts are not allowed in text templates');
    }

    public function insert(string $path, array $context = []): void
    {
        throw new MethodNotAllowed('Inserts are not allowed in text templates');
    }

    public function begin(string $name): void
    {
        throw new MethodNotAllowed('Sections are not allowed in text templates');
    }

    public function append(string $name): void
    {
        throw new MethodNotAllowed('Sections are not allowed in text templates');
    }

    public function prepend(string $name): void
    {
        throw new MethodNotAllowed('Sections are not allowed in text templates');
    }

    public function end(): void
    {
        throw new MethodNotAllowed('Sections are not allowed in text templates');
    }

    public function section(string $name): string
    {
        throw new MethodNotAllowed('Sections are not allowed in text templates');
    }

    public function has(string $name): bool
    {
        throw new MethodNotAllowed('Sections are not allowed in text templates');
    }
}

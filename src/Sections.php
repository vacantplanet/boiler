<?php

declare(strict_types=1);

namespace Conia\Boiler;

use Conia\Boiler\Error\LogicException;

class Sections
{
    /** @var array<string, string> */
    protected array $sections = [];
    protected array $capture = [];
    protected SectionMode $sectionMode = SectionMode::Closed;

    protected function open(string $name, SectionMode $mode): void
    {
        if ($this->sectionMode !== SectionMode::Closed) {
            throw new LogicException('Nested sections are not allowed');
        }

        $this->sectionMode = $mode;
        $this->capture[] = $name;
        ob_start();
    }

    public function begin(string $name): void
    {
        $this->open($name, SectionMode::Assign);
    }

    public function append(string $name): void
    {
        $this->open($name, SectionMode::Append);
    }

    public function prepend(string $name): void
    {
        $this->open($name, SectionMode::Prepend);
    }

    public function end(): void
    {
        $content = ob_get_clean();
        $name = (string)array_pop($this->capture);

        $this->sections[$name] = match ($this->sectionMode) {
            SectionMode::Assign => $content,
            SectionMode::Append => ($this->sections[$name] ?? '') . $content,
            SectionMode::Prepend => $content . ($this->sections[$name] ?? ''),
            SectionMode::Closed => throw new LogicException('No section started'),
        };

        $this->sectionMode = SectionMode::Closed;
    }

    public function get(string $name): string
    {
        return $this->sections[$name];
    }

    public function has(string $name): bool
    {
        return isset($this->sections[$name]);
    }
}

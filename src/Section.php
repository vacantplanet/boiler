<?php

declare(strict_types=1);

namespace VacantPlanet\Boiler;

class Section
{
    /** @var list<string> */
    protected array $prepended = [];

    /** @var list<string> */
    protected array $appended = [];

    public function __construct(protected string $value) {}

    public function prepend(string $content): self
    {
        $this->prepended[] = $content;

        return $this;
    }

    public function append(string $content): self
    {
        array_unshift($this->appended, $content);

        return $this;
    }

    public function empty(): bool
    {
        error_log(print_r($this->value, true));

        return empty($this->value);
    }

    public function get(): string
    {
        return implode('', array_merge($this->prepended, [$this->value], $this->appended));
    }

    public function setValue(string $value): void
    {
        $this->value = $value;
    }
}

<?php

declare(strict_types=1);

namespace VacantPlanet\Boiler;

use VacantPlanet\Boiler\Exception\UnexpectedValueException;

class CustomMethods
{
    /** @psalm-var array<non-empty-string, callable> */
    protected array $methods = [];

    /** @psalm-param non-empty-string $name */
    public function add(string $name, callable $callable): void
    {
        $this->methods[$name] = $callable;
    }

    public function get(string $name): callable
    {
        return array_key_exists($name, $this->methods) ?
            $this->methods[$name] :
            throw new UnexpectedValueException("Custom method '{$name}' does not exist");
    }
}

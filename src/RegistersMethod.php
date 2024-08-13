<?php

declare(strict_types=1);

namespace VacantPlanet\Boiler;

trait RegistersMethod
{
    protected CustomMethods $customMethods;

    /** @psalm-param non-empty-string $name */
    public function registerMethod(string $name, callable $callable): void
    {
        $this->customMethods->add($name, $callable);
    }

    public function getMethods(): CustomMethods
    {
        return $this->customMethods;
    }
}

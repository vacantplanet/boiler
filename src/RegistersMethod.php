<?php

declare(strict_types=1);

namespace Conia\Boiler;


trait RegistersMethod
{
    protected CustomMethods $customMethods;

    public function registerMethod(string $name, callable $callable): void
    {
        $this->customMethods->add($name, $callable);
    }

    public function getMethods(): CustomMethods
    {
        return $this->customMethods;
    }
}

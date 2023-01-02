<?php

declare(strict_types=1);

namespace Conia\Boiler;

use Conia\Boiler\Error\UnexpectedValueException;


class CustomMethods
{
    protected array $methods = [];

    public function add(string $name, callable $callable): void
    {
        $this->methods[$name] = $callable;
    }

    public function get(string $name): callable
    {
        return array_key_exists($name, $this->methods) ?
            $this->methods[$name] :
            throw new UnexpectedValueException("Custom method '$name' does not exist");
    }
}

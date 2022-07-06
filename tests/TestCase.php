<?php

declare(strict_types=1);

namespace Conia\Boiler\Tests;

use PHPUnit\Framework\TestCase as BaseTestCase;


class TestCase extends BaseTestCase
{
    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
    }

    public function fulltrim(string $text): string
    {
        return trim(
            preg_replace(
                '/> </',
                '><',
                preg_replace(
                    '/\s+/',
                    ' ',
                    preg_replace('/\n/', '', $text)
                )
            )
        );
    }

    public function templates(array $templates = []): array
    {
        return array_merge($templates, [
            __DIR__ . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . 'default',
        ]);
    }

    public function namespaced(array $templates = []): array
    {
        return array_merge($templates, [
            'namespace' => __DIR__ . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . 'default',
        ]);
    }

    public function additional(): array
    {
        return  [
            'additional' => __DIR__ . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . 'additional',
        ];
    }

    public function obj(): object
    {
        return new class()
        {
            public function name(): string
            {
                return 'boiler';
            }
        };
    }
}

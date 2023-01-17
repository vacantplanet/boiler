<?php

declare(strict_types=1);

namespace Conia\Boiler\Tests;

use Conia\Chuck\Psr\Nyholm;
use PHPUnit\Framework\TestCase as BaseTestCase;

/**
 * @internal
 *
 * @coversNothing
 */
class TestCase extends BaseTestCase
{
    public const ROOT_DIR = __DIR__ . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR;
    public const DEFAULT_DIR = self::ROOT_DIR . 'default';
    public const DS = DIRECTORY_SEPARATOR;

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

    public function factory(): Nyholm
    {
        return new Nyholm();
    }

    public function templates(array $templates = []): array
    {
        return array_merge($templates, [self::DEFAULT_DIR]);
    }

    public function namespaced(array $templates = []): array
    {
        return array_merge($templates, [
            'namespace' => self::DEFAULT_DIR,
        ]);
    }

    public function additional(): array
    {
        return [
            'additional' => self::ROOT_DIR . 'additional',
        ];
    }

    public function obj(): object
    {
        return new class () {
            public function name(): string
            {
                return 'boiler';
            }
        };
    }
}

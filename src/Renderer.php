<?php

declare(strict_types=1);

namespace Conia\Boiler;

use Conia\Boiler\Engine;
use Conia\Boiler\Exception\RendererException;
use Conia\Chuck\Factory;
use Conia\Chuck\Renderer\Renderer as RendererInterface;
use Conia\Chuck\Response;
use Throwable;
use Traversable;

/**
 * @psalm-api
 *
 * @psalm-import-type DirsInput from \Conia\Boiler\Engine
 */
class Renderer implements RendererInterface
{
    /**
     * @psalm-param DirsInput $dirs
     * @psalm-param list<class-string> $whitelist
     */
    public function __construct(
        protected Factory $factory,
        protected string|array $dirs,
        protected array $defaults = [],
        protected array $whitelist = [],
        protected bool $autoescape = true,
    ) {
    }

    public function render(mixed $data, mixed ...$args): string
    {
        if ($data instanceof Traversable) {
            $context = iterator_to_array($data);
        } elseif (is_array($data)) {
            $context = $data;
        } else {
            throw new RendererException('The template context must be an array or a Traversable');
        }

        try {
            $templateName = (string)$args[0];
            assert(!empty($templateName));
        } catch (Throwable) {
            throw new RendererException('The template must be passed to the renderer');
        }

        if (is_string($this->dirs)) {
            $this->dirs = [$this->dirs];
        } else {
            if (count($this->dirs) === 0) {
                throw new RendererException('T');
            }
        }

        $engine = $this->createEngine($this->dirs);

        return $engine->render($templateName, $context);
    }

    public function response(mixed $data, mixed ...$args): Response
    {
        $response = Response::fromFactory($this->factory)->status(
            (int)($args['statusCode'] ?? 200),
            (string)($args['reasonPhrase'] ?? ''),
        );

        return $response
            ->header('Content-Type', (string)(($args['contentType'] ?? null) ?: 'text/html'))
            ->body($this->render($data, ...$args));
    }

    /** @psalm-param DirsInput $dirs */
    protected function createEngine(string|array $dirs): Engine
    {
        return new Engine($dirs, $this->defaults, $this->whitelist, $this->autoescape);
    }
}

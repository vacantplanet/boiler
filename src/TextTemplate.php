<?php

declare(strict_types=1);

namespace Conia\Boiler;


class TextTemplate extends Template
{
    protected mixed $tmpFile = false;

    public function __construct(string $code)
    {
        $this->tmpFile = tmpfile();
        fwrite($this->tmpFile, $code);
        $path = stream_get_meta_data($this->tmpFile)['uri'];

        parent::__construct($path);
    }

    public function __destruct()
    {
        if ($this->tmpFile) {
            fclose($this->tmpFile);
        }
    }

    protected function templateContext(array $context, bool $autoescape): TextTemplateContext
    {
        return new TextTemplateContext($this, $context, $autoescape);
    }
}

<?php

declare(strict_types=1);

namespace VacantPlanet\Boiler;

use Symfony\Component\HtmlSanitizer\HtmlSanitizer;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;

class Sanitizer
{
	protected HtmlSanitizer $sanitizer;

	public function __construct(?HtmlSanitizerConfig $config = null)
	{
		$config = $config ?: (new HtmlSanitizerConfig())
			// Allow "safe" elements and attributes. All scripts will be removed
			// as well as other dangerous behaviors like CSS injection
			->allowSafeElements();

		$this->sanitizer = new HtmlSanitizer($config);
	}

	public function clean(string $html): string
	{
		return $this->sanitizer->sanitize($html);
	}
}

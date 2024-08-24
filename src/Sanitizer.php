<?php

declare(strict_types=1);

namespace VacantPlanet\Boiler;

use Symfony\Component\HtmlSanitizer\HtmlSanitizer;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;

class Sanitizer
{
	public static function clean(
		string $html,
		?HtmlSanitizerConfig $config = null,
	): string {
		$config = $config ?: (new HtmlSanitizerConfig())
			// Allow "safe" elements and attributes. All scripts will be removed
			// as well as other dangerous behaviors like CSS injection
			->allowSafeElements();
		$sanitizer = new HtmlSanitizer($config);

		return $sanitizer->sanitize($html);
	}
}

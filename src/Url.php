<?php

declare(strict_types=1);

namespace VacantPlanet\Boiler;

use VacantPlanet\Boiler\Exception\RuntimeException;

class Url
{
	public static function clean(string $url): string
	{
		$parsed = parse_url($url);

		if ($parsed === false) {
			throw new RuntimeException('Invalid Url');
		}

		$path = self::empty($parsed, 'scheme') ? '' : ($parsed['scheme'] ?? '') . '://';
		$path .= implode('@', array_map('rawurlencode', explode('@', $parsed['user'] ?? '')));
		$path .= rawurlencode(self::empty($parsed, 'pass') ? '' : ':' . ($parsed['pass'] ?? ''));
		$path .= !self::empty($parsed, 'user') || !self::empty($parsed, 'pass') ? '@' : '';
		$path .= $parsed['host'] ?? '';
		$path .= self::empty($parsed, 'port') ? '' : ':' . ($parsed['port'] ?? '');

		$segments = [];

		foreach (explode('/', $parsed['path'] ?? '') as $segment) {
			$segments[] = urlencode($segment);
		}

		$path .= implode('/', $segments);
		$query = '';

		if (!self::empty($parsed, 'query')) {
			parse_str($parsed['query'] ?? '', $array);

			if (count($array) > 0) {
				$query .= '?' . http_build_query($array);
			}
		}

		$query .= self::empty($parsed, 'fragment') ? '' : '#' . rawurlencode($parsed['fragment'] ?? '');

		return $path . $query;
	}

	protected static function empty(array $url, string $key): bool
	{
		return strlen((string) ($url[$key] ?? '')) === 0;
	}
}

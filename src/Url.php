<?php

declare(strict_types=1);

namespace Conia\Boiler;

use Conia\Boiler\Error\RuntimeException;

class URL
{
    public static function clean(string $url): string
    {
        $parsed = parse_url($url);

        if (!$parsed) {
            throw new RuntimeException('Invalid Url');
        }

        $path = '';
        $query = '';

        $path = empty($parsed['scheme']) ? '' : $parsed['scheme'] . '://';
        $path .= rawurlencode($parsed['user'] ?? '');
        $path .= rawurlencode(empty($parsed['pass']) ? '' : ':' . $parsed['pass']);
        $path .= !empty($parsed['pass']) || !empty($parsed['pass']) ? '@' : '';
        $path .= $parsed['host'] ?? '';
        $path .= empty($parsed['port']) ? '' : ':' . $parsed['port'];

        $segments = [];

        foreach (explode('/', $parsed['path'] ?? '') as $segment) {
            $segments[] = urlencode($segment);
        }

        $path .= implode('/', $segments);

        if (!empty($parsed['query'])) {
            parse_str($parsed['query'], $array);

            if (count($array) > 0) {
                $query .= empty($parsed['query']) ? '' : '?' . http_build_query($array);
            }
        }

        $query .= empty($parsed['fragment']) ? '' : '#' . rawurlencode($parsed['fragment']);

        return filter_var($path . $query, FILTER_SANITIZE_URL);
    }
}

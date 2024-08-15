<?php

declare(strict_types=1);

use VacantPlanet\Boiler\Exception\RuntimeException;
use VacantPlanet\Boiler\Url;

test('All segments', function () {
    $url = Url::clean(
        "http://user :pss@example.com:81/mypath/   " .
        " myfile.html?a=\"chuck schuldiner\"&b[]=2&b[]\n\n\n=3&z=666#symbolic",
    );

    expect($url)->toBe(
        'http://user%20%3Apss@example.com:81/mypath/++++myfile.html' .
        '?a=%22chuck+schuldiner%22&b%5B0%5D=2&b%5B1%5D=3&z=666#symbolic',
    );
});

test('Already clean without anything', function () {
    $url = Url::clean('http://example.com/');

    expect($url)->toBe('http://example.com/');
});

test('Failing', function () {
    Url::clean('scheme://example_login:!#Password?@ZZZ@127.0.0.1/some_path');
})->throws(RuntimeException::class, 'Invalid Url');

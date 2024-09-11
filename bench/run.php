<?php

declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

if (!is_dir('./cache')) mkdir('./cache', 0755, true);

const RUNS = 10000;
$context = [
    'title' => 'Engine',
    'array' => ['string1', 'string2', '<b>string3</b>'],
    'htmlval' => '<p>lorem ipsum</p>'
];



$start = microtime(true);
for ($i = 0; $i < RUNS; $i++) {
    $engine = new League\Plates\Engine('./plates');
    $p = $engine->render('page', $context);
}
$end = microtime(true);
print "Plates:          " . (string)($end - $start) . "\n";


$start = microtime(true);
for ($i = 0; $i < RUNS; $i++) {
    $loader = new \Twig\Loader\FilesystemLoader('./twig');
    $engine = new \Twig\Environment($loader, [
        'cache' => './cache/twig',
    ]);
    $t = $engine->render('page.html', $context);
}
$end = microtime(true);
print "Twig:            " . (string)($end - $start) . "\n";


$start = microtime(true);
for ($i = 0; $i < RUNS; $i++) {
    $engine = new \eftec\bladeone\BladeOne('./bladeone', './cache/bladeone');
    $l = $engine->run('page', $context);
}
$end = microtime(true);
print "BladeOne:        " . (string)($end - $start) . "\n";


$start = microtime(true);
for ($i = 0; $i < RUNS; $i++) {
    $engine = new Conia\Boiler\Engine('./boiler', autoescape: false);
    $e = $engine->render('pagenoescape', $context);
}
$end = microtime(true);
print "Boiler noescape: " . (string)($end - $start) . "\n";


$start = microtime(true);
for ($i = 0; $i < RUNS; $i++) {
    $engine = new Conia\Boiler\Engine('./boiler');
    $b = $engine->render('page', $context);
}
$end = microtime(true);
print "Boiler:          " . (string)($end - $start) . "\n";

$b = fulltrim($b);
$l = fulltrim($l);
$e = fulltrim($e);
$p = fulltrim($p);
$t = fulltrim($t);
assert($b === $p);
assert($b === $t);
assert($b === $e);
assert($b === $l);
// echo ('' . PHP_EOL);
// echo ('---- BladeOne' . PHP_EOL);
// echo ($l . PHP_EOL);
// echo ('' . PHP_EOL);
// echo ('---- Plates' . PHP_EOL);
// echo ($p . PHP_EOL);
// echo ('' . PHP_EOL);
// echo ('---- Twig' . PHP_EOL);
// echo ($t . PHP_EOL);
// echo ('' . PHP_EOL);
// echo ('---- Boiler (noescape)' . PHP_EOL);
// echo ($e . PHP_EOL);
// echo ('' . PHP_EOL);
// echo ('---- Boiler' . PHP_EOL);
// echo ($b . PHP_EOL);

function fulltrim(string $text): string
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

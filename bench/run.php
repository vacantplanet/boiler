<?php

declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

if (!is_dir('./cache')) {
    mkdir('./cache', 0755, true);
}
if (!is_dir('./cache/bladeone')) {
    mkdir('./cache/bladeone', 0755, true);
}

const SHOWTEXT = false;
const RUNS = 10000;
const CONTEXT = [
    'title' => 'Engine',
    'array' => ['string1', 'string2', '<b>string3</b>'],
    'htmlval' => '<p>lorem ipsum</p>'
];


function benchTwig(): string
{
    $start = microtime(true);

    for ($i = 0; $i < RUNS; $i++) {
        $loader = new \Twig\Loader\FilesystemLoader('./twig');
        $engine = new \Twig\Environment($loader, [
            'cache' => './cache/twig',
        ]);
        $t = $engine->render('page.html', CONTEXT);
    }

    $end = microtime(true);
    print "Twig:      " . (string)($end - $start) . "\n";

    return fulltrim($t);
}


function benchBladeOne(): string
{
    $start = microtime(true);

    for ($i = 0; $i < RUNS; $i++) {
        $engine = new \eftec\bladeone\BladeOne('./bladeone', './cache/bladeone');
        $t = $engine->run('page', CONTEXT);
    }

    $end = microtime(true);
    print "BladeOne:  " . (string)($end - $start) . "\n";

    return fulltrim($t);
}


function benchBoiler(): string
{
    $start = microtime(true);

    for ($i = 0; $i < RUNS; $i++) {
        $engine = VacantPlanet\Boiler\Engine::create('./boiler');
        $t = $engine->render('page', CONTEXT);
    }

    $end = microtime(true);
    print "Boiler:    " . (string)($end - $start) . "\n";

    return fulltrim($t);
}

function benchPlates(): string
{
    $start = microtime(true);

    for ($i = 0; $i < RUNS; $i++) {
        $engine = new League\Plates\Engine('./plates');
        $t = $engine->render('page', CONTEXT);
    }

    $end = microtime(true);
    print "Plates:    " . (string)($end - $start) . "\n";

    return fulltrim($t);
}


function benchBoilerUnescaped(): string
{
    $start = microtime(true);

    for ($i = 0; $i < RUNS; $i++) {
        $engine = VacantPlanet\Boiler\Engine::unescaped('./boiler');
        $t = $engine->render('pagenoescape', CONTEXT);
    }

    $end = microtime(true);
    print "Boiler:    " . (string)($end - $start) . "\n";

    return fulltrim($t);
}

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

function main()
{
    echo '---- ESCAPED ----' . PHP_EOL;
    $e = benchTwig();
    $p = benchBladeOne();
    $t = benchBoiler();

    echo "\n--- UNESCAPED ---\n";
    $b = benchPlates();
    $l = benchBoilerUnescaped();
    assert($b === $p);
    assert($b === $t);
    assert($b === $e);
    assert($b === $l);

    if (SHOWTEXT) {
        echo('' . PHP_EOL);
        echo('---- BladeOne' . PHP_EOL);
        echo($l . PHP_EOL);
        echo('' . PHP_EOL);
        echo('---- Plates' . PHP_EOL);
        echo($p . PHP_EOL);
        echo('' . PHP_EOL);
        echo('---- Twig' . PHP_EOL);
        echo($t . PHP_EOL);
        echo('' . PHP_EOL);
        echo('---- Boiler (noescape)' . PHP_EOL);
        echo($e . PHP_EOL);
        echo('' . PHP_EOL);
        echo('---- Boiler' . PHP_EOL);
        echo($b . PHP_EOL);
    }
}

main();

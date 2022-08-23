<?php

$config = (new PhpCsFixer\Config())
    ->setRiskyAllowed(true)
    ->setRules([
        '@PSR2' => true,
        '@PHP80Migration' => true,
        '@PHP80Migration:risky' => true,
        '@PHPUnit84Migration:risky' => true,
        '@Symfony' => true,
        '@Symfony:risky' => true,
        'yoda_style' => false,
    ])
    ->setFinder(
        PhpCsFixer\Finder::create()
            ->in(__DIR__ . '/src')
            ->in(__DIR__ . '/tests')
            ->name('*.php')
    );

return $config;

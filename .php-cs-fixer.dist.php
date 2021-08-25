<?php

$finder = PhpCsFixer\Finder::create()
    ->in([__DIR__ . '/jds-demo-plugin/src/', __DIR__ . '/jds-demo-plugin/tests/unit/']);

$config = new PhpCsFixer\Config();
return $config->setRules([
    '@PSR12' => true,
    'array_syntax' => ['syntax' => 'short'],
])
    ->setFinder($finder);

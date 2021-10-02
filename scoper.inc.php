<?php

declare(strict_types=1);

require_once 'scoper-fixes/import.php';

use Isolated\Symfony\Component\Finder\Finder;

$polyfillsBootstraps = array_keys(iterator_to_array(Finder::create()
    ->files()
    ->in('./jds-demo-plugin/vendor/symfony/polyfill-*')
    ->name('bootstrap.php')));

$polyfillsStubs = array_keys(iterator_to_array(Finder::create()
    ->files()
    ->in('./jds-demo-plugin/vendor/symfony/polyfill-*/Resources/stubs')
    ->name('*.php')));


return [
    // The prefix configuration. If a non null value will be used, a random prefix will be generated.
    'prefix' => 'JdsDesigns\Release',

    // By default, when running php-scoper add-prefix, it will prefix all relevant code found in the current working
    // directory. You can however define which files should be scoped by defining a collection of Finders in the
    // following configuration key.
    //
    // For more see: https://github.com/humbug/php-scoper#finders-and-paths
    'finders' => [
        Finder::create()->files()->in(['./jds-demo-plugin/src',
            './jds-demo-plugin/tasks',
            './jds-demo-plugin/languages',
            './jds-demo-plugin/templates',
            './jds-demo-plugin/logs',
            './jds-demo-plugin/db'
        ])->notName(['*.log']),
        Finder::create()->files()->in('./jds-demo-plugin')->name(['jds-demo-plugin.php', 'phinx.wordpress.php']),
        Finder::create()
            ->files()
            ->ignoreVCS(true)
            ->notName('/LICENSE|.*\\.md|.*\\.dist|Makefile|composer\\.json|composer\\.lock/')
            ->exclude([
                'doc',
                'test',
                'test_old',
                'tests',
                'Tests',
                'vendor-bin',
            ])
            ->in('./jds-demo-plugin/vendor'),
        Finder::create()->append([
            './jds-demo-plugin/composer.json',
        ]),
    ],

    // Whitelists a list of files. Unlike the other whitelist related features, this one is about completely leaving
    // a file untouched.
    // Paths are relative to the configuration file unless if they are already absolute
    'files-whitelist' => [
        ...$polyfillsBootstraps,
        ...$polyfillsStubs,
        './jds-demo-plugin/vendor/php-di/php-di/src/Compiler/Template.php',
        './jds-demo-plugin/phinx.php'
    ],

    // When scoping PHP files, there will be scenarios where some of the code being scoped indirectly references the
    // original namespace. These will include, for example, strings or string manipulations. PHP-Scoper has limited
    // support for prefixing such strings. To circumvent that, you can define patchers to manipulate the file to your
    // heart contents.
    //
    // For more see: https://github.com/humbug/php-scoper#patchers
    'patchers' => [
        JdsDemoPlugin\Patchers\Twig\twigPatcher(),
        JdsDemoPlugin\Patchers\RootNamespace\rootNamespacePatcher(),
    ],

    // PHP-Scoper's goal is to make sure that all code for a project lies in a distinct PHP namespace. However, you
    // may want to share a common API between the bundled code of your PHAR and the consumer code. For example if
    // you have a PHPUnit PHAR with isolated code, you still want the PHAR to be able to understand the
    // PHPUnit\Framework\TestCase class.
    //
    // A way to achieve this is by specifying a list of classes to not prefix with the following configuration key. Note
    // that this does not work with functions or constants neither with classes belonging to the global namespace.
    //
    // Fore more see https://github.com/humbug/php-scoper#whitelist
    'whitelist' => [
        'Symfony\\Polyfill\\*',
    ],
    'whitelist-global-constants' => true,
    'whitelist-global-classes' => true,
    'whitelist-global-functions' => true,
];

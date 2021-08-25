<?php

declare(strict_types=1);

use Isolated\Symfony\Component\Finder\Finder;

return [
    // The prefix configuration. If a non null value will be used, a random prefix will be generated.
    'prefix' => 'JdsDesigns\Release',

    // By default when running php-scoper add-prefix, it will prefix all relevant code found in the current working
    // directory. You can however define which files should be scoped by defining a collection of Finders in the
    // following configuration key.
    //
    // For more see: https://github.com/humbug/php-scoper#finders-and-paths
    'finders' => [
        Finder::create()->files()->in(['./jds-demo-plugin/src',
            './jds-demo-plugin/tasks',
            './jds-demo-plugin/languages',
            './jds-demo-plugin/templates']),
        Finder::create()->files()->in('./jds-demo-plugin')->name(['jsd-demo-plugin.php']),
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
        './jds-demo-plugin/vendor/php-di/php-di/src/Compiler/Template.php'
    ],

    // When scoping PHP files, there will be scenarios where some of the code being scoped indirectly references the
    // original namespace. These will include, for example, strings or string manipulations. PHP-Scoper has limited
    // support for prefixing such strings. To circumvent that, you can define patchers to manipulate the file to your
    // heart contents.
    //
    // For more see: https://github.com/humbug/php-scoper#patchers
    'patchers' => [
        function (string $filePath, string $prefix, string $contents): string {
            try {
                $twigFunctionsReferenceFile = realpath(__DIR__ . '/scoper-fixes/twigFunctions.json');

                if ($twigFunctionsReferenceFile === false) {
                    echo "Unable to determine the twig global function information file path: $twigFunctionsReferenceFile\n\n";
                    die;
                }

                $twigRoot = join(DIRECTORY_SEPARATOR, ['twig', 'twig', 'src']) . DIRECTORY_SEPARATOR;

                // only process twig files
                $isTwigFile = false !== mb_strpos($filePath, $twigRoot);

                if (!$isTwigFile) {
                    return $contents;
                }

                $rawFunctionsFileContents = file_get_contents($twigFunctionsReferenceFile);

                if ($rawFunctionsFileContents === false) {
                    echo "Unable to read twig global function information: $twigFunctionsReferenceFile\n\n";
                    die;
                }

                $segments = explode(DIRECTORY_SEPARATOR, $filePath);
                $fileName = array_pop($segments);

                $twigRootFunctionsLookup = json_decode($rawFunctionsFileContents, true);

                // filter the complete list to only include functions not defined in this particular file
                $twigRootFunctionsList = array_filter(
                    array_keys($twigRootFunctionsLookup),
                    fn (string $func) => $twigRootFunctionsLookup[$func] !== $fileName
                );

                $twigRootReplacements = array_map(
                    fn ($func) => '\\' . $prefix . '\\' . $func,
                    $twigRootFunctionsList
                );

                // replace global functions
                $contents = str_replace($twigRootFunctionsList, $twigRootReplacements, $contents);


                // todo replace string'd functions
                $singleQuoteFunctionStrings = array_map(fn ($func) =>"'$func'", array_keys($twigRootFunctionsLookup));
                $singeQuoteReplacements = array_map(fn ($func) => "'$prefix\\$func'", array_keys($twigRootFunctionsLookup));
                $contents = str_replace($singleQuoteFunctionStrings, $singeQuoteReplacements, $contents);

                // replace compilation strings
                $moduleNode = 'ModuleNode.php';
                if ($moduleNode === $fileName) {
                    $contents = preg_replace_callback(
                        "/\"use (Twig\\\+[^\"]+)\"/",
                        fn (array $matches) => "\"use $prefix\\\\$matches[1]\"",
                        $contents
                    );
                }
            } catch (\Exception $e) {
                echo $e;
            }
            return $contents;
        },
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
        // 'PHPUnit\Framework\TestCase',   // A specific class
        // 'PHPUnit\Framework\*',          // The whole namespace
        // '*',                            // Everything
    ],

    // If `true` then the user defined constants belonging to the global namespace will not be prefixed.
    //
    // For more see https://github.com/humbug/php-scoper#constants--constants--functions-from-the-global-namespace
    'whitelist-global-constants' => true,

    // If `true` then the user defined classes belonging to the global namespace will not be prefixed.
    //
    // For more see https://github.com/humbug/php-scoper#constants--constants--functions-from-the-global-namespace
    'whitelist-global-classes' => true,

    // If `true` then the user defined functions belonging to the global namespace will not be prefixed.
    //
    // For more see https://github.com/humbug/php-scoper#constants--constants--functions-from-the-global-namespace
    'whitelist-global-functions' => true,
];

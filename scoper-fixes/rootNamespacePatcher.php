<?php

namespace JdsDemoPlugin\Patchers\RootNamespace;

use Exception;

const PROJECT_SOURCE_CODE_PATH_PARTIAL = DIRECTORY_SEPARATOR . 'jds-demo-plugin' . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR;

function init(): array
{
    $rootNamespaceFunctionsFile = realpath(__DIR__ . '/rootNamespaceImports.json');
    if ($rootNamespaceFunctionsFile === false) {
        echo "Unable to determine root namespace function information file path: $rootNamespaceFunctionsFile\n\n";
        // bring the world to a halt and fix the build process
        die;
    }
    $rawFunctionsFileContents = file_get_contents($rootNamespaceFunctionsFile);

    if ($rawFunctionsFileContents === false) {
        echo "Unable to read root namespace functions information: $rootNamespaceFunctionsFile\n\n";
        // bring the world to a halt and fix the build process
        die;
    }

    return json_decode($rawFunctionsFileContents, true);
}

/**
 * Factory for root namespace patcher function
 *
 * Returned callable does a simple search for `{prefix}\{someRootThing}` and replaces it
 * with `\{someRootThing}`, where `{someRootThing}` is provided by `rootNamespaceImports.json`.
 *
 * This is necessary because `php-scoper` does not seem to ignore imports from the root namespace
 * even when whitelisted, e.g.:
 *
 * <code>
 * 'whitelist' => [
 *	    'Symfony\\Polyfill\\*',
 *     'wpdb',
 *     '\wpdb',
 *     '\\wpdb',
 *     ],
 * 'whitelist-global-constants' => true,
 * 'whitelist-global-classes' => true,
 * 'whitelist-global-functions' => true,
 * </code>
 *
 * Does not whitelist `wpdb`.
 * @return callable
 */
function rootNamespacePatcher(): callable
{
    $rootNamespaceImports = init();
    return function (string $filePath, string $prefix, string $contents) use ($rootNamespaceImports) {

        // only process project source code
        if (false === mb_strpos($filePath, PROJECT_SOURCE_CODE_PATH_PARTIAL)) {
            return $contents;
        }

        foreach ($rootNamespaceImports as $importName) {
            $match = "$prefix\\$importName";
            $findMatchRegex = preg_quote($match);
            $replace = "\\$importName";
            $contents = mb_ereg_replace($findMatchRegex, $replace, $contents);
            if (false === $contents || null === $contents) {
                throw new Exception("replacement failed");
            }
        }
        return $contents;
    };
}

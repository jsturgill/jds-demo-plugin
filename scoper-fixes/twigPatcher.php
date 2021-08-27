<?php

namespace JdsDemoPlugin\Patchers;

use Exception;

const TWIG_ROOT = DIRECTORY_SEPARATOR . 'twig' . DIRECTORY_SEPARATOR . 'twig' . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR;

/**
 * @return array<string,string>
 */
function init(): array
{
    $twigFunctionsReferenceFile = realpath(__DIR__ . '/twigFunctions.json');

    if ($twigFunctionsReferenceFile === false) {
        echo "Unable to determine the twig global function information file path: $twigFunctionsReferenceFile\n\n";
        // bring the world to a halt and fix the build process
        die;
    }

    $rawFunctionsFileContents = file_get_contents($twigFunctionsReferenceFile);

    if ($rawFunctionsFileContents === false) {
        echo "Unable to read twig global function information: $twigFunctionsReferenceFile\n\n";
        // bring the world to a halt and fix the build process
        die;
    }

    return json_decode($rawFunctionsFileContents, true);
}

function twigPatcher(): callable
{
    $twigRootFunctionsLookup = init();
    return function (string $filePath, string $prefix, string $contents) use ($twigRootFunctionsLookup) {
        try {
            // only process twig files
            if (false === mb_strpos($filePath, TWIG_ROOT)) {
                return $contents;
            }

            $segments = explode(DIRECTORY_SEPARATOR, $filePath);
            $fileName = array_pop($segments);


            // filter the complete list to only include functions *not* defined in this particular file
            // reasoning: the declaration should remain undecorated with a prefix (the namespace change
            // completed by PHP Scoper is sufficient)
            $twigRootFunctionsList = array_filter(
                array_keys($twigRootFunctionsLookup),
                fn (string $func) => $twigRootFunctionsLookup[$func] !== $fileName
            );

            // create map of "raw" function strings with replacement "namespaced" function strings
            $twigRootReplacements = array_map(
                fn ($func) => '\\' . $prefix . '\\' . $func,
                $twigRootFunctionsList
            );

            // now replace bare function calls with namespaced function calls
            $contents = str_replace($twigRootFunctionsList, $twigRootReplacements, $contents);

            // next step: replace quoted function strings, used in code generation or other such madness
            $singleQuoteFunctionStrings = array_map(fn ($func) => "'$func'", array_keys($twigRootFunctionsLookup));
            $singeQuoteReplacements = array_map(fn ($func) => "'$prefix\\$func'", array_keys($twigRootFunctionsLookup));
            $contents = str_replace($singleQuoteFunctionStrings, $singeQuoteReplacements, $contents);

            // replace use statement strings -- which currently only appear in one file
            $moduleNode = 'ModuleNode.php';
            if ($moduleNode === $fileName) {
                $contents = preg_replace_callback(
                    "/\"use (Twig\\\+[^\"]+)\"/",
                    fn (array $matches) => "\"use $prefix\\\\$matches[1]\"",
                    $contents
                );
            }
        } catch (Exception $e) {
            echo $e;
        }
        return $contents;
    };
}

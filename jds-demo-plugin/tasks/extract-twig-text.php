<?php /** @noinspection PhpUnhandledExceptionInspection */

/**
 * Extracts text in a gettext friendly format
 */

namespace JdsDemoPlugin\Cli;

use JdsDemoPlugin\Services\DependencyContainerFactory;
use JdsDemoPlugin\Services\TwigTextExtractor;

require_once(dirname(__DIR__) . '/vendor/autoload.php');


$di = (new DependencyContainerFactory())->create();

/** @var TwigTextExtractor $twigTextExtractor */
$twigTextExtractor = $di->get(TwigTextExtractor::class);
$twigTextExtractor->clearExtractions();
$twigTextExtractor->extractText();

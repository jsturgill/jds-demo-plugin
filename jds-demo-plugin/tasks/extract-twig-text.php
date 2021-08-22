<?php /** @noinspection PhpUnhandledExceptionInspection */

/**
 * Extracts text in a gettext friendly format
 */

namespace JdsDemoPlugin\Cli;

use Exception;
use JdsDemoPlugin\Config\TemplateConfig;
use JdsDemoPlugin\Config\TwigTextExtractionConfig;
use JdsDemoPlugin\Services\DependencyContainer;
use JdsDemoPlugin\Services\FileSystem;
use JdsDemoPlugin\Services\TwigTextExtractor;
use Twig\Environment;
use Twig\Node\Expression\FunctionExpression;
use Twig\Node\Node;
use Twig\Source;

require_once( dirname( __DIR__ ) . '/vendor/autoload.php' );


$di = DependencyContainer::create();

/** @var TwigTextExtractor $twigTextExtractor */
$twigTextExtractor = $di->get( TwigTextExtractor::class );
$twigTextExtractor->clearExtractions();
$twigTextExtractor->extractText();

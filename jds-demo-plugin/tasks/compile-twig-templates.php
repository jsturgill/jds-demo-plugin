<?php /** @noinspection PhpUnhandledExceptionInspection */

/**
 * Pre-compiles twig templates
 */

namespace JdsDemoPlugin\Cli;

use JdsDemoPlugin\Config\TemplateConfig;
use JdsDemoPlugin\Services\DependencyContainer;
use Twig\Environment;

require_once( dirname( __DIR__ ) . '/vendor/autoload.php' );

$di = DependencyContainer::create();

/** @var Environment $twig */
$twig = $di->get( Environment::class );

/** @var TemplateConfig $templateConfig */
$templateConfig = $di->get( TemplateConfig::class );

$pathPrefixLength = mb_strlen( $templateConfig->templateRootPath ) + 1;
$toRelativePath   = fn( $path ): string => mb_substr( $path, $pathPrefixLength );

foreach ( glob( $templateConfig->templateRootPath . "/*.twig" ) as $absPath ) {
	// below should compile the template
	$twig->load( $toRelativePath( $absPath ) );
}

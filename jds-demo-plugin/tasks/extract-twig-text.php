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
use Twig\Environment;
use Twig\Node\Expression\FunctionExpression;
use Twig\Node\Node;
use Twig\Source;

require_once( dirname( __DIR__ ) . '/vendor/autoload.php' );

/**
 * Visits each node in and extracts translated strings
 * @throws Exception
 */
function extractText( Node $node, array &$text = [] ) {
	if ( $node instanceof FunctionExpression ) {
		$name = $node->getAttribute( 'name' );
		if ( $name !== '__' ) {
			return;
		}
		$textValue = null;
		foreach ( $node->getNode( 'arguments' )->getIterator() as $argument ) {
			$textValue = $argument->getAttribute( 'value' );
			break;
		}
		$cleanText = addslashes( $textValue );
		// if below is needed, something is wildly wrong
		$cleanDomain        = addslashes( TwigTextExtractionConfig::DOMAIN );
		$text[ $textValue ] = "$name(\"$cleanText\", \"$cleanDomain\");";
	} else {
		foreach ( $node->getIterator() as $childNode ) {
			extractText( $childNode, $text );
		}
	}
}

$di = DependencyContainer::create();

/** @var Environment $twig */
$twig = $di->get( Environment::class );

/** @var TemplateConfig $templateConfig */
$templateConfig = $di->get( TemplateConfig::class );

/** @var TwigTextExtractionConfig $extractTextConfig */
$extractTextConfig = $di->get( TwigTextExtractionConfig::class );
$prefixLength      = mb_strlen( $templateConfig->templateRootPath ) + 1;

/** @var FileSystem $fileSystem */
$fileSystem = $di->get( FileSystem::class );

$fileSystem->emptyDirectory( $extractTextConfig->outputDir, [ FileSystem::class, 'deleteAllButGitignore' ] );

foreach ( glob( $templateConfig->templateRootPath . "/*.twig" ) as $absPath ) {
	$relativePath = mb_substr( $absPath, $prefixLength );
	$stream       = $twig->tokenize( new Source( file_get_contents( $absPath ), $relativePath, $absPath ) );
	$nodes        = $twig->parse( $stream );
	$text         = [];
	foreach ( $nodes->getIterator() as $node ) {
		extractText( $node, $text );
	}
	$lines = join( "\n", array_values( $text ) );
	file_put_contents( $extractTextConfig->toOutputFilePath( $relativePath ), "<?php\n$lines\n" );
}


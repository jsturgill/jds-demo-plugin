<?php

namespace JdsDemoPlugin\Services;

use Exception;
use JdsDemoPlugin\Config\TwigTextExtractionConfig;
use JdsDemoPlugin\Exceptions\CommandFailureException;
use JdsDemoPlugin\Exceptions\InvalidArgumentException;
use SplFileInfo;
use stdClass;
use Twig\Environment;
use Twig\Error\SyntaxError;
use Twig\Node\Expression\FilterExpression;
use Twig\Node\Expression\FunctionExpression;
use Twig\Node\Expression\NameExpression;
use Twig\Node\Node;
use Twig\Source;

class TwigTextExtractor {
	private TwigTextExtractionConfig $config;
	private Environment $twig;
	private FileSystem $fileSystem;
	private string $cleanDomain;
	const TRANSLATOR_COMMENT_PREFIX = 'translators: ';

	// TODO add support for _x, _n, _nx, _n_noop, _nx_noop
	const TRANSLATION_FUNCTIONS = [ '__', '_e' ];
	const DEFAULT_EXPORT_FUNC = '__';

	/**
	 * @throws Exception
	 */
	public function __construct( TwigTextExtractionConfig $config, Environment $twig, FileSystem $fileSystem ) {
		$this->config     = $config;
		$this->twig       = $twig;
		$this->fileSystem = $fileSystem;

		$this->cleanDomain = addslashes( TwigTextExtractionConfig::DOMAIN );

		if ( $this->cleanDomain !== TwigTextExtractionConfig::DOMAIN ) {
			throw new Exception( "TwigTextExtractionConfig::DOMAIN const is terribly wrong" );
		}
	}

	/**
	 * Returns a string guaranteed not to contain line breaks or `?>`
	 *
	 * Ensures the string starts with the necessary translators prefix,
	 * and adds `// ` to the start.
	 *
	 * Null incoming values are returned without modification.
	 *
	 * @param ?string $comment
	 *
	 * @return ?string
	 * @see self::TRANSLATOR_COMMENT_PREFIX
	 */
	private function toValidSingleLineCommentString( ?string $comment ): ?string {
		if ( null === $comment ) {
			return null;
		}
		// mb_str_replace should not be needed here
		$intermediate = str_replace( [ "\r", "\n", '?>' ], " ", $comment );

		if ( ! str_starts_with( $intermediate, self::TRANSLATOR_COMMENT_PREFIX ) ) {
			$intermediate = self::TRANSLATOR_COMMENT_PREFIX . $intermediate;
		}

		return "// $intermediate";
	}


	/**
	 * @throws InvalidArgumentException
	 * @throws CommandFailureException
	 */
	public function clearExtractions(): void {
		$this->fileSystem->emptyDirectory( $this->config->outputDir,
			[ FileSystem::class, 'deleteAllButGitignore' ]
		);
	}

	/**
	 * @throws Exception
	 */
	private function extractArguments( FunctionExpression $node, $min = 1 ): array {
		$result = [];
		foreach ( $node->getNode( 'arguments' )->getIterator() as $argument ) {
			array_push( $result, $argument->getAttribute( 'value' ) );
		}
		if ( count( $result ) < $min ) {
			throw new InvalidArgumentException( "FunctionExpression node has " . count( $result ) . "arguments (minimum: $min)" );
		}

		return $result;
	}

	/**
	 * Processes a translation function expression in a twig template
	 * @throws Exception
	 */
	private function processFunctionExpression( FunctionExpression $node, array &$text ) {
		$name = $node->getAttribute( 'name' );
		if ( ! in_array( $name, self::TRANSLATION_FUNCTIONS ) ) {
			return;
		}
		$arguments = $this->extractArguments( $node );
		$textValue = $arguments[0];
		$comment   = array_key_exists( 1, $arguments ) ? $arguments[1] : null;
		if ( array_key_exists( $textValue, $text ) ) {
			return;
		}

		$cleanText          = addslashes( $textValue );
		$cleanComment       = $this->toValidSingleLineCommentString( $comment );
		$text[ $textValue ] = "$cleanComment\n$name(\"$cleanText\", \"$this->cleanDomain\");";
	}

	/**
	 * @throws InvalidArgumentException
	 * @throws Exception
	 * @link https://www.php.net/manual/en/language.variables.basics.php Source for valid PHP variable regex
	 */
	private function processFilterExpression( FilterExpression $node, array &$text ) {
		// the filtered node should be a function expression where the
		// function name is one of the supported translation functions
		$filteredNode = $node->getNode( 'node' );

		if ( false === $filteredNode instanceof FunctionExpression ) {
			return;
		}

		$functionName = $filteredNode->getAttribute( 'name' );

		if ( ! in_array( $functionName, self::TRANSLATION_FUNCTIONS ) ) {
			return;
		}

		// extract the translation string
		$arguments    = $this->extractArguments( $filteredNode );
		$textValue    = $arguments[0];
		$cleanText    = addslashes( $textValue );
		$comment      = array_key_exists( 1, $arguments ) ? $arguments[1] : null;
		$cleanComment = $this->toValidSingleLineCommentString( $comment );

		// ensure the filter being processed is the 'format' filter
		$filterNode = $node->getNode( 'filter' );
		if ( 'format' !== $filterNode->getAttribute( 'value' ) ) {
			return;
		}

		$argumentNodes = $node->getNode( 'arguments' );
		$arguments     = [];
		/** @var NameExpression $argumentNode */
		foreach ( $argumentNodes as $argumentNode ) {
			if ( $argumentNode->hasAttribute( 'name' ) ) {
				$arg = $argumentNode->getAttribute( 'name' );

				// see docblock for source of regex
				if ( ! preg_match( '/^[a-zA-Z_\x80-\xff][a-zA-Z0-9_\x80-\xff]*$/', $arg ) ) {
					throw new InvalidArgumentException( "Invalid PHP variable name: '$arg'" );
				}
				array_push( $arguments, '$' . $arg );
			} elseif ( $argumentNode->hasAttribute( 'value' ) ) {

				array_push( $arguments, '"' . addslashes( $argumentNode->getAttribute( 'value' ) ) . '"' );

			} else {
				array_push( $arguments, '$unkownVariable' );
			}

		}

		$argString = join( ', ', $arguments );

		$text[ $textValue ] = "$cleanComment\nsprintf( " . $functionName . "( \"$cleanText\", \"$this->cleanDomain\"), $argString);";
	}

	/**
	 * Visits each node in and extracts translated strings
	 * @throws Exception
	 */
	private function processNode( Node $node, array &$text ) {
		// sprintf format
		if ( $node instanceof FilterExpression ) {
			$this->processFilterExpression( $node, $text );
		}

		// simple strings
		if ( $node instanceof FunctionExpression ) {
			$this->processFunctionExpression( $node, $text );;
		}
		// todo pluralization?
		foreach ( $node->getIterator() as $childNode ) {
			$this->processNode( $childNode, $text );
		}
	}

	/**
	 * @throws SyntaxError
	 * @throws Exception
	 */
	public function processTwigTemplate( SplFileInfo $fileInfo ): void {
		if ( 'twig' !== $fileInfo->getExtension() ) {
			return;
		}

		// add one to input path to account for trailing slash
		$relativePath = mb_substr( $fileInfo->getRealPath(), $this->config->inputPathLength + 1 );
		$stream       = $this->twig->tokenize(
			new Source( file_get_contents( $fileInfo->getRealPath() ),
				$relativePath,
				$fileInfo->getRealPath() )
		);
		$nodes        = $this->twig->parse( $stream );
		$text         = [];

		foreach ( $nodes->getIterator() as $node ) {
			$this->processNode( $node, $text );
		}

		// filter out skip entries and join together as lines of code to write out
		$lines = join( "\n", array_values( $text ) );
		file_put_contents( $this->config->toOutputFilePath( $relativePath ), "<?php\n$lines\n" );
	}

	/**
	 * @throws InvalidArgumentException
	 */
	public function extractText(): void {
		$this->fileSystem->processFiles( $this->config->inputDir, [ $this, 'processTwigTemplate' ] );
	}
}

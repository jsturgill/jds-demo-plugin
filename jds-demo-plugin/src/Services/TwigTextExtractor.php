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
	private static $skipIdentifier;

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
		if ( false === $this::$skipIdentifier instanceof stdClass ) {
			$this::$skipIdentifier = new stdClass();
		}
	}

	private function isSkipEntry( $value ) {
		return $value === $this::$skipIdentifier;
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
	private function extractFirstArgument( FunctionExpression $node ) {
		$result = null;
		foreach ( $node->getNode( 'arguments' )->getIterator() as $argument ) {
			$result = $argument->getAttribute( 'value' );
			break;
		}

		return $result;
	}

	/**
	 * @throws Exception
	 */
	private function processFunctionExpression( FunctionExpression $node, array &$text ) {
		$name = $node->getAttribute( 'name' );
		if ( ! in_array( $name, self::TRANSLATION_FUNCTIONS ) ) {
			return;
		}
		$textValue = $this->extractFirstArgument( $node );
		if ( array_key_exists( $textValue, $text ) ) {
			return;
		}
		$cleanText = addslashes( $textValue );

		$text[ $textValue ] = self::DEFAULT_EXPORT_FUNC . "('$cleanText', '$this->cleanDomain');";
	}

	/**
	 * @throws InvalidArgumentException
	 * @throws Exception
	 * @link https://www.php.net/manual/en/language.variables.basics.php Source for valid PHP variable regex
	 */
	private function processFilterExpression( FilterExpression $node, array &$text ) {
		$filteredNode = $node->getNode( 'node' );

		if ( false === $filteredNode instanceof FunctionExpression ) {
			return;
		}

		if ( ! in_array( $filteredNode->getAttribute( 'name' ), self::TRANSLATION_FUNCTIONS ) ) {
			return;
		}

		$textValue = $this->extractFirstArgument( $filteredNode );
		$cleanText = addslashes( $textValue );

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
				array_push( $arguments, $arg );
			} else {
				// hard-coded sprintf strings are not supported -- must have a variable name
				// however, if a valid entry exists already for the same text, then
				// don't overwrite it
				if ( ! array_key_exists( $textValue, $text ) ) {
					$text[ $textValue ] = $this::$skipIdentifier;
				}

				return;
			}

		}

		$cleanArgs = join( ', ',
			array_map( fn( string $arg ) => '$' . $arg, $arguments )
		);

		$text[ $textValue ] = "sprintf( " . self::DEFAULT_EXPORT_FUNC . "( '$cleanText', '$this->cleanDomain'), $cleanArgs);";
	}

	/**
	 * Visits each node in and extracts translated strings
	 * @throws Exception
	 */
	private function extractTextFromNode(
		Node $node, array &$text = []
	) {
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
			$this->extractTextFromNode( $childNode, $text );
		}
	}

	/**
	 * @throws SyntaxError
	 * @throws Exception
	 */
	public
	function processTwigTemplate(
		SplFileInfo $fileInfo
	): void {
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
			$this->extractTextFromNode( $node, $text );
		}

		// filter out skip entries and join together as lines of code to write out
		$lines = join( "\n", array_filter(
				array_values( $text ), fn( $x ) => false === $this::isSkipEntry( $x ) )
		);
		file_put_contents( $this->config->toOutputFilePath( $relativePath ), "<?php\n$lines\n" );
	}

	/**
	 * @throws InvalidArgumentException
	 */
	public
	function extractText(): void {
		$this->fileSystem->processFiles( $this->config->inputDir, [ $this, 'processTwigTemplate' ] );
	}
}

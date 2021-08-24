<?php

namespace JdsDemoPlugin\Services;

use Exception;
use JdsDemoPlugin\Config\TwigTextExtractionConfig;
use JdsDemoPlugin\Exceptions\CommandFailureException;
use JdsDemoPlugin\Exceptions\InvalidArgumentException;
use JdsDemoPlugin\Services\TwigTextExtractor\Argument;
use JdsDemoPlugin\Services\TwigTextExtractor\ArgumentRepresentations;
use SplFileInfo;
use Twig\Environment;
use Twig\Error\SyntaxError;
use Twig\Node\Expression\ConstantExpression;
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
	const TRANSLATION_FUNCTIONS = [ '__', '_e', '_x' ];
	const DEFAULT_EXPORT_FUNC = '__';
	const FUNCTIONS_TO_PARAM_COUNT_MAP = [
		'__' => 1,
		'_e' => 1,
		'_x' => 2
	];

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
	 * @throws InvalidArgumentException
	 * @throws CommandFailureException
	 */
	public function clearExtractions(): void {
		$this->fileSystem->emptyDirectory( $this->config->outputDir,
			[ FileSystem::class, 'deleteAllButGitignore' ]
		);
	}

	/**
	 * @return Argument[]
	 * @throws Exception
	 */
	private function extractArguments( Node $node, $min = 1 ): array {
		if ( ! $node->hasNode( 'arguments' ) ) {
			throw new InvalidArgumentException( "Cannot extract arguments from a node that does not have any" );
		}

		$result = [];

		foreach ( $node->getNode( 'arguments' )->getIterator() as $argument ) {
			array_push( $result, Argument::ofNode( $argument ) );
		}

		if ( count( $result ) < $min ) {
			throw new InvalidArgumentException( "FunctionExpression node has " . count( $result ) . "arguments (minimum: $min)" );
		}

		return $result;
	}

	/**
	 * Processes a translation function expression in a twig template
	 *
	 * This is the simplest case.
	 * @throws Exception
	 * @see TwigTextExtractor::processFilterExpression the more complex "sprintf" style case
	 */
	private function processFunctionExpression( FunctionExpression $node, array &$text ) {
		$name = $node->getAttribute( 'name' );
		if ( ! in_array( $name, self::TRANSLATION_FUNCTIONS ) ) {
			return;
		}
		$arguments = $this->extractArguments( $node );
		$textValue = $arguments[0]->stringValue;
		// don't overwrite the value if it already exists
		if ( array_key_exists( $textValue, $text ) ) {
			return;
		}
		$text[ $textValue ] = $this->codeGenerator( $arguments,
			$name,
			self::FUNCTIONS_TO_PARAM_COUNT_MAP[ $name ]
		);
	}

	private function codeGenerator(
		$arguments,
		$functionName,
		int $paramCount = 1,
		$wrapInSprintf = false,
		$sprintfArgs = null
	): string {
		$comment      = array_key_exists( $paramCount, $arguments ) ? $arguments[ $paramCount ] : null;
		$cleanComment = null !== $comment
			? $comment->asComment( self::TRANSLATOR_COMMENT_PREFIX )
			: null;
		$argumentList = [];
		$sprintfArgs  = [];
		// collect all args except the first, up until the limit passed in as $paramCount
		// -- the first arg counts, so if $paramCount === 1, then no arguments will be
		// collected
		foreach ( $arguments as $idx => $argument ) {
			if ( $idx >= $paramCount ) {
				array_push( $sprintfArgs, $argument );
			}
			array_push( $argumentList, $argument );
		}

		// escape, quote, separate with commas, and append the domain
		$cleanArgs = join( ", ",
				array_map( fn( $arg ) => $arg->asPhpCode(), $argumentList )
		             ) . ", \"$this->cleanDomain\"";

		$prefix = null === $cleanComment
			? ""
			: "$cleanComment\n";
		if ( $wrapInSprintf ) {
			return $prefix .= "sprintf( $functionName( $cleanArgs ) );";
		}

		return $prefix .= "$functionName( $cleanArgs );";
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

		// ensure the filter being processed is the 'format' filter
		$filterNode = $node->getNode( 'filter' );
		if ( 'format' !== $filterNode->getAttribute( 'value' ) ) {
			return;
		}

		$translationArgs = $this->extractArguments( $filteredNode );

		$formatArgs = $this->extractArguments( $node );

		$argString = join( ', ', array_map( fn( Argument $x ) => $x->asPhpCode(), $formatArgs ) );

		$textValue = $translationArgs[0]->stringValue;
		//$text[ $textValue ] = "$cleanComment\nsprintf( " . $functionName . "( \"$cleanText\", \"$this->cleanDomain\"), $argString);";
		$text[ $textValue ] = $this->codeGenerator( $formatArgs,
			$functionName,
			self::FUNCTIONS_TO_PARAM_COUNT_MAP[ $functionName ],
			true,
			$formatArgs
		);
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

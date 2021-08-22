<?php

namespace JdsDemoPlugin\Config;

use JdsDemoPlugin\Exceptions\InvalidArgumentException;

class TwigTextExtractionConfig {
	public string $outputDir;
	public const DOMAIN = 'jds-demo-plugin-domain';

	public function __construct( string $outputDir ) {
		$this->outputDir = $outputDir;
	}

	/**
	 * Returns the absolute path to write out the extracted translated strings
	 * @throws InvalidArgumentException
	 */
	public function toOutputFilePath( string $relativeTemplatePath ): string {
		$pathinfo = pathinfo( $relativeTemplatePath );
		if ( mb_strtolower( $pathinfo['extension'] ) !== 'twig' ) {
			throw new InvalidArgumentException( "Expected a .twig extension: $relativeTemplatePath" );
		}

		return $this->outputDir . "/${pathinfo['dirname']}/${pathinfo['filename']}.php";
	}
}

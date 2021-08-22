<?php

namespace JdsDemoPlugin\Config;

use JdsDemoPlugin\Exceptions\InvalidArgumentException;

class TwigTextExtractionConfig {
	public string $extractionDir;
	public const DOMAIN = 'jds-demo-plugin-domain';

	public function __construct( string $extractionDir ) {
		$this->extractionDir = $extractionDir;
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

		return $this->extractionDir . "/${pathinfo['dirname']}/${pathinfo['filename']}.php";
	}
}

<?php

namespace JdsDemoPlugin\Config;

use JdsDemoPlugin\Exceptions\InvalidArgumentException;
use JdsDemoPlugin\Services\FileSystem;
use Twig\Environment;

class TwigTextExtractionConfig {
	public string $inputDir;
	public string $outputDir;
	public int $inputPathLength;
	public const DOMAIN = 'jds-demo-plugin-domain';

	public function __construct( TemplateConfig $templateConfig, string $outputDir ) {
		$this->outputDir       = rtrim( $outputDir, FileSystem::PATH_SEPARATORS );
		$this->inputDir        = rtrim( $templateConfig->templateRootPath, FileSystem::PATH_SEPARATORS );
		$this->inputPathLength = mb_strlen( $templateConfig->templateRootPath );
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

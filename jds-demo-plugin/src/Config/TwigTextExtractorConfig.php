<?php

namespace JdsDemoPlugin\Config;

use JdsDemoPlugin\Exceptions\InvalidArgumentException;
use JdsDemoPlugin\Services\FileSystem;

class TwigTextExtractorConfig
{
	public string $inputDir;
	public string $outputDir;
	public int $inputPathLength;
	public const DOMAIN = 'jds-demo-plugin-domain';
	private string $translationDomain;

	public function __construct(TemplateConfig $templateConfig, string $outputDir, string $translationDomain)
	{
		$this->outputDir = rtrim($outputDir, FileSystem::PATH_SEPARATORS);
		$this->inputDir = rtrim($templateConfig->templateRootPath, FileSystem::PATH_SEPARATORS);
		$this->inputPathLength = mb_strlen($templateConfig->templateRootPath);
		$this->translationDomain = $translationDomain;
	}

	/**
	 * Returns the absolute path to write out the extracted translated strings
	 * @throws InvalidArgumentException
	 */
	public function toOutputFilePath(string $relativeTemplatePath): string
	{
		$pathinfo = pathinfo($relativeTemplatePath);
		if (array_key_exists('extension', $pathinfo) && mb_strtolower($pathinfo['extension']) !== 'twig') {
			throw new InvalidArgumentException("Expected a .twig extension: $relativeTemplatePath");
		}

		return $this->outputDir . "/${pathinfo['dirname']}/${pathinfo['filename']}.php";
	}

	public function domain(): string
	{
		return $this->translationDomain;
	}
}

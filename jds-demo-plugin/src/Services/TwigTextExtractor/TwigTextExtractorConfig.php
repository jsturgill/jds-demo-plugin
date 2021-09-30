<?php

namespace JdsDemoPlugin\Services\TwigTextExtractor;

use _PHPStan_68495e8a9\Nette\Neon\Exception;
use JdsDemoPlugin\Config\TemplateConfig;
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
        $this->outputDir = rtrim($outputDir, FileSystem::PATH_SEPARATORS) . DIRECTORY_SEPARATOR;
        $this->inputDir = rtrim($templateConfig->templateRootPath, FileSystem::PATH_SEPARATORS) . DIRECTORY_SEPARATOR;
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

        return $this->outputDir . $pathinfo['dirname'] . DIRECTORY_SEPARATOR . "${pathinfo['filename']}.php";
    }

    public function domain(): string
    {
        return $this->translationDomain;
    }
}

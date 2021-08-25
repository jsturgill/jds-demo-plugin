<?php

namespace JdsDemoPlugin\Config;

use JdsDemoPlugin\Services\FileSystem;

class ConfigFactory
{
	const PATH_PARTIAL_TEMPLATES = "templates";
	const PATH_PARTIAL_CACHE_ROOT = "cache";
	const PATH_PARTIAL_TEMPLATE_CACHE = ConfigFactory::PATH_PARTIAL_CACHE_ROOT . "/templates";
	const PATH_PARTIAL_DI_CACHE = ConfigFactory::PATH_PARTIAL_CACHE_ROOT . "/di";
	const PATH_PARTIAL_TWIG_TEXT_CACHE = ConfigFactory::PATH_PARTIAL_CACHE_ROOT . "/gettext";

	private string $pluginRootPath;
	private string $translationDomain;

	/**
	 * @var array<string, mixed>
	 */
	private array $cache = [];

	public function __construct(FileSystem $fileSystem, string $rootPluginPath, string $translationDomain)
	{
		$this->pluginRootPath = $fileSystem->forceTrailingSlash($rootPluginPath);
		$this->translationDomain = $translationDomain;
	}

	/**
	 *
	 * @psalm-suppress UnusedParam
	 * @template T of object
	 * @param class-string<T> $className
	 * @param string $key
	 * @param callable():T $callback
	 *
	 * @return T
	 */
	private function getOrCache(string $className, string $key, callable $callback): object
	{

		if (!array_key_exists($key, $this->cache)) {
			$instance = $callback();
			$this->cache[$key] = $instance;
		}

		/** @var T */
		return $this->cache[$key];
	}

	public function createTemplateConfig(): TemplateConfig
	{
		/** @var TemplateConfig $result */
		/** @noinspection PhpUnnecessaryLocalVariableInspection */
		$result = $this->getOrCache(TemplateConfig::class,
			'templateConfig',
			fn() => new TemplateConfig(
				$this->pluginRootPath . ConfigFactory::PATH_PARTIAL_TEMPLATES,
				$this->pluginRootPath . ConfigFactory::PATH_PARTIAL_TEMPLATE_CACHE
			));

		return $result;
	}

	public function createTwigTextExtractionConfig(): TwigTextExtractorConfig
	{
		/** @var TwigTextExtractorConfig $result */
		/** @noinspection PhpUnnecessaryLocalVariableInspection */
		$result = $this->getOrCache(TwigTextExtractorConfig::class,
			'twigTextExtractionConfig',
			fn() => new TwigTextExtractorConfig(
				$this->createTemplateConfig(),
				$this->pluginRootPath . ConfigFactory::PATH_PARTIAL_TWIG_TEXT_CACHE,
				$this->translationDomain
			)
		);

		return $result;
	}
}

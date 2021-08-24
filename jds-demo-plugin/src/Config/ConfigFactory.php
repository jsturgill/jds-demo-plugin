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

	protected string $pluginRootPath;

	public array $cache;

	public function __construct(FileSystem $fileSystem, string $rootPluginPath)
	{
		$this->pluginRootPath = $fileSystem->forceTrailingSlash($rootPluginPath);
	}

	/**
	 * @param class-string<T> $className
	 * @param string $key
	 * @param callable $callback
	 *
	 * @return T
	 * @noinspection PhpUndefinedClassInspection
	 */
	private function getOrCache(string $className, string $key, callable $callback): object
	{
		// TODO configure PHPStan / Psalm, try to get generics working
		$this->cache[$key] = $this->cache[$key] ?? $callback();

		return $this->cache[$key];
	}

	public function createTemplateConfig(): TemplateConfig
	{
		/** @var TemplateConfig $result */
		/** @noinspection PhpUnnecessaryLocalVariableInspection */
		$result = $this->getOrCache(TemplateConfig::class,
			'templateConfig',
			fn() => new TemplateConfig(
				$this->pluginRootPath . $this::PATH_PARTIAL_TEMPLATES,
				$this->pluginRootPath . $this::PATH_PARTIAL_TEMPLATE_CACHE
			));

		return $result;
	}

	public function createTwigTextExtractionConfig(): TwigTextExtractionConfig
	{
		/** @var TwigTextExtractionConfig $result */
		/** @noinspection PhpUnnecessaryLocalVariableInspection */
		$result = $this->getOrCache(TwigTextExtractionConfig::class,
			'twigTextExtractionConfig',
			fn() => new TwigTextExtractionConfig(
				$this->createTemplateConfig(),
				$this->pluginRootPath . $this::PATH_PARTIAL_TWIG_TEXT_CACHE)
		);

		return $result;
	}
}

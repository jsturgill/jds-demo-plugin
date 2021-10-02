<?php

namespace JdsDemoPlugin\Config;

use JdsDemoPlugin\Services\FileSystem;
use JdsDemoPlugin\Services\Persistence\PersistenceConfig;
use JdsDemoPlugin\Services\TwigTextExtractor\TwigTextExtractorConfig;

// TODO interface for ConfigFactory
// TODO BaseConfig class that includes separate, granular values for project root, templates root, cache root, etc.

class ConfigFactory
{
    public const PATH_PARTIAL_TEMPLATES = "templates";
    public const PATH_PARTIAL_CACHE_ROOT = "cache";
    public const PATH_PARTIAL_MIGRATION_CONFIG = "phinx.wordpress.php";
    public const PATH_PARTIAL_TEMPLATE_CACHE = ConfigFactory::PATH_PARTIAL_CACHE_ROOT . DIRECTORY_SEPARATOR . "templates";
    public const PATH_PARTIAL_DI_CACHE = ConfigFactory::PATH_PARTIAL_CACHE_ROOT . DIRECTORY_SEPARATOR . "di";
    public const PATH_PARTIAL_TWIG_TEXT_CACHE = ConfigFactory::PATH_PARTIAL_CACHE_ROOT . DIRECTORY_SEPARATOR . "gettext";

    private string $pluginRootPath;
    private string $translationDomain;

    /**
     * @var array<string, mixed>
     */
    private array $cache = [];

    public function __construct(string $rootPluginPath, string $translationDomain)
    {
        $this->pluginRootPath = rtrim($rootPluginPath, FileSystem::PATH_SEPARATORS) . DIRECTORY_SEPARATOR;
        $this->translationDomain = $translationDomain;
    }

    /**
     * @return PersistenceConfig
     */
    public function createPersistenceConfig(): PersistenceConfig
    {
        return new PersistenceConfig();
    }

    /**
     * @return array<string,mixed>
     */
    public function createMigrationConfig(): array
    {
        // modify psalm.xml if this is updated to something more typesafe
        return require($this->pluginRootPath . ConfigFactory::PATH_PARTIAL_MIGRATION_CONFIG);
    }

    /**
     * @template T of object
     * @param class-string<T> $className
     * @param callable():T $callback
     *
     * @return T
     */
    private function getOrCache(string $className, callable $callback): object
    {
        if (!array_key_exists($className, $this->cache)) {
            $instance = $callback();
            $this->cache[$className] = $instance;
        }

        /** @var T */
        return $this->cache[$className];
    }

    public function createTemplateConfig(): TemplateConfig
    {
        /** @var TemplateConfig $result */
        /** @noinspection PhpUnnecessaryLocalVariableInspection */
        $result = $this->getOrCache(
            TemplateConfig::class,
            fn () => new TemplateConfig(
                $this->pluginRootPath . ConfigFactory::PATH_PARTIAL_TEMPLATES,
                $this->pluginRootPath . ConfigFactory::PATH_PARTIAL_TEMPLATE_CACHE
            )
        );

        return $result;
    }

    public function createTwigTextExtractionConfig(): TwigTextExtractorConfig
    {
        /** @var TwigTextExtractorConfig $result */
        /** @noinspection PhpUnnecessaryLocalVariableInspection */
        $result = $this->getOrCache(
            TwigTextExtractorConfig::class,
            fn () => new TwigTextExtractorConfig(
                $this->createTemplateConfig(),
                $this->pluginRootPath . ConfigFactory::PATH_PARTIAL_TWIG_TEXT_CACHE,
                $this->translationDomain
            )
        );

        return $result;
    }
}

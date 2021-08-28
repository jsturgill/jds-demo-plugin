<?php

namespace Helper;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

use Codeception\Module;
use DI\Container;
use Error;
use Exception;
use JdsDemoPlugin\Services\DependencyContainerFactory;

class Unit extends Module
{
    public const FILES_PATH_PARTIAL = '_data';

    public const GOLDEN_MASTERS_PARTIAL_PATH = 'goldenMasters';
    /**
     * @var array<string, Container> $cache
     */
    private static array $cache = [];

    public function getTestFilesRoot(): string
    {
        return dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . Unit::FILES_PATH_PARTIAL;
    }

    public function getPluginRoot(): string
    {
        return dirname(__DIR__, 3);
    }

    public function getTestFilePath(string $partial): string
    {
        return $this->getTestFilesRoot() . DIRECTORY_SEPARATOR . $partial;
    }

    public function getGoldenMastersRoot(): string
    {
        return $this->getTestFilePath(Unit::GOLDEN_MASTERS_PARTIAL_PATH);
    }

    public function getGoldenMasterPath(string $partial): string
    {
        return $this->getGoldenMastersRoot() . DIRECTORY_SEPARATOR . $partial;
    }

    public function getTestFileContents(string $partial): string
    {
        $path = $this->getTestFilePath($partial);
        $result = file_get_contents($path);
        if (false === $result) {
            throw new Error("Unable to read file: $path");
        }
        return $result;
    }

    public function getGoldenMasterContents(string $partial): string
    {
        return $this->getTestFileContents(Unit::GOLDEN_MASTERS_PARTIAL_PATH . DIRECTORY_SEPARATOR . $partial);
    }

    /**
     * @throws Exception
     */
    public function getDiContainer($rootPath = null): Container
    {
        $rootPath = $rootPath ?? $this->getTestFilesRoot();
        if (array_key_exists($rootPath, Unit::$cache)) {
            return Unit::$cache[$rootPath];
        }

        $factory = new DependencyContainerFactory();

        Unit::$cache[$rootPath] = $factory->create($rootPath, DependencyContainerFactory::ENV_TEST);
        return Unit::$cache[$rootPath];
    }
}

<?php

namespace Helper;

use DI\Container;
use Exception;
use JdsDemoPlugin\Services\DependencyContainerFactory;

trait SharedHelperTrait
{
    public function getTestFilesRoot(): string
    {
        return dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . SharedHelperConstants::FILES_PATH_PARTIAL;
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
        return $this->getTestFilePath(SharedHelperConstants::GOLDEN_MASTERS_PARTIAL_PATH);
    }

    public function getGoldenMasterPath(string $partial): string
    {
        return $this->getGoldenMastersRoot() . DIRECTORY_SEPARATOR . $partial;
    }

    /**
     * @throws Exception
     */
    public function getTestFileContents(string $partial): string
    {
        $path = $this->getTestFilePath($partial);
        $result = file_get_contents($path);
        if (false === $result) {
            throw new Exception("Unable to read file: $path");
        }
        return $result;
    }

    /**
     * @throws Exception
     */
    public function getGoldenMasterContents(string $partial): string
    {
        return $this->getTestFileContents(SharedHelperConstants::GOLDEN_MASTERS_PARTIAL_PATH . DIRECTORY_SEPARATOR . $partial);
    }

    /**
     * @param null $rootPath
     * @return Container
     * @throws Exception
     */
    public function getDiContainer($rootPath = null): Container
    {
        $rootPath = $rootPath ?? $this->getTestFilesRoot();
        $factory = new DependencyContainerFactory();
        return $factory->create($rootPath, DependencyContainerFactory::ENV_TEST);
    }
}

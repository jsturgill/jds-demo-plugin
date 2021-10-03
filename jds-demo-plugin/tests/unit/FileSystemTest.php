<?php

/** @noinspection PhpIllegalPsrClassPathInspection */

namespace JdsDemoPlugin\Tests;

use Codeception\Test\Unit;
use JdsDemoPlugin\Exceptions\InvalidArgumentException;
use JdsDemoPlugin\Services\FileSystem;
use UnitTester;

class FileSystemTest extends Unit
{
    public const PATH_PARTIAL = 'cache/file-system-test';
    public const IDENTIFIERS_WITH_GITIGNORE = ['b'];

    protected UnitTester $tester;
    private FileSystem $fileSystem;

    /**
     * @throws InvalidArgumentException
     */
    protected function _before()
    {
        $paths = $this->getPaths();
        foreach ($paths as $identifier=>$path) {
            $this->ensureDirectoryExists($path);
            $this->ensureFileExists($path . DIRECTORY_SEPARATOR . "$identifier.txt");
            if (in_array($identifier, self::IDENTIFIERS_WITH_GITIGNORE)) {
                $this->ensureFileExists($path . DIRECTORY_SEPARATOR . '.gitignore', "# a gitignore file");
            }
        }
        $this->fileSystem = new FileSystem($paths['root'], false);
    }

    /**
     * @return array
     */
    public function getPaths(): array
    {
        $result['root'] = $this->tester->getTestFilePath(self::PATH_PARTIAL);
        $result['a'] = $result['root'] . DIRECTORY_SEPARATOR . 'a';
        $result['b'] = $result['a'] . DIRECTORY_SEPARATOR . 'b';
        $result['c'] = $result['b'] . DIRECTORY_SEPARATOR . 'c';
        $result['d'] = $result['root'] . DIRECTORY_SEPARATOR . 'd';
        return $result;
    }

    /**
     * Creates a directory at the provided path (if one does not already exist)
     * @param string $path
     */
    private function ensureDirectoryExists(string $path): void
    {
        if (!is_dir($path)) {
            $result = mkdir($path, 0755, true);
        }
        self::assertDirectoryExists($path, "failed to create base path for FileSystemTest: $path");
    }

    /**
     * Writes a file at the provided path (if one does not already exist)
     * @param string $path
     * @param ?string $contents optional
     */
    private function ensureFileExists(string $path, ?string $contents = null)
    {
        if (!is_file($path)) {
            file_put_contents($path, $contents ?? $path);
        }
    }


    protected function _after()
    {
    }

    private function assertTestPathsExist($paths)
    {
        foreach ($paths as $identifier=>$path) {
            self::assertDirectoryExists($path, "required directory was not bootstrapped for test: $path");
            self::assertFileExists($path . DIRECTORY_SEPARATOR . "$identifier.txt");
            if (in_array($identifier, self::IDENTIFIERS_WITH_GITIGNORE)) {
                self::assertFileExists($path . DIRECTORY_SEPARATOR . '.gitignore', "required .gitignore file does not exist");
            }
        }
    }

    // tests
    public function testDeleteAll()
    {
        $paths = $this->getPaths();
        $this->assertTestPathsExist($paths);
        $this->fileSystem->emptyDirectory($paths['root'], [FileSystem::class, 'deleteAllFilter']);
        foreach ($paths as $identifier=>$path) {
            if ('root' === $identifier) {
                self::assertDirectoryExists($path, "base directory was incorrectly removed by emptyDirectory method");
            } else {
                self::assertDirectoryDoesNotExist($path, "directory was incorrectly retained by emptyDirectory method: $path");
            }
        }
    }

    public function testDeleteAllButGitignore()
    {
        $paths = $this->getPaths();
        $this->assertTestPathsExist($paths);

        $this->fileSystem->emptyDirectory($paths['root'], [FileSystem::class, 'deleteAllButGitignore']);

        $gitignorePaths = [];

        foreach (self::IDENTIFIERS_WITH_GITIGNORE as $identifier) {
            array_push($gitignorePaths, $paths[$identifier]);
            $filePath = $paths[$identifier] . DIRECTORY_SEPARATOR . '.gitignore';
            self::assertFileExists(
                $filePath,
                "gitignore incorrectly removed by emptyDirectory method: $filePath"
            );
        }

        foreach ($paths as $path) {
            $isParentOfGitignorePath = false;
            foreach ($gitignorePaths as $gitignorePath) {
                if (0 === strncmp($gitignorePath, $path, mb_strlen($path))) {
                    $isParentOfGitignorePath = true;
                }
            }

            if ($isParentOfGitignorePath) {
                self::assertDirectoryExists($path, "directory was incorrectly removed by emptyDirectory method: $path");
            } else {
                self::assertDirectoryDoesNotExist($path, "directory was incorrectly retained by emptyDirectory method: $path");
            }
        }
    }
}

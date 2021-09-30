<?php

/** @noinspection PhpUnhandledExceptionInspection */

/**
 * Pre-compiles twig templates
 */

namespace JdsDemoPlugin\Cli;

use JdsDemoPlugin\Services\DependencyContainerFactory;
use JdsDemoPlugin\Services\FileSystem;

require_once(dirname(__DIR__) . '/vendor/autoload.php');

$di = (new DependencyContainerFactory())->create(dirname(__DIR__), DependencyContainerFactory::ENV_TASK);
/** @var FileSystem $fileSystem */
$fileSystem = $di->get(FileSystem::class);

$fileSystem->emptyDirectory(dirname(__DIR__) . "/cache", [FileSystem::class, 'deleteAllButGitignore']);

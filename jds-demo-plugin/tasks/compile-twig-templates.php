<?php

/** @noinspection PhpUnhandledExceptionInspection */

/**
 * Pre-compiles twig templates
 */

namespace JdsDemoPlugin\Cli;

use JdsDemoPlugin\Config\TemplateConfig;
use JdsDemoPlugin\Services\DependencyContainerFactory;
use JdsDemoPlugin\Services\FileSystem;
use Psr\Log\LoggerInterface;
use Twig\Environment;

require_once(dirname(__DIR__) . '/vendor/autoload.php');

$di = (new DependencyContainerFactory())->create(dirname(__DIR__), DependencyContainerFactory::ENV_TASK);

/** @var Environment $twig */
$twig = $di->get(Environment::class);

/** @var LoggerInterface $logger */
$logger = $di->get(LoggerInterface::class);

/** @var TemplateConfig $templateConfig */
$templateConfig = $di->get(TemplateConfig::class);

$pathPrefixLength = mb_strlen($templateConfig->templateRootPath) + 1;
$toRelativePath = fn ($path): string => mb_substr($path, $pathPrefixLength);

/** @var FileSystem $fileSystem */
$fileSystem = $di->get(FileSystem::class);

$fileSystem->emptyDirectory($templateConfig->templateCachePath, [FileSystem::class, 'deleteAllButGitignore']);

foreach (glob($templateConfig->templateRootPath . "/*.twig") as $absPath) {
    // below should compile the template
    $twig->load($toRelativePath($absPath));
    $logger->notice("Compiled twig template", ["path" => $absPath]);
}

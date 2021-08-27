<?php

/** @noinspection PhpUnhandledExceptionInspection */

/**
 * Perform migrations
 */

namespace JdsDemoPlugin\Cli;

use JdsDemoPlugin\Services\DependencyContainerFactory;
use JdsDemoPlugin\Services\Persistence\IMigrationManagerFactory;
use JdsDemoPlugin\Services\Persistence\MigrationManager;
use Psr\Log\LoggerInterface;

require_once(dirname(__DIR__) . '/vendor/autoload.php');

$di = (new DependencyContainerFactory())->create();

/** @var MigrationManager $migrationManager */
$migrationManager = $di->get(IMigrationManagerFactory::class)->create();

/** @var LoggerInterface $logger */
$logger = $di->get(LoggerInterface::class);

$migrationManager->migrate();

$logger->notice("Migrations applied");

<?php

namespace JdsDemoPlugin\Services\Persistence;

use Exception;
use Phinx\Migration\Manager;
use Psr\Log\LoggerInterface;

class MigrationManager implements IMigrationManager
{
    private string $env;
    private Manager $manager;
    private LoggerInterface $logger;

    public function __construct(string $env, Manager $manager, LoggerInterface $logger)
    {
        $this->manager = $manager;
        $this->env = $env;
        $this->logger = $logger;
    }

    public function migrate(): bool
    {
        try {
            $this->manager->migrate($this->env);
            $result = $this->manager->printStatus($this->env);

            // if no true values are returned, then there are no no missing or down migrations
            // -- so, success (return true)
            return !in_array(true, array_values($result), true);
        } catch (Exception $e) {
            $this->logger->error("migration operation failed", ['message'=> $e->getMessage(), 'trace' => $e->getTrace()]);
            return false;
        }
    }

    public function seed(): bool
    {
        try {
            $this->manager->seed($this->env);
        } catch (Exception $e) {
            $this->logger->error("seed operations failed", ['message'=> $e->getMessage(), 'trace' => $e->getTrace()]);
            return false;
        }
        return true;
    }
}

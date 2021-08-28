<?php

namespace JdsDemoPlugin\Services\Persistence;

use Phinx\Migration\Manager;

class MigrationManager implements IMigrationManager
{
    private string $env;
    private Manager $manager;

    public function __construct(string $env, Manager $manager)
    {
        $this->manager = $manager;
        $this->env = $env;
    }

    public function migrate(): bool
    {
        $this->manager->migrate($this->env);
        $result = $this->manager->printStatus($this->env);

        // if no true values are returned, then there are no no missing or down migrations
        // -- so, success (return true)
        return ! in_array(true, array_values($result), true);
    }
}

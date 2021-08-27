<?php

namespace JdsDemoPlugin\Services\Persistence;

use Phinx\Console\PhinxApplication;
use Phinx\Wrapper\TextWrapper;

class MigrationManagerFactory implements IMigrationManagerFactory
{
    private string $configPath;

    public function __construct(string $configPath)
    {
        $this->configPath = $configPath;
    }

    public function create(string $configPath = null): MigrationManager
    {
        return new MigrationManager($configPath ?? $this->configPath, new TextWrapper(new PhinxApplication()));
    }
}

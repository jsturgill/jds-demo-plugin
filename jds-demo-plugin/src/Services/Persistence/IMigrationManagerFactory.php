<?php

namespace JdsDemoPlugin\Services\Persistence;

interface IMigrationManagerFactory
{
    /**
     * @param ?array<string,mixed> $config
     * @param ?string $env
     * @return MigrationManager
     */
    public function create(array $config = null, string $env = null): IMigrationManager;
}

<?php

namespace JdsDemoPlugin\Services\Persistence;

interface IMigrationManagerFactory
{
    /**
     * @param ?array<string,mixed> $config
     * @param ?string $env
     * @return IMigrationManager
     */
    public function create(array $config = null, ?string $env = null): IMigrationManager;
}

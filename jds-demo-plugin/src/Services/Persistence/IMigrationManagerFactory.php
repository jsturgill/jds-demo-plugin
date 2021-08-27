<?php

namespace JdsDemoPlugin\Services\Persistence;

interface IMigrationManagerFactory
{
    public function create(): MigrationManager;
}

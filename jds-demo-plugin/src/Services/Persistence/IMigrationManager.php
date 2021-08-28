<?php

namespace JdsDemoPlugin\Services\Persistence;

interface IMigrationManager
{
    /**
     * @return bool true if migrations succeeded
     */
    public function migrate(): bool;
}

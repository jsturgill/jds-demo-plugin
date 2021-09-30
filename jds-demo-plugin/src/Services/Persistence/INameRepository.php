<?php

namespace JdsDemoPlugin\Services\Persistence;

interface INameRepository
{
    /**
     * @param string[] $namesToExclude
     * @return string
     */
    public function getRandomName(array $namesToExclude = []): string;
}

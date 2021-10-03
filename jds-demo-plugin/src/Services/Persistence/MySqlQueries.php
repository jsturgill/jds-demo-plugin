<?php

namespace JdsDemoPlugin\Services\Persistence;

class MySqlQueries
{
    private PersistenceConfig $persistenceConfig;
    public function __construct(PersistenceConfig $persistenceConfig)
    {
        $this->persistenceConfig = $persistenceConfig;
    }

    public function getRandomNameQuery(string $prefix = '', string $unescapedWhereConditions = ''): string
    {
        return 'select `name`
from ' . $prefix . $this->persistenceConfig->namesTable . '
where 1=1
' . $unescapedWhereConditions . '
order by rand() limit 1';
    }
}

<?php

namespace Helper;

use PDO;

trait DbModuleHelperTrait
{
    public function fetchAllFromDatabase(string $sql, int $mode = PDO::FETCH_BOTH, $extra = null): array
    {
        $statement = $this->getModule('Db')->dbh->query($sql);
        return (null !== $extra)
            ? $statement->fetchAll($mode, $extra)
            : $statement->fetchAll($mode);
    }
}

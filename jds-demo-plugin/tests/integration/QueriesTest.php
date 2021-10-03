<?php

/** @noinspection PhpIllegalPsrClassPathInspection */

use JdsDemoPlugin\Services\Persistence\MySqlQueries;
use JdsDemoPlugin\Services\Persistence\PersistenceConfig;
use Codeception\Test\Unit;

class QueriesTest extends Unit
{
    protected IntegrationTester $tester;

    public function _before()
    {
        /** @var PersistenceConfig $persistenceConfig */
        $persistenceConfig = $this->tester->getDiContainer()->get(PersistenceConfig::class);
        $this->tester->seeInDatabase($persistenceConfig->namesTable, ['name' => 'Alice']);
        $this->tester->seeInDatabase($persistenceConfig->namesTable, ['name' => 'Bob']);
        $this->tester->seeInDatabase($persistenceConfig->namesTable, ['name' => 'Carol']);
    }

    public function testQueryUsesWhereCondition()
    {
        /** @var MySqlQueries $queries */
        $queries = $this->tester->getDiContainer()->get(MySqlQueries::class);

        // exclude alice and bob
        $sql = $queries->getRandomNameQuery('', "and name not in ('Alice', 'Bob')");
        $rows = $this->tester->fetchAllFromDatabase($sql);
        $this->assertEquals(1, count($rows), "Expected 1 row, found " . count($rows));
        $this->assertEquals('Carol', $rows[0]['name'], "Unexpected row value: " . json_encode($rows[0]));

        // exclude carol and bob
        $sql = $queries->getRandomNameQuery('', "and name not in ('Carol', 'Bob')");
        $rows = $this->tester->fetchAllFromDatabase($sql);
        $this->assertEquals(1, count($rows), "Expected 1 row, found " . count($rows));
        $this->assertEquals('Alice', $rows[0]['name'], "Unexpected row value: " . json_encode($rows[0]));

        // exclude carol and alice
        $sql = $queries->getRandomNameQuery('', "and name not in ('Carol', 'Alice')");
        $rows = $this->tester->fetchAllFromDatabase($sql);
        $this->assertEquals(1, count($rows), "Expected 1 row, found " . count($rows));
        $this->assertEquals('Bob', $rows[0]['name'], "Unexpected row value: " . json_encode($rows[0]));
    }

    public function testQueryIsLikelyRandom()
    {
        /** @var MySqlQueries $queries */
        $queries = $this->tester->getDiContainer()->get(MySqlQueries::class);
        $fetched = [];
        $sql = $queries->getRandomNameQuery();
        $attempts = 0;
        $limit = 100;
        while (count($fetched) < 3 && $attempts < $limit) {
            $rows = $this->tester->fetchAllFromDatabase($sql);
            $fetched[$rows[0]['name']] = null;
            $attempts++;
        }
        $this->assertEquals(3, count($fetched), 'Failed to fetch all 3 names after ' . $limit . ' attempts');
    }
}

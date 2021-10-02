<?php

namespace JdsDemoPlugin\Services\Persistence;

use JdsDemoPlugin\Services\Persistence\Exceptions\QueryFailureException;
use Psr\Log\LoggerInterface;

class WpDbNameRepository implements INameRepository
{
    /**
     * @var \wpdb
     * @see https://developer.wordpress.org/reference/classes/wpdb/
     * @noinspection PhpFullyQualifiedNameUsageInspection
     */
    private \wpdb $wpdb;
    private MySqlQueries $mySqlQueries;
    private LoggerInterface $logger;

    public function __construct(
        \wpdb $wpdb,
        MySqlQueries $mySqlQueries,
        LoggerInterface               $logger
    ) {
        $this->wpdb = $wpdb;
        $this->mySqlQueries = $mySqlQueries;
        $this->logger = $logger;
    }

    /**
     * @throws QueryFailureException
     */
    public function getRandomName(array $namesToExclude = []): string
    {
        $whereClause = (count($namesToExclude) > 0)
            ? 'and upper(name) not in (' . join(", ", array_fill(0, count($namesToExclude), '%s')) . ')'
            : '';
        $namesToExclude = array_map(fn ($name) => mb_strtoupper($name), $namesToExclude);

        $rawSqlString = $this->mySqlQueries->getRandomNameQuery($this->wpdb->prefix, $whereClause);
        $preparedSql = $this->wpdb->prepare($rawSqlString, $namesToExclude);

        if (!is_string($preparedSql)) {
            throw new QueryFailureException("wpdb failed to process sql: $rawSqlString");
        }

        $this->logger->debug('getRandomName prepared query: ' . $preparedSql);

        /**
         * @var array[] $resultsArray
         */
        $resultsArray = $this->wpdb->get_results($preparedSql, 'ARRAY_A');

        if (count($resultsArray) !== 1) {
            throw new QueryFailureException("Expected exactly one result, found " . count($resultsArray));
        }

        $result = $resultsArray[0]['name'];

        if (!is_string($result)) {
            throw new QueryFailureException("Returned name was not a string -- found " . gettype($result));
        }

        return $result;
    }
}

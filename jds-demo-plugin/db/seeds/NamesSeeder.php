<?php


use Cake\Database\Expression\QueryExpression;
use Cake\Database\StatementInterface;
use Phinx\Db\Table;
use Phinx\Seed\AbstractSeed;

class NamesSeeder extends AbstractSeed
{
    // names from 2020 given to 1,000 or more children according to ssa.gov
    public const SOURCE = 'names.txt';
    public const CHUNK_SIZE = 500;
    private string $prefix;

    /**
     * Safe insert
     *
     * Ensures duplicates are not entered (barring race conditions that are not expected to be a concern).
     * @param Table $table
     * @param array<string,null> $listOfNames
     * @return array<string,null> empty
     */
    private function insertNames(Table $table, array $listOfNames): array
    {
        // fetch all names from the database that match the chunk
        // -- trying to insert already-present values would result in a duplicate key error
        $query = $table->getAdapter()
            ->getQueryBuilder()
            ->select('name')
            ->from($this->prefix . 'jdsdp_names')
            ->where(function (QueryExpression $exp) use ($listOfNames) {
                return $exp->in('name', array_keys($listOfNames));
            });

        // remove entries from the "set" that are already in the database
        foreach ($query->execute() as $result) {
            unset($listOfNames[$result['name']]);
        }

        // if every chunk item is already in the database
        if (0 === count($listOfNames)) {
            return [];
        }

        // insert the remainder
        $namesToInsert = array_map(fn ($name) => ['name' => $name], array_keys($listOfNames));
        $table->insert($namesToInsert)
            ->saveData();
        return [];
    }

    /**
     * Load a list of names
     *
     * @link https://book.cakephp.org/phinx/0/en/seeding.html
     * @link https://www.ssa.gov/oact/babynames/limits.html
     * @throws Exception
     */
    public function run()
    {
        if (false === $file = fopen(__DIR__ . DIRECTORY_SEPARATOR . NamesSeeder::SOURCE, 'r')) {
            throw new Exception("Unable to open name source file: " . self::SOURCE);
        }


        global $wpdb;
        $prefix = $wpdb->prefix;
        $this->prefix = $prefix;
        $table = $this->table($prefix . 'jdsdp_names');
        $data = [];

        while (!feof($file)) {
            $name = fgets($file);

            if (false === $name) {
                continue;
            }

            $name = trim($name);

            if ('' === $name) {
                continue;
            }

            // abuse the array into a makeshift set
            $data[$name] = null;

            if (count($data) >= self::CHUNK_SIZE) {
                $data = $this->insertNames($table, $data);
            }
        }

        // insert the remainder
        if (count($data) >= 1) {
            $data = $this->insertNames($table, $data);
        }
    }
}

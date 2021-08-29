<?php

namespace JdsDemoPlugin\Tests;

use Codeception\Test\Unit;
use JdsDemoPlugin\Exceptions\CommandFailureException;
use JdsDemoPlugin\Services\Persistence\IMigrationManager;
use JdsDemoPlugin\Services\Persistence\IMigrationManagerFactory;
use JdsDemoPlugin\Services\Persistence\MigrationManagerFactory;
use PDO;
use Psr\Log\LoggerInterface;
use UnitTester;

class MigrationsTest extends Unit
{
    protected UnitTester $tester;
    private const TEST_PHINX_CONFIG_PATH_PARTIAL = 'phinx.test.php';
    private const WORDPRESS_PHINX_CONFIG_PATH_PARTIAL = 'phinx.wordpress.php';
    private const NAMES_SOURCE_FILE_PATH_PARTIAL = 'names.txt';
    private const PHINX_ENVIRONMENT = 'test';

    private ?IMigrationManagerFactory $migrationManagerFactory = null;

    private const TABLE_PREFIX = 'jdsdp_';
    private const TABLE_NAMES = self::TABLE_PREFIX . 'names';

    private const KEY_CONNECTION = 'connection';
    private const KEY_MANAGER = 'manager';
    private const KEY_CONFIG = 'config';
    private const KEY_PATHS = 'paths';
    private const KEY_SEEDS = 'seeds';

    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
    }

    private function getPhinxConfigPath(string $partial): string
    {
        return $this->tester->getPluginRoot()
            . DIRECTORY_SEPARATOR
            . $partial;
    }

    protected function _before(): void
    {
        if (null === $this->migrationManagerFactory) {
            /** @var LoggerInterface $logger */
            $logger = $this->tester->getDiContainer()->get(LoggerInterface::class);
            $this->migrationManagerFactory = new MigrationManagerFactory([], $logger);
        }
        $this->setupFakeWpEnvironment();
    }

    protected function _after(): void
    {
        $this->tearDownFakeWpEnvironment();
    }

    /**
     * @return array<string,object>
     */
    private function getDbConnectionAndManagerInstance(): array
    {
        $sqlite = new PDO('sqlite::memory:', null, null, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);

        $config = require $this->getPhinxConfigPath(MigrationsTest::TEST_PHINX_CONFIG_PATH_PARTIAL);
        $config[MigrationManagerFactory::KEY_ENVIRONMENTS][self::PHINX_ENVIRONMENT] = [
            'adapter' => 'sqlite',
            'connection' => $sqlite
        ];

        $migrationManager = $this->migrationManagerFactory
            ->create($config, self::PHINX_ENVIRONMENT);
        return [
            self::KEY_CONNECTION => $sqlite,
            self::KEY_MANAGER => $migrationManager,
            self::KEY_CONFIG => $config];
    }

    private function setupFakeWpEnvironment(): void
    {
        global $wpdb;
        $wpdb = $this->make('stdClass', ['prefix' => '']);
    }

    private function tearDownFakeWpEnvironment(): void
    {
        global $wpdb;
        $wpdb = null;
    }

    public function testWordpressConfigThrowsExceptionOutsideOfWordPress()
    {
        $this->tearDownFakeWpEnvironment();
        $this->expectException(CommandFailureException::class);
        include($this->getPhinxConfigPath(MigrationsTest::WORDPRESS_PHINX_CONFIG_PATH_PARTIAL));
        $this->assertEquals(0, 1, "Expected exception from loading phinx.php outside of WP not thrown");
    }

    public function testMigrationsCreateTableNames()
    {
        $dbEntities = $this->getDbConnectionAndManagerInstance();

        /** @var PDO $conn */
        $conn = $dbEntities[self::KEY_CONNECTION];

        /** @var IMigrationManager $manager */
        $manager = $dbEntities[self::KEY_MANAGER];

        $result = $manager->migrate();
        $this->assertEquals(true, $result, 'migration manager reported failures');

        $statement = $conn->query('select * from ' . self::TABLE_NAMES);

        $result = $statement->fetchAll();

        $this->assertEquals(1, count($result));
        $this->assertEquals('World', $result[0]['name']);

        // clean up
        $statement = null;
        $conn = null;
    }

    public function testSeedsAddNames()
    {
        $dbEntities = $this->getDbConnectionAndManagerInstance();

        /** @var PDO $conn */
        $conn = $dbEntities[self::KEY_CONNECTION];

        /** @var IMigrationManager $manager */
        $manager = $dbEntities[self::KEY_MANAGER];

        $seedsPath = $dbEntities[self::KEY_CONFIG][self::KEY_PATHS][self::KEY_SEEDS];
        $nameFilePath = $seedsPath . DIRECTORY_SEPARATOR . self::NAMES_SOURCE_FILE_PATH_PARTIAL;
        $file = fopen($nameFilePath, 'r');
        if (false === $file) {
            $this->fail("Unable to load name source: " . $nameFilePath);
        }

        // "World" was added in the migration
        $expectedLineCount = 1;

        while (!feof($file)) {
            if (false !== $line = fgets($file)) {
                // added in Migration
                if (trim($line) === "World") {
                    continue;
                }
                // assumes no duplicates
                $expectedLineCount++;
            }
        }

        $result = $manager->migrate();
        $this->assertEquals(true, $result, 'migration failed during seeds test');

        $result = $manager->seed();
        $this->assertEquals(true, $result, 'seeding failed');

        $statement = $conn->query('select count(*) as nameCount from ' . self::TABLE_NAMES);

        $result = $statement->fetchAll();

        // presumably
        $this->assertEquals($expectedLineCount, $result[0][0], "expected $expectedLineCount names after seeding, found " . $result[0]['nameCount']);

        // clean up
        $statement = null;
        $conn = null;
    }
}

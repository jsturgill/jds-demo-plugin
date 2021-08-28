<?php

namespace JdsDemoPlugin\Tests;

use Codeception\Test\Unit;
use JdsDemoPlugin\Exceptions\CommandFailureException;
use JdsDemoPlugin\Exceptions\InvalidArgumentException;
use JdsDemoPlugin\Services\Persistence\IMigrationManagerFactory;
use JdsDemoPlugin\Services\Persistence\MigrationManagerFactory;
use PDO;
use UnitTester;

class MigrationsTest extends Unit
{
    protected UnitTester $tester;
    public const TEST_PHINX_CONFIG_PATH_PARTIAL = 'phinx.test.php';
    public const WORDPRESS_PHINX_CONFIG_PATH_PARTIAL = 'phinx.wordpress.php';
    public const PHINX_ENVIRONMENT = 'test';
    private IMigrationManagerFactory $migrationManagerFactory;

    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->migrationManagerFactory = new MigrationManagerFactory([]);
    }

    private function getPhinxConfigPath(string $partial): string
    {
        return $this->tester->getPluginRoot()
            . DIRECTORY_SEPARATOR
            . $partial;
    }

    protected function _before(): void
    {
        $this->setupFakeWpEnvironment();
    }

    protected function _after(): void
    {
        $this->tearDownFakeWpEnvironment();
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

    /**
     * @throws InvalidArgumentException
     */
    public function testMigrationsCreateTableNames()
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
        $result = $migrationManager->migrate();
        $this->assertEquals(true, $result);

        $result = $sqlite->query('SELECT * FROM jdsdp_names')->fetchAll();
        var_dump($result);
        $this->assertEquals(1, count($result));
        $this->assertEquals('World', $result[0]['name']);
    }
}

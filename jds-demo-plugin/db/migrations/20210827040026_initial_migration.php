<?php

declare(strict_types=1);

use JdsDemoPlugin\Services\Persistence\PersistenceConfig;
use Phinx\Migration\AbstractMigration;

final class InitialMigration extends AbstractMigration
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-change-method
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function change(): void
    {
        global $wpdb;
        $persistenceConfig = new PersistenceConfig();
        $prefix = $wpdb->prefix;
        $table = $this->table($prefix . $persistenceConfig->namesTable);
        $table->addColumn('name', 'string', ['limit' => 255])
            ->addIndex(['name'], ['unique' => true])
            ->create();
        if ($this->isMigratingUp()) {
            $table->insert([['id' => 1, 'name' => 'World']])
                      ->save();
        }
    }
}

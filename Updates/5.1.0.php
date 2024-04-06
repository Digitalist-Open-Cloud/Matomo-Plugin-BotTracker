<?php

/**
 * BotTracker, a Matomo plugin by Digitalist Open Tech
 * Based on the work of Thomas--F (https://github.com/Thomas--F)
 * @link https://github.com/digitalist-se/BotTracker
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\BotTracker;

use Piwik\Updater;
use Piwik\Updates as PiwikUpdates;
use Piwik\Updater\Migration;
use Piwik\Updater\Migration\Factory as MigrationFactory;

/**
 * Update for version 5.1.0.
 */
class Updates_5_1_0 extends PiwikUpdates
{
    /**
     * @var MigrationFactory
     */
    private $migration;

    public function __construct(MigrationFactory $factory)
    {
        $this->migration = $factory;
    }

    /**
     * @param Updater $updater
     * @return Migration\Db[]
     */
    public function getMigrations(Updater $updater)
    {
        $migrations = [];

        $migrations[] = $this->migration->db->addColumn(
            'bot_db',
            'botType',
            'TINYINT(0) UNSIGNED NULL DEFAULT 0'
        );
        $migrations[] = $this->migration->db->changeColumnType('bot_db', 'botName', 'VARCHAR(256)');
        $migrations[] = $this->migration->db->changeColumnType('bot_db', 'botKeyword', 'VARCHAR(256)');
        $migrations[] = $this->migration->db->changeColumnType('bot_db_stat', 'page', 'VARCHAR(256)');
        $migrations[] = $this->migration->db->changeColumnType('bot_db_stat', 'useragent', 'VARCHAR(256)');

        $migrations[] = $this->migration->db->createTable('bot_visits', [
            'id' => 'bigint unsigned NOT NULL AUTO_INCREMENT',
            'botId' => 'INT UNSIGNED',
            'idsite' => 'INT UNSIGNED',
            'date' => 'TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP',

        ], $primaryKey = 'id');

        $migrations[] = $this->migration->db->createTable('bot_type', [
            'id' => 'INT(11) NOT NULL AUTO_INCREMENT',
            'name' => 'VARCHAR(256) NOT NULL',
            'date' => 'TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP',

        ], $primaryKey = 'id');

        $migrations[] = $this->migration->db->insert('bot_type', ['name' => 'Monitoring & Analytics']);
        $migrations[] = $this->migration->db->insert('bot_type', ['name' => 'Search Engine Optimization']);
        $migrations[] = $this->migration->db->insert('bot_type', ['name' => 'Advertising & Marketing']);
        $migrations[] = $this->migration->db->insert('bot_type', ['name' => 'Page Preview']);
        $migrations[] = $this->migration->db->insert('bot_type', ['name' => 'Webhook']);
        $migrations[] = $this->migration->db->insert('bot_type', ['name' => 'Social network']);
        $migrations[] = $this->migration->db->insert('bot_type', ['name' => 'Scraper']);
        $migrations[] = $this->migration->db->insert('bot_type', ['name' => 'Copyright']);
        $migrations[] = $this->migration->db->insert('bot_type', ['name' => 'Search Engine Crawler']);
        $migrations[] = $this->migration->db->insert('bot_type', ['name' => 'AI Search Crawler']);
        $migrations[] = $this->migration->db->insert('bot_type', ['name' => 'AI Data Scraper']);
        $migrations[] = $this->migration->db->insert('bot_type', ['name' => 'AI Assistant']);
        $migrations[] = $this->migration->db->insert('bot_type', ['name' => 'Other']);
        return $migrations;
    }

    /**
     * Perform the incremental version update.
     *
     * This method should perform all updating logic. If you define queries in the `getMigrations()` method,
     * you must call {@link Updater::executeMigrations()} here.
     *
     * @param Updater $updater
     */
    public function doUpdate(Updater $updater)
    {
        $updater->executeMigrations(__FILE__, $this->getMigrations($updater));
    }
}

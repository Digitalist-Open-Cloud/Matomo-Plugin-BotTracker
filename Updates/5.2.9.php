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
 * Update for version 5.2.9.
 */
class Updates_5_2_9 extends PiwikUpdates
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
        $migrations[] = $this->migration->db->createTable('bot_device_detector_bots', [
            'id' => 'INT(11) NOT NULL AUTO_INCREMENT',
            'idsite' => 'INT UNSIGNED',
            'useragent' => 'VARCHAR(256) NOT NULL',
            'date' => 'TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP',
        ], $primaryKey = ['id']);
        $migrations[] = $this->migration->db->changeColumnType('bot_type', 'name', 'VARCHAR(256)');
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

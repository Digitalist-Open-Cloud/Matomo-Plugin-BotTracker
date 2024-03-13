<?php
/**
 * BotTracker, a Matomo plugin by Digitalist Open Tech
 * Based on the work of Thomas--F (https://github.com/Thomas--F)
 * @link https://github.com/digitalist-se/MatomoPlugin-BotTracker
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Plugins\BotTracker;

use Piwik\Common;
use Piwik\Db;
use Piwik\Plugins\SitesManager\API as APISitesManager;
use Piwik\Tracker;
use Piwik\Plugin;
use Piwik\Plugins\BotTracker\API as BotTrackerAPI;

class BotTracker extends \Piwik\Plugin
{
    public function postLoad()
    {
        $dir = Plugin\Manager::getPluginDirectory('BotTracker');
        require_once $dir . '/functions.php';
    }

    private function getDb()
    {
        return Db::get();
    }

    public function install()
    {
        $tableExists = false;

        // create new table "bot_db"
        $query = "CREATE TABLE `" . Common::prefixTable('bot_db') . "`
						 (`botId` INTEGER(10) UNSIGNED NOT NULL AUTO_INCREMENT,
						  `idsite` INTEGER(10) UNSIGNED NOT NULL,
						  `botName` VARCHAR(100) NOT NULL,
						  `botActive` BOOLEAN NOT NULL,
						  `botKeyword` VARCHAR(32) NOT NULL,
						  `botCount` INTEGER(10) UNSIGNED NOT NULL,
						  `botLastVisit` TIMESTAMP NOT NULL,
						  `extra_stats` BOOLEAN NOT NULL DEFAULT FALSE,
                          `botType` TINYINT(0) UNSIGNED NULL DEFAULT 0,
						  PRIMARY KEY(`botId`)
						)  DEFAULT CHARSET=utf8";

        // if the table already exist do not throw error. Could be installed twice...
        try {
            Db::exec($query);
        } catch (\Exception $e) {
            $tableExists = true;
        }

        if (!$tableExists) {
            $sites = APISitesManager::getInstance()->getSitesWithAdminAccess();
            foreach ($sites as $site) {
                BotTrackerAPI::insert_default_bots($site['idsite']);
            }
        }
        // Create bot_db_stat table.
        $query2 =  'CREATE TABLE IF NOT EXISTS `' . Common::prefixTable('bot_db_stat') . '`
						(
						`visitId` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			 			`botId` INTEGER(10) UNSIGNED NOT NULL,
			 			`idsite` INTEGER(10) UNSIGNED NOT NULL,
			 			`page` VARCHAR(100) NOT NULL,
			 			`visit_timestamp` TIMESTAMP NOT NULL,
			 			`useragent` VARCHAR(100) NOT NULL,

			 			PRIMARY KEY(`visitId`,`botId`,`idsite`)
						)  DEFAULT CHARSET=utf8';
        try {
            Db::exec($query2);
        } catch (\Exception $e) {
            throw $e;
        }

        // Create bot_type table
        $query3 =  'CREATE TABLE IF NOT EXISTS `' . Common::prefixTable('bot_type') . '`
        (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
             `name` VARCHAR(255) NOT NULL,
             `date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
             PRIMARY KEY(`id`)
            )  DEFAULT CHARSET=utf8';
        try {
            Db::exec($query3);
        } catch (\Exception $e) {
            throw $e;
        }

        $botTypes = [
            'Monitoring & Analytics',
            'Search Engine Optimization',
            'Advertising & Marketing',
            'Page Preview',
            'Webhook',
            'Social network',
            'Scraper',
            'Copyright',
            'Search Engine Crawler',
            'AI Search Crawler',
            'AI Data Scraper',
            'AI Assistant',
            'Other',
        ];
        $db = $this->getDb();
        try {
            foreach ($botTypes as $type) {
                $sql = sprintf(
                    'INSERT INTO ' . Common::prefixTable('bot_type') . ' (`name`) VALUES (?)'
                );
                $db->query($sql, [$type]);
            }
        } catch (\Exception $e) {
            throw $e;
        }

    }

    public function uninstall()
    {
        $query = "DROP TABLE `" . Common::prefixTable('bot_db') . "` ";
        Db::query($query);
        $query2 = "DROP TABLE `" . Common::prefixTable('bot_db_stat') . "` ";
        Db::query($query2);
        $query3 = "DROP TABLE `" . Common::prefixTable('bot_type') . "` ";
        Db::query($query3);
    }

    public function registerEvents()
    {
        return [
            'Tracker.isExcludedVisit'  => 'checkBot',
            'Translate.getClientSideTranslationKeys' => 'getClientSideTranslationKeys',
        ];
    }

    /**
     * Get translations strings to js.
     */
    public function getClientSideTranslationKeys(&$translationKeys)
    {
        $translationKeys[] = 'BotTracker_BotTracker';
        $translationKeys[] = 'BotTracker_PluginDescription';
        $translationKeys[] = 'BotTracker_insert_db';
        $translationKeys[] = 'BotTracker_NoOfActiveBots';
    }

    function checkBot(&$exclude, $request)
    {
        $userAgent = $request->getUserAgent();
        $idSite = $request->getIdSite();
        $currentTimestamp = gmdate("Y-m-d H:i:s");
        // max length of url can be 100 Bytes
        $currentUrl = substr($request->getParam('url'), 0, 100);

        $db = Tracker::getDatabase();
        $result = $db->fetchRow("SELECT `botId`, `extra_stats` FROM " . Common::prefixTable('bot_db') . "
		                        WHERE `botActive` = 1
		                        AND   `idSite` = ?
		                        AND   LOCATE(`botKeyword`,?) >0
						            LIMIT 1", [$idSite, $userAgent]);

        $botId = $result['botId'] ?? 0;
        if ($botId > 0) {
            $db->query("UPDATE `" . Common::prefixTable('bot_db') . "`
			               SET botCount = botCount + 1
			                 , botLastVisit = ?
			             WHERE botId = ?", [$currentTimestamp, $botId]);

            $exclude = true;

            if ($result['extra_stats'] > 0) {
                $query = "INSERT INTO `" . Common::prefixTable('bot_db_stat') . "`
					(idsite, botid, page, visit_timestamp, useragent) VALUES (?,?,?,?,?)";
                // max length of useragent can be 100 Bytes
                $params = [$idSite,$botId,$currentUrl,$currentTimestamp,substr($userAgent, 0, 100)];
                $db->query($query, $params);
            }
        }
    }
}

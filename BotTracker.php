<?php

/**
 * BotTracker, a Matomo plugin by Digitalist Open Tech
 * Based on the work of Thomas--F (https://github.com/Thomas--F)
 * @link https://github.com/digitalist-se/BotTracker
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\BotTracker;

use Piwik\Common;
use Piwik\Config;
use Piwik\Container\StaticContainer;
use Piwik\Db;
use Piwik\Plugins\SitesManager\API as APISitesManager;
use Piwik\Tracker;
use Piwik\Plugins\BotTracker\API as BotTrackerAPI;
use Piwik\DeviceDetector\DeviceDetectorFactory;

class BotTracker extends \Piwik\Plugin
{
    private function getDb()
    {
        return Db::get();
    }

    public function install()
    {
        $tableExists = false;
        $db = $this->getDb();

        // create new table "bot_db"
        $query = "CREATE TABLE `" . Common::prefixTable('bot_db') . "`
						 (`botId` INTEGER(10) UNSIGNED NOT NULL AUTO_INCREMENT,
						  `idsite` INTEGER(10) UNSIGNED NOT NULL,
						  `botName` VARCHAR(256) NOT NULL,
						  `botActive` BOOLEAN NOT NULL,
						  `botKeyword` VARCHAR(256) NOT NULL,
						  `botCount` INTEGER(10) UNSIGNED NOT NULL,
						  `botLastVisit` TIMESTAMP NOT NULL,
						  `extra_stats` BOOLEAN NOT NULL DEFAULT FALSE,
                          `botType` TINYINT(0) UNSIGNED NULL DEFAULT 0,
						  PRIMARY KEY(`botId`)
						)  DEFAULT CHARSET=utf8";

        // if the table already exist do not throw error. Could be installed twice...
        try {
            $db->exec($query);
        } catch (\Exception $e) {
            $tableExists = true;
        }

        if (!$tableExists) {
            $sites = APISitesManager::getInstance()->getSitesWithAdminAccess();
            foreach ($sites as $site) {
                BotTrackerAPI::insertDefaultBots($site['idsite']);
            }
        }
        // Create bot_db_stat table.
        $query2 =  'CREATE TABLE IF NOT EXISTS `' . Common::prefixTable('bot_db_stat') . '`
						(
						`visitId` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			 			`botId` INTEGER(10) UNSIGNED NOT NULL,
			 			`idsite` INTEGER(10) UNSIGNED NOT NULL,
			 			`page` VARCHAR(256) NOT NULL,
			 			`visit_timestamp` TIMESTAMP NOT NULL,
			 			`useragent` VARCHAR(256) NOT NULL,

			 			PRIMARY KEY(`visitId`,`botId`,`idsite`)
						)  DEFAULT CHARSET=utf8';
        try {
            $db->exec($query2);
        } catch (\Exception $e) {
            throw $e;
        }

        // Create bot_type table
        $query3 =  'CREATE TABLE IF NOT EXISTS `' . Common::prefixTable('bot_type') . '`
        (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
             `name` VARCHAR(256) NOT NULL,
             `date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
             PRIMARY KEY(`id`), UNIQUE(`name`)
            ) DEFAULT CHARSET=utf8';
        try {
            $db->exec($query3);
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
        try {
            foreach ($botTypes as $type) {
                $sql = sprintf(
                    'INSERT IGNORE INTO ' . Common::prefixTable('bot_type') . ' (`name`) VALUES (?)'
                );
                $db->query($sql, [$type]);
            }
        } catch (\Exception $e) {
            throw $e;
        }

        $query4 =  'CREATE TABLE IF NOT EXISTS `' . Common::prefixTable('bot_visits') . '`
        (
            `id` BIGINT UNSIGNED AUTO_INCREMENT,
            `botId` INT UNSIGNED,
            `idsite` INT UNSIGNED,
            `date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
             PRIMARY KEY(`id`)
            ) DEFAULT CHARSET=utf8';
        try {
            $db->exec($query4);
        } catch (\Exception $e) {
            throw $e;
        }

        $query5 =  'CREATE TABLE IF NOT EXISTS `' . Common::prefixTable('bot_device_detector_bots') . '`
        (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `idsite` INT UNSIGNED,
            `useragent` VARCHAR(256) NOT NULL,
            `date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
             PRIMARY KEY(`id`)
            ) DEFAULT CHARSET=utf8';
        try {
            $db->exec($query5);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function uninstall()
    {
        $db = $this->getDb();
        $query = "DROP TABLE `" . Common::prefixTable('bot_db') . "` ";
        $db->query($query);
        $query2 = "DROP TABLE `" . Common::prefixTable('bot_db_stat') . "` ";
        $db->query($query2);
        $query3 = "DROP TABLE `" . Common::prefixTable('bot_type') . "` ";
        $db->query($query3);
        $query4 = "DROP TABLE `" . Common::prefixTable('bot_visits') . "` ";
        $db->query($query4);
        $query5 = "DROP TABLE `" . Common::prefixTable('bot_device_detector_bots') . "` ";
        $db->query($query5);
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
        $translationKeys[] = 'BotTracker_AddNewBots';
        $translationKeys[] = 'BotTracker_Added';
        $translationKeys[] = 'BotTracker_BotActive';
        $translationKeys[] = 'BotTracker_BotCount';
        $translationKeys[] = 'BotTracker_BotKeyword';
        $translationKeys[] = 'BotTracker_BotLastVisit';
        $translationKeys[] = 'BotTracker_BotName';
        $translationKeys[] = 'BotTracker_BotTracker';
        $translationKeys[] = 'BotTracker_Bot_Tracker';
        $translationKeys[] = 'BotTracker_Bot_Tracker_Report_Stats';
        $translationKeys[] = 'BotTracker_Bot_Tracker_Report';
        $translationKeys[] = 'BotTracker_Bot_Tracker_Total_Over_Time_Deprecated_Report';
        $translationKeys[] = 'BotTracker_Config';
        $translationKeys[] = 'BotTracker_Delete';
        $translationKeys[] = 'BotTracker_DisplayWidget';
        $translationKeys[] = 'BotTracker_DisplayWidget_Deprecated_Report';
        $translationKeys[] = 'BotTracker_Documentation';
        $translationKeys[] = 'BotTracker_Error_empty';
        $translationKeys[] = 'BotTracker_Error_Fileimport_Not_Even';
        $translationKeys[] = 'BotTracker_Error_Fileimport_Upload';
        $translationKeys[] = 'BotTracker_ExtraStats';
        $translationKeys[] = 'BotTracker_File';
        $translationKeys[] = 'BotTracker_false';
        $translationKeys[] = 'BotTracker_hits_by_Bot';
        $translationKeys[] = 'BotTracker_Importfile';
        $translationKeys[] = 'BotTracker_Import_help_format';
        $translationKeys[] = 'BotTracker_Import_help_headline';
        $translationKeys[] = 'BotTracker_Import_help_line1';
        $translationKeys[] = 'BotTracker_Import_help_line2';
        $translationKeys[] = 'BotTracker_Import_help_line3';
        $translationKeys[] = 'BotTracker_insert_db';
        $translationKeys[] = 'BotTracker_Message_bot_inserted';
        $translationKeys[] = 'BotTracker_Message_deleted';
        $translationKeys[] = 'BotTracker_New';
        $translationKeys[] = 'BotTracker_NoOfActiveBots';
        $translationKeys[] = 'Bot_Tracker_OtherBots';
        $translationKeys[] = 'Bot_Tracker_OtherBotsDocumentation';
        $translationKeys[] = 'BotTracker_PluginDescription';
        $translationKeys[] = 'BotTracker_ReportDocumentation';
        $translationKeys[] = 'BotTracker_save_changes';
        $translationKeys[] = 'BotTracker_SelectAWebsite';
        $translationKeys[] = 'BotTracker_send_file';
        $translationKeys[] = 'BotTracker_Single';
        $translationKeys[] = 'BotTracker_Top_10_Bots';
        $translationKeys[] = 'BotTracker_Top_10_Bots_Deprecated_Report';
        $translationKeys[] = 'BotTracker_Top10';
        $translationKeys[] = 'BotTracker_TopTenDocumentation';
        $translationKeys[] = 'BotTracker_true';
        $translationKeys[] = 'BotTracker_Visit_Timestamp';
        $translationKeys[] = 'BotTracker_Widgets';
    }

    public function checkBot(&$exclude, $request)
    {
        $userAgent = $request->getUserAgent();
        $idSite = $request->getIdSite();
        $currentTimestamp = gmdate("Y-m-d H:i:s");
        // max length of url can be 256 Bytes
        $currentUrl = substr($request->getParam('url'), 0, 256);

        $db = Tracker::getDatabase();
        $result = $db->fetchRow("SELECT `botId`, `extra_stats` FROM " . Common::prefixTable('bot_db') . "
		                        WHERE `botActive` = 1
		                        AND   `idSite` = ?
		                        AND   LOCATE(`botKeyword`,?) >0
						            LIMIT 1", [$idSite, $userAgent]);

        $botId = $result['botId'] ?? 0;

        if ($botId > 0) {
            // New since 5.1.0
            $query = "INSERT INTO `" . Common::prefixTable('bot_visits') . "`
            (botid, idsite, date) VALUES (?,?,?)";
            $params = [$botId, $idSite, $currentTimestamp];
            $db->query($query, $params);

            // @deprecated since v5.2.0
            $db->query("UPDATE `" . Common::prefixTable('bot_db') . "`
			               SET botCount = botCount + 1
			                 , botLastVisit = ?
			             WHERE botId = ?", [$currentTimestamp, $botId]);

            $exclude = true;

            if ($result['extra_stats'] > 0) {
                $query = "INSERT INTO `" . Common::prefixTable('bot_db_stat') . "`
					(idsite, botid, page, visit_timestamp, useragent) VALUES (?,?,?,?,?)";
                // max length of useragent can be 256 Bytes
                $params = [$idSite,$botId,$currentUrl,$currentTimestamp,substr($userAgent, 0, 256)];
                $db->query($query, $params);
            }
        } else {
            $userAgent = $request->getUserAgent();
            $deviceDetector = StaticContainer::get(DeviceDetectorFactory::class)->makeInstance(
                $userAgent,
                $request->getClientHints()
            );
            $deviceDetector->parse();
            if ($deviceDetector->isBot()) {
                $settings = new SystemSettings();
                $botsLogging = $settings->trackDeviceDetectorBots->getValue();
                // If set in config, this overrides setting in UI.
                if (Config::getInstance()->BotTracker['track_device_detector_bots'] == 1) {
                    $botsLogging = 1;
                }
                if ($botsLogging) {
                        $currentTimestamp = gmdate("Y-m-d H:i:s");
                        $query = "INSERT INTO `" . Common::prefixTable('bot_device_detector_bots') . "`
                        (idsite, useragent, date) VALUES (?,?,?)";
                        $params = [$idSite, substr($userAgent, 0, 256), $currentTimestamp];
                        $db->query($query, $params);
                }
                $exclude = true;
            }
        }
    }
}

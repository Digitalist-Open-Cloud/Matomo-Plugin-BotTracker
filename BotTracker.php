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
use Piwik\DbHelper;
use Piwik\Plugins\SitesManager\API as APISitesManager;
use Piwik\Tracker;
use Piwik\Plugins\BotTracker\API as BotTrackerAPI;
use Piwik\DeviceDetector\DeviceDetectorFactory;

class BotTracker extends \Piwik\Plugin
{
    public const TABLES = ['bot_db', 'bot_db_stat', 'bot_type', 'bot_visits', 'bot_device_detector_bots'];

    private const BOT_TYPES = [
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

    public function install()
    {
        $isFirstInstall = !Db::fetchOne('SHOW TABLES LIKE ?', [Common::prefixTable('bot_db')]);

        // DbHelper creates the tables with the character set and engine Matomo is configured for.
        DbHelper::createTable('bot_db', "
            `botId` INTEGER(10) UNSIGNED NOT NULL AUTO_INCREMENT,
            `idsite` INTEGER(10) UNSIGNED NOT NULL,
            `botName` VARCHAR(256) NOT NULL,
            `botActive` BOOLEAN NOT NULL,
            `botKeyword` VARCHAR(256) NOT NULL,
            `botCount` INTEGER(10) UNSIGNED NOT NULL,
            `botLastVisit` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `extra_stats` BOOLEAN NOT NULL DEFAULT FALSE,
            `botType` TINYINT UNSIGNED NULL DEFAULT 0,
            PRIMARY KEY(`botId`)");

        DbHelper::createTable('bot_db_stat', "
            `visitId` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            `botId` INTEGER(10) UNSIGNED NOT NULL,
            `idsite` INTEGER(10) UNSIGNED NOT NULL,
            `page` VARCHAR(256) NOT NULL,
            `visit_timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `useragent` VARCHAR(256) NOT NULL,
            PRIMARY KEY(`visitId`,`botId`,`idsite`)");

        DbHelper::createTable('bot_type', "
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `name` VARCHAR(256) NOT NULL,
            `date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY(`id`), UNIQUE(`name`)");

        DbHelper::createTable('bot_visits', "
            `id` BIGINT UNSIGNED AUTO_INCREMENT,
            `botId` INT UNSIGNED,
            `idsite` INT UNSIGNED,
            `date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY(`id`)");

        DbHelper::createTable('bot_device_detector_bots', "
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `idsite` INT UNSIGNED,
            `useragent` VARCHAR(256) NOT NULL,
            `date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY(`id`)");

        foreach (self::BOT_TYPES as $type) {
            Db::query('INSERT IGNORE INTO ' . Common::prefixTable('bot_type') . ' (`name`) VALUES (?)', [$type]);
        }

        if ($isFirstInstall) {
            foreach (APISitesManager::getInstance()->getSitesWithAdminAccess() as $site) {
                BotTrackerAPI::insertDefaultBots($site['idsite']);
            }
        }
    }

    public function uninstall()
    {
        Db::dropTables(array_map([Common::class, 'prefixTable'], self::TABLES));
    }

    public function getTablesInstalled(&$allTablesInstalled)
    {
        foreach (self::TABLES as $table) {
            $allTablesInstalled[] = Common::prefixTable($table);
        }
    }

    /**
     * Removes the bots of a website that is deleted, and what was counted for them.
     */
    public function deleteSiteData($idSite)
    {
        foreach (['bot_db', 'bot_db_stat', 'bot_visits', 'bot_device_detector_bots'] as $table) {
            Db::query('DELETE FROM ' . Common::prefixTable($table) . ' WHERE idsite = ?', [$idSite]);
        }
    }

    public function registerEvents()
    {
        return [
            'Tracker.isExcludedVisit'  => 'checkBot',
            'Db.getTablesInstalled' => 'getTablesInstalled',
            'SitesManager.deleteSite.end' => 'deleteSiteData',
            'AssetManager.getStylesheetFiles' => 'getStylesheetFiles',
            'Translate.getClientSideTranslationKeys' => 'getClientSideTranslationKeys',
        ];
    }

    public function getStylesheetFiles(&$stylesheets)
    {
        $stylesheets[] = 'plugins/BotTracker/stylesheets/menuIcon.less';
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
        $currentUrl = mb_substr((string) $request->getParam('url'), 0, 256, 'UTF-8');

        $db = Tracker::getDatabase();
        $result = $db->fetchRow("SELECT `botId`, `extra_stats` FROM " . Common::prefixTable('bot_db') . "
		                        WHERE `botActive` = 1
		                        AND   `idSite` = ?
		                        AND   LOCATE(`botKeyword`,?) >0
						            LIMIT 1", [$idSite, $userAgent]);

        $botId = $result['botId'] ?? 0;

        if ($botId > 0) {
            $query = "INSERT INTO `" . Common::prefixTable('bot_visits') . "`
            (botid, idsite, date) VALUES (?,?,?)";
            $params = [$botId, $idSite, $currentTimestamp];
            $db->query($query, $params);

            $exclude = true;

            if ($result['extra_stats'] > 0) {
                $query = "INSERT INTO `" . Common::prefixTable('bot_db_stat') . "`
					(idsite, botid, page, visit_timestamp, useragent) VALUES (?,?,?,?,?)";
                $params = [$idSite, $botId, $currentUrl, $currentTimestamp, mb_substr($userAgent, 0, 256, 'UTF-8')];
                $db->query($query, $params);
            }
        } else {
            $deviceDetector = StaticContainer::get(DeviceDetectorFactory::class)->makeInstance(
                $userAgent,
                $request->getClientHints()
            );
            $deviceDetector->parse();
            if ($deviceDetector->isBot()) {
                $settings = new SystemSettings();
                $botsLogging = $settings->trackDeviceDetectorBots->getValue();
                // If set in config, this overrides setting in UI.
                if ($this->__isset('track_device_detector_bots')) {
                    if (Config::getInstance()->BotTracker['track_device_detector_bots'] == 1) {
                        $botsLogging = 1;
                    }
                }
                if ($botsLogging) {
                        $currentTimestamp = gmdate("Y-m-d H:i:s");
                        $query = "INSERT INTO `" . Common::prefixTable('bot_device_detector_bots') . "`
                        (idsite, useragent, date) VALUES (?,?,?)";
                        $params = [$idSite, mb_substr($userAgent, 0, 256, 'UTF-8'), $currentTimestamp];
                        $db->query($query, $params);
                }
                $exclude = true;
            }
        }
    }

    /**
     * @param string $key
     * @return bool
     */
    public function __isset(string $key): bool
    {
        return $this->has($key);
    }

    /**
     * @param string $key
     * @return bool
     */
    public function has(string $key): bool
    {
        return isset(Config::getInstance()->BotTracker[$key]);
    }
}

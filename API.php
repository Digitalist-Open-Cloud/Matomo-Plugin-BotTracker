<?php

/**
 * BotTracker, a Matomo plugin by Digitalist Open Tech
 * Based on the work of Thomas--F (https://github.com/Thomas--F)
 * @link https://github.com/digitalist-se/MatomoPlugin-BotTracker
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\BotTracker;

use Piwik\Db;
use Piwik\Common;
use Piwik\DataTable;
use Piwik\Site;
use Piwik\Date;
use Piwik\Piwik;

/**
 * @package Piwik_BotTracker
 */
class API extends \Piwik\Plugin\API
{
    private static $instance = null;

    public function __construct()
    {
        $dir = \Piwik\Plugin\Manager::getPluginDirectory('BotTracker');
        require_once $dir . '/functions.php';
    }

    public static function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public static function getAllBotData($idSite)
    {
        $rows = Db::get()->fetchAll("SELECT * FROM " . Common::prefixTable('bot_db') . " WHERE idSite= ? ORDER BY `botId`", [$idSite]);
        $rows = self::convertBotLastVisitToLocalTime($rows, $idSite);
        // convert this array to a DataTable object
        return DataTable::makeFromIndexedArray($rows);
    }

    public static function getAllBotDataForConfig($idsite)
    {
        $rows = Db::get()->fetchAll("SELECT `idsite`, `botId`, `botName`, `botActive`, `botKeyword`, `extra_stats` FROM " . Common::prefixTable('bot_db') . " WHERE `idsite` = ? ORDER BY `botId`", [$idsite]);

        return $rows;
    }


    public static function getActiveBotData($idSite)
    {
        $rows = Db::get()->fetchAll("SELECT `botName`, `botLastVisit`, `botCount` FROM " . Common::prefixTable('bot_db') . " WHERE `botActive` = 1 AND idSite= ? ORDER BY `botId`", [$idSite]);
        $rows = self::convertBotLastVisitToLocalTime($rows, $idSite);
        // convert this array to a DataTable object
        return DataTable::makeFromIndexedArray($rows);
    }

    public function getAllBotDataWithIcon($idSite)
    {
        $dataTable = $this->getAllBotData($idSite);
        $dataTable->renameColumn('botActive', 'label');

        $dataTable->filter('ColumnCallbackAddMetadata', ['label', 'logo', __NAMESPACE__ . '\getActiveIcon']);
        $dataTable->filter('ColumnCallbackReplace', ['label', function ($label) {
            return ' ';
        }]);
        $dataTable->queueFilter('ColumnCallbackAddMetadata', [[], 'logoWidth', function () {
            return 16;
        }]);
        $dataTable->queueFilter('ColumnCallbackAddMetadata', [[], 'logoHeight', function () {
            return 16;
        }]);

        return $dataTable;
    }


    public static function getAllBotDataPie($idSite)
    {
        $rows = Db::get()->fetchAll("SELECT `botName`, `botCount` FROM " . Common::prefixTable('bot_db') . " WHERE `idSite`= ? ORDER BY `botCount` DESC LIMIT 10", [$idSite]);

        $i = 0;
        $keys[0] = "";
        $values[0] = "";
        foreach ($rows as $row) {
            $keys[$i] = $row['botName'];
            $values[$i] = $row['botCount'];
            $i++;
        }
        $pieArray = array_combine($keys, $values);

        // convert this array to a DataTable object
        return DataTable::makeFromIndexedArray($pieArray);
    }

    public static function updateBot($botName, $botKeyword, $botActive, $botId, $extraStats)
    {
        Db::get()->query("UPDATE `" . Common::prefixTable('bot_db') . "`
		             SET `botName` = ?
		               , `botKeyword` = ?
		               , `botActive` = ?
		               , `extra_stats` = ?
		             WHERE `botId` = ?", [self::htmlentities2utf8($botName), self::htmlentities2utf8($botKeyword), $botActive, $extraStats, $botId]);
    }

    public static function insertBot($idSite, $botName, $botActive, $botKeyword, $extraStats)
    {
        Db::get()->query(
            "INSERT INTO `" . Common::prefixTable('bot_db') . "`
               (`idsite`,`botName`, `botActive`, `botKeyword`, `botCount`, `extra_stats`)
                VALUES (?,?,?,?,0,?)",
            [$idSite, self::htmlentities2utf8($botName), $botActive, self::htmlentities2utf8($botKeyword), $extraStats]
        );
    }

    public static function insertDefaultBots($idsite = 0)
    {
        $i = 0;
        if ($idsite <> 0) {
            Piwik::checkUserHasSuperUserAccess();

            $botList = [];
            $botList[] = ['Amazonbot','Amazonbot'];
            $botList[] = ['Qualys','Qualys'];
            $botList[] = ['bingbot','bingbot'];
            $botList[] = ['YandexBot','YandexBot'];
            $botList[] = ['AhrefsBot','AhrefsBot'];
            $botList[] = ['Ahrefs','Ahrefs'];
            $botList[] = ['curl','curl'];
            $botList[] = ['Wget','Wget'];
            $botList[] = ['Scrapy','Scrapy'];
            $botList[] = ['Googlebot-Image','Google-Image'];
            $botList[] = ['Googlebot-News','Googlebot-News'];
            $botList[] = ['Googlebot-Video','Googlebot-Video'];
            $botList[] = ['Storebot-Google','Storebot-Google'];
            $botList[] = ['Google-InspectionTool','Google-InspectionTool'];
            $botList[] = ['Google-Extended','Google-Extended'];
            $botList[] = ['GoogleOther','GoogleOther'];
            $botList[] = ['APIs-Google','APIs-Google'];
            $botList[] = ['AdsBot-Google-Mobile','AdsBot-Google-Mobile'];
            $botList[] = ['AdsBot-Google','AdsBot-Google'];
            $botList[] = ['Mediapartners-Google','Google AdSense'];
            $botList[] = ['Google-Safety','Google-Safety'];
            $botList[] = ['Googlebot','Googlebot'];
            $botList[] = ['Google-Read-Aloud','Google-Read-Aloud'];
            $botList[] = ['Google-Site-Verification','Google-Site-Verification'];
            $botList[] = ['AdIdxBot','AdIdxBot'];
            $botList[] = ['NewRelic','NewRelic'];
            $botList[] = ['Detectify','Detectify'];
            $botList[] = ['UptimeRobot','UptimeRobot'];
            $botList[] = ['SendGrid','SendGrid'];
            $botList[] = ['Applebot','Applebot'];
            $botList[] = ['PinterestBot','PinterestBot'];
            $botList[] = ['Pingdom','Pingdom'];
            $botList[] = ['Barkrowler','Barkrowler'];
            $botList[] = ['SEMrush','SEMrush'];
            $botList[] = ['GPTBot','GPTBot'];
            $botList[] = ['ChatGPT-User','ChatGPT-User'];
            $botList[] = ['Bytespider','Bytespider'];
            $botList[] = ['CCBot','CCBot'];
            $botList[] = ['FacebookBot','FacebookBot'];
            $botList[] = ['Google-Extended','Google-Extended'];
            $botList[] = ['Site24x7','Site24x7'];
            $botList[] = ['Stripe','Stripe'];
            $botList[] = ['Slackbot','Slackbot'];
            $botList[] = ['Proximic','Proximic'];
            $botList[] = ['okhttp','okhttp'];
            $botList[] = ['Python','Python'];
            $botList[] = ['SemrushBot','SemrushBot'];
            $botList[] = ['Chrome-Lighthouse','Chrome-Lighthouse'];
            $botList[] = ['Axios','Axios'];
            $botList[] = ['PetalBot','PetalBot'];
            $botList[] = ['CriteoBot','CriteoBot'];
            $botList[] = ['Baidu','Baidu'];
            $botList[] = ['ContentKing','ContentKing'];
            $botList[] = ['IAS crawler','IAS crawler'];
            $botList[] = ['Sucuri','Sucuri'];
            $botList[] = ['Seekport','Seekport'];
            $botList[] = ['Sogou','Sogou'];
            $botList[] = ['YahooMailProxy','YahooMailProxy'];

            foreach ($botList as $bot) {
                $botX = self::getBotByName($idsite, $bot[0]);
                if (empty($botX)) {
                    self::insertBot($idsite, $bot[0], 1, $bot[1], 0);
                    $i++;
                }
            }
        }

        return $i;
    }

    static function deleteBot($botId)
    {
        Db::get()->query("DELETE FROM `" . Common::prefixTable('bot_db') . "` WHERE `botId` = ?", [$botId]);
    }

    static function getBotByName($idSite, $botName)
    {
        $rows = Db::get()->fetchAll("SELECT * FROM " . Common::prefixTable('bot_db') . " WHERE `botName` = ? AND `idSite`= ? ORDER BY `botId`", [$botName, $idSite]);
        $rows = self::convertBotLastVisitToLocalTime($rows, $idSite);
        return $rows;
    }

    static function convertBotLastVisitToLocalTime($rows, $idSite)
    {
        // convert lastVisit to localtime
        $timezone = Site::getTimezoneFor($idSite);

        foreach ($rows as &$row) {
            if ($row['botLastVisit'] == '0000-00-00 00:00:00') {
                $row['botLastVisit'] = " - ";
            } elseif ($row['botLastVisit'] == '2000-01-01 00:00:00') {
                $row['botLastVisit'] = " - ";
            } else {
                $botLastVisit = Date::adjustForTimezone(strtotime($row['botLastVisit']), $timezone);
                    $row['botLastVisit'] = date('Y-m-d H:i:s', $botLastVisit);
            }
        }
        return $rows;
    }
    static function htmlentities2utf8($string)
    {
        $output = preg_replace_callback("/(&#[0-9]+;)/", function ($m) {
            return mb_convert_encoding($m[1], "UTF-8", "HTML-ENTITIES");
        }, $string);
            return html_entity_decode($output);
    }

    /**
     * Get Data for the Report "Top10"
     * @param int $idSite
     * @param string $period
     * @param string $date
     * @param bool|string $segment
     * @return DataTable
     */
    public function getTop10($idSite, $period, $date, $segment = false)
    {
        return $this->getAllBotDataPie($idSite);
    }
    /**
     * Get Data for the Report "BotTracker"
     * @param int $idSite
     * @param string $period
     * @param string $date
     * @param bool|string $segment
     * @return DataTable
     */
    public function getBotTracker($idSite, $period, $date, $segment = false)
    {
        return $this->getAllBotDataWithIcon($idSite);
    }


    /**
     * Get Data for Dashboard-Widget
     * @param int $idSite
     * @param string $period
     * @param string $date
     * @param bool|string $segment
     * @return DataTable
     */
    public function getBotTrackerAnzeige($idSite, $period, $date, $segment = false)
    {
        return $this->getActiveBotData($idSite);
    }
}

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
use Piwik\Period;
use Piwik\Plugins\CoreVisualizations\Visualizations\JqplotGraph\Evolution;

/**
 * @package Piwik_BotTracker
 */
class API extends \Piwik\Plugin\API
{
    private static $instance = null;

    private function getDb()
    {
        return Db::get();
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
        Piwik::checkUserHasSomeViewAccess();
        $rows = Db::get()->fetchAll(
            "SELECT * FROM " . Common::prefixTable('bot_db') .
            " WHERE idSite= ? ORDER BY `botId`",
            [$idSite]
        );
        $rows = self::convertBotLastVisitToLocalTime($rows, $idSite);
        // convert this array to a DataTable object
        return DataTable::makeFromIndexedArray($rows);
    }

    public static function getBotTrackerReportData($idSite, $period, $date, $segment)
    {
        Piwik::checkUserHasSomeViewAccess();
        list($startDate, $endDate) = self::getDateRangeForPeriod($date, $period, false);
        $startDate = $startDate->toString();
        $endDate = $endDate->toString();
        $rows = Db::get()->fetchAll(
            "SELECT * FROM " .
            Common::prefixTable('bot_db') .
            " WHERE idSite= ? ORDER BY `botId`",
            [$idSite]
        );
        $rows = self::convertBotLastVisitToLocalTime($rows, $idSite);

        // Get totals of a bot number.
        foreach ($rows as &$row) {
            $totals = Db::get()->fetchRow(
                "SELECT COUNT(botId) as total FROM " .
                Common::prefixTable('bot_visits') .
                " WHERE botId= ? AND date(date) between ? AND ?",
                [$row['botId'],
                $startDate,
                $endDate]
            );
            $row['total'] = implode($totals);
        }

        // convert this array to a DataTable object
        return DataTable::makeFromIndexedArray($rows);
    }

    public static function getBotTrackerTopTenReportPieData($idSite, $period, $date, $segment)
    {
        Piwik::checkUserHasSomeViewAccess();

        list($startDate, $endDate) = self::getDateRangeForPeriod($date, $period, false);
        $startDate = $startDate->toString();
        $endDate = $endDate->toString();
        $rows = Db::get()->fetchAll(
            "SELECT `botName`, `botId` FROM " .
            Common::prefixTable('bot_db') .
            " WHERE idSite= ? ORDER BY `botId`",
            [$idSite]
        );
        // Get totals of a bot number.
        foreach ($rows as &$row) {
            $totals = Db::get()->fetchRow(
                "SELECT COUNT(botId) as total FROM " .
                Common::prefixTable('bot_visits') .
                " WHERE botId= ? AND date(date) between ? AND ?",
                [$row['botId'],
                $startDate,
                $endDate]
            );
            $row['botCount'] = implode($totals);
        }
        $i = 0;
        $keys[0] = "";
        $values[0] = "";
        foreach ($rows as $row) {
            $keys[$i] = $row['botName'];
            $values[$i] = $row['botCount'];
            $i++;
        }

        $pie = array_combine($keys, $values);
        // Remove zero results
        $pie = array_diff($pie, [0]);
        arsort($pie);
        $topTen = array_slice($pie, 0, 10);

        return DataTable::makeFromIndexedArray($topTen);
    }

    public static function getAllBotDataForConfig($idsite)
    {
        Piwik::checkUserHasSomeViewAccess();
        $rows = Db::get()->fetchAll(
            "SELECT `idsite`, `botId`,
            `botName`, `botActive`,
            `botKeyword`, `extra_stats`,
            `botType` FROM " .
            Common::prefixTable('bot_db') .
            " WHERE `idsite` = ? ORDER BY `botId`",
            [$idsite]
        );

        return $rows;
    }


    public static function getActiveBotData($idSite)
    {
        Piwik::checkUserHasSomeViewAccess();
        $rows = Db::get()->fetchAll(
            "SELECT `botName`, `botLastVisit`, `botCount` FROM " .
            Common::prefixTable('bot_db') .
            " WHERE `botActive` = 1 AND idSite= ? ORDER BY `botId`",
            [$idSite]
        );
        $rows = self::convertBotLastVisitToLocalTime($rows, $idSite);
        // convert this array to a DataTable object
        return DataTable::makeFromIndexedArray($rows);
    }

    /**
     * @deprecated in 5.1.0, will be removed in 5.2.0
     */
    public static function getAllBotDataPie($idSite)
    {
        Piwik::checkUserHasSomeViewAccess();
        $rows = Db::get()->fetchAll(
            "SELECT `botName`, `botCount` FROM " .
            Common::prefixTable('bot_db') .
            " WHERE `botActive` = 1 AND `idSite`= ? ORDER BY `botCount`
            DESC LIMIT 10",
            [$idSite]
        );

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
        Piwik::checkUserHasSuperUserAccess();
        Db::get()->query(
            "UPDATE `" . Common::prefixTable('bot_db') . "`
		             SET `botName` = ?
		               , `botKeyword` = ?
		               , `botActive` = ?
		               , `extra_stats` = ?
		             WHERE `botId` = ?",
            [self::htmlentities2utf8($botName),
                     self::htmlentities2utf8($botKeyword),
                     $botActive,
                     $extraStats,
                     $botId]
        );
    }

    public static function insertBot($idSite, $botName, $botActive, $botKeyword, $extraStats, $botType = 0)
    {
        Piwik::checkUserHasSuperUserAccess();
        Db::get()->query(
            "INSERT INTO `" . Common::prefixTable('bot_db') . "`
               (`idsite`,`botName`, `botActive`,
               `botKeyword`, `botCount`, `extra_stats`, `botType`)
                VALUES (?,?,?,?,0,?,?)",
            [$idSite,
            self::htmlentities2utf8($botName),
            $botActive,
            self::htmlentities2utf8($botKeyword),
            $extraStats,
            $botType]
        );
    }

    public static function insertDefaultBots($idsite = 0)
    {
        Piwik::checkUserHasSuperUserAccess();
        $i = 0;
        if ($idsite <> 0) {
            $botList = [];
            $botList[] = ['Amazonbot','Amazonbot'];
            $botList[] = ['Qualys','Qualys'];
            $botList[] = ['bingbot','bingbot'];
            $botList[] = ['YandexBot','YandexBot'];
            $botList[] = ['AhrefsBot','AhrefsBot'];
            $botList[] = ['Ahrefs','Ahrefs'];
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

    public function deleteBot($botId)
    {
        Piwik::checkUserHasSuperUserAccess();
        $db = $this->getDb();
        $db->query(
            "DELETE FROM `" .
            Common::prefixTable('bot_db') .
            "` WHERE `botId` = ?",
            [$botId]
        );
    }

    public static function getBotByName($idSite, $botName)
    {
        Piwik::checkUserHasSomeViewAccess();
        $rows = Db::get()->fetchAll(
            "SELECT * FROM " .
            Common::prefixTable('bot_db') .
            " WHERE `botName` = ? AND `idSite`= ? ORDER BY `botId`",
            [$botName, $idSite]
        );
        $rows = self::convertBotLastVisitToLocalTime($rows, $idSite);
        return $rows;
    }

    private static function convertBotLastVisitToLocalTime($rows, $idSite)
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
    private static function htmlentities2utf8($string)
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
        Piwik::checkUserHasSomeViewAccess();
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
        Piwik::checkUserHasSomeViewAccess();
        return $this->getAllBotData($idSite);
    }

        /**
     * Get Data for the Report "BotTrackerReport"
     * @param int $idSite
     * @param string $period
     * @param string $date
     * @param bool|string $segment
     * @return DataTable
     */
    public function getBotTrackerReport($idSite, $period, $date, $segment = false)
    {
        Piwik::checkUserHasSomeViewAccess();
        return $this->getBotTrackerReportData($idSite, $period, $date, $segment = false);
    }

            /**
     * Get Data for the Report "BotTrackerReport"
     * @param int $idSite
     * @param string $period
     * @param string $date
     * @param bool|string $segment
     * @return DataTable
     */
    public function getBotTrackerTopTenReport($idSite, $period, $date, $segment = false)
    {
        Piwik::checkUserHasSomeViewAccess();
        return $this->getBotTrackerTopTenReportPieData($idSite, $period, $date, $segment = false);
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
        Piwik::checkUserHasSomeViewAccess();
        return $this->getActiveBotData($idSite);
    }

    public function getBotTypes()
    {
        Piwik::checkUserHasSomeViewAccess();
        $db = $this->getDb();
        $rows = $db->fetchAll("SELECT `name`, `id` FROM " . Common::prefixTable('bot_type') . " ORDER BY `name`");
        return $rows;
    }
    public function addBotType($type)
    {
        Piwik::checkUserHasSuperUserAccess();
        try {
            $db = $this->getDb();
            $sql = sprintf(
                'INSERT INTO ' . Common::prefixTable('bot_type') . ' (`name`) VALUES (?)'
            );
            $db->query($sql, [$type]);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public static function getDateRangeForPeriod($date, $period, $lastN = false)
    {
        $lastN = false;
        if ($date === false) {
            return array(false, false);
        }

        $isMultiplePeriod = Period\Range::isMultiplePeriod($date, $period);

        // if the range is just a normal period (or the period is a range in which case lastN is ignored)
        if ($period == 'range') {
            $oPeriod = new Period\Range('day', $date);
            $startDate = $oPeriod->getDateStart();
            $endDate = $oPeriod->getDateEnd();
        } elseif ($lastN == false && !$isMultiplePeriod) {
            $oPeriod = Period\Factory::build($period, Date::factory($date));
            $startDate = $oPeriod->getDateStart();
            $endDate = $oPeriod->getDateEnd();
        } else { // if the range includes the last N periods or is a multiple period
            if (!$isMultiplePeriod) {
                list($date, $lastN) = Evolution::getDateRangeAndLastN($period, $date, $lastN);
            }
            list($startDate, $endDate) = explode(',', $date);

            $startDate = Date::factory($startDate);
            $endDate = Date::factory($endDate);
        }
        return array($startDate, $endDate);
    }
}

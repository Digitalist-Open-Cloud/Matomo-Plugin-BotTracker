<?php

/**
 * BotTracker, a Matomo plugin by Digitalist Open Tech
 * Based on the work of Thomas--F (https://github.com/Thomas--F)
 * @link https://github.com/digitalist-se/MatomoPlugin-BotTracker
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\BotTracker;

use Exception;
use Piwik\Container\StaticContainer;
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

    private static function getDb()
    {
        return Db::get();
    }

    private static function getDataTable($rows)
    {
        return DataTable::makeFromIndexedArray($rows);
    }
    /**
     * @return \Piwik\Plugins\BotTracker\API
     * @throws Exception
     */
    public static function getInstance()
    {
        try {
            $instance = StaticContainer::get('BotTracker_API');
            if (!($instance instanceof API)) {
                throw new Exception('BotTracker_API must inherit API');
            }
            self::$instance = $instance;
        } catch (Exception $e) {
            self::$instance = StaticContainer::get('Piwik\Plugins\BotTracker\API');
            StaticContainer::getContainer()->set('BotTracker_API', self::$instance);
        }

        return self::$instance;
    }

    public static function getAllBotData($idSite)
    {
        Piwik::checkUserHasSomeViewAccess();
        $rows = self::getDb()->fetchAll(
            "SELECT * FROM " . Common::prefixTable('bot_db') .
            " WHERE idSite= ? ORDER BY `botId`",
            [$idSite]
        );
        $rows = self::convertBotLastVisitToLocalTime($rows, $idSite);
        // convert this array to a DataTable object
        return self::getDataTable($rows);
        //return DataTable::makeFromIndexedArray($rows);
    }

    public static function getBotTrackerReportDataTable($idSite, $period, $date, $segment)
    {
        $rows = self::getBotTrackerReportData($idSite, $period, $date, $segment);
        return self::getDataTable($rows);
    }

    public static function getBotTrackerReportData($idSite, $period, $date, $segment)
    {
        Piwik::checkUserHasSomeViewAccess();
        list($startDate, $endDate) = self::getDateRangeForPeriod($date, $period, false);
        $startDate = $startDate->toString();
        $endDate = $endDate->toString();
        $rows = self::getDb()->fetchAll(
            "SELECT * FROM " .
            Common::prefixTable('bot_db') .
            " WHERE idSite= ? ORDER BY `botId`",
            [$idSite]
        );
        $rows = self::convertBotLastVisitToLocalTime($rows, $idSite);

        // Get totals of a bot number.
        foreach ($rows as &$row) {
            $totals = self::getDb()->fetchRow(
                "SELECT COUNT(botId) as total FROM " .
                Common::prefixTable('bot_visits') .
                " WHERE botId= ? AND date(date) between ? AND ?",
                [$row['botId'],
                $startDate,
                $endDate]
            );
            $row['total'] = implode($totals);
        }
        return $rows;
    }

    public static function getBotTrackerTopTenReportPieDataTable($idSite, $period, $date, $segment)
    {
        $rows = self::getBotTrackerTopTenReportPieData($idSite, $period, $date, $segment);
        return self::getDataTable($rows);
    }

    public static function getBotTrackerTopTenReportPieData($idSite, $period, $date, $segment)
    {
        Piwik::checkUserHasSomeViewAccess();

        list($startDate, $endDate) = self::getDateRangeForPeriod($date, $period, false);
        $startDate = $startDate->toString();
        $endDate = $endDate->toString();
        $rows = self::getDb()->fetchAll(
            "SELECT `botName`, `botId` FROM " .
            Common::prefixTable('bot_db') .
            " WHERE idSite= ? ORDER BY `botId`",
            [$idSite]
        );
        // For some reason, we are off by one, add an dummy bot in end.
        // @todo: Look into why this is needed.
        $dummy = [
            'botName' => 'dummy',
            'botId' => 0,
            'botCount' => 0,
        ];
        array_push($rows, $dummy);
        // Get totals of a bot number.
        foreach ($rows as &$row) {
            $totals = self::getDb()->fetchRow(
                "SELECT COUNT(botId) as total FROM " .
                Common::prefixTable('bot_visits') .
                " WHERE botId= ? AND date(date) between ? AND ?",
                [$row['botId'],
                $startDate,
                $endDate]
            );
            $row['botCount'] = implode($totals);
        }
        $bot = [];
        $count = [];
        foreach ($rows as $row) {
            $bot[] = $row['botName'];
            $count[] = $row['botCount'];
        }

        $pie = array_combine($bot, $count);
        // Remove zero results, and limit to ten.
        $pie = array_diff($pie, [0]);
        arsort($pie);
        $rows = array_slice($pie, 0, 10);

        return $rows;
    }

    public static function getAllBotDataForConfig($idsite)
    {
        Piwik::checkUserHasSomeViewAccess();
        $rows = self::getDb()->fetchAll(
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
        $rows = self::getDb()->fetchAll(
            "SELECT `botName`, `botLastVisit`, `botCount` FROM " .
            Common::prefixTable('bot_db') .
            " WHERE `botActive` = 1 AND idSite= ? ORDER BY `botId`",
            [$idSite]
        );
        $rows = self::convertBotLastVisitToLocalTime($rows, $idSite);
        // convert this array to a DataTable object
        return self::getDataTable($rows);
    }

    /**
     * @deprecated in 5.1.0, will be removed in 5.2.0
     */
    public static function getAllBotDataPie($idSite)
    {
        Piwik::checkUserHasSomeViewAccess();
        $rows = self::getDb()->fetchAll(
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
        return self::getDataTable($pieArray);
        //return DataTable::makeFromIndexedArray($pieArray);
    }

    public static function updateBot($botName, $botKeyword, $botActive, $botId, $extraStats)
    {
        Piwik::checkUserHasSuperUserAccess();
        self::getDb()->query(
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

        try {
            self::getDb()->query(
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
            return true;
        } catch (\Exception $e) {
            throw $e;
            return false;
        }
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
        $db = self::getDb();
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
        $rows = self::getDb()->fetchAll(
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
        return $this->getBotTrackerReportDataTable($idSite, $period, $date, $segment = false);
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
        return $this->getBotTrackerTopTenReportPieDataTable($idSite, $period, $date, $segment = false);
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
        $db = self::getDb();
        $rows = $db->fetchAll("SELECT `name`, `id` FROM " . Common::prefixTable('bot_type') . " ORDER BY `name`");
        return $rows;
    }
    public function addBotType($type)
    {
        Piwik::checkUserHasSuperUserAccess();
        try {
            $db = self::getDb();
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
            return [false, false];
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
        return [$startDate, $endDate];
    }
}

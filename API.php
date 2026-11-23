<?php

/**
 * BotTracker, a Matomo plugin by Digitalist Open Tech
 * Based on the work of Thomas--F (https://github.com/Thomas--F)
 * @link https://github.com/digitalist-se/BotTracker
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\BotTracker;

use Piwik\Container\StaticContainer;
use Piwik\Db;
use Piwik\Common;
use Piwik\DataTable;
use Piwik\Site;
use Piwik\Date;
use Piwik\Piwik;
use Piwik\Period;

/**
 * @package Piwik_BotTracker
 */
class API extends \Piwik\Plugin\API
{
    // What a bot that has not been seen has as its last visit.
    private const NEVER_VISITED = '2000-01-01 00:00:00';
    private const MAX_LENGTH = 256;

    private static $instance = null;

    private static function getDb()
    {
        return Db::get();
    }

    /**
     * @return DataTable
     */
    private static function getDataTable($rows)
    {
        return DataTable::makeFromIndexedArray($rows);
    }
    /**
     * @return \Piwik\Plugins\BotTracker\API
     * @throws \Exception
     */
    public static function getInstance()
    {
        try {
            $instance = StaticContainer::get('BotTracker_API');
            if (!($instance instanceof API)) {
                throw new \Exception('BotTracker_API must inherit API');
            }
            self::$instance = $instance;
        } catch (\Exception $e) {
            self::$instance = StaticContainer::get('Piwik\Plugins\BotTracker\API');
            StaticContainer::getContainer()->set('BotTracker_API', self::$instance);
        }

        return self::$instance;
    }

    public static function getAllBotData($idSite)
    {
        Piwik::checkUserHasViewAccess($idSite);
        $rows = self::getDb()->fetchAll(
            "SELECT * FROM " . Common::prefixTable('bot_db') .
            " WHERE idSite= ? ORDER BY `botId`",
            [$idSite]
        );
        $rows = self::convertBotLastVisitToLocalTime($rows, $idSite);
        // convert this array to a DataTable object
        return self::getDataTable($rows);
    }

    /**
     * @return DataTable
     */
    public static function getBotTrackerReportDataTable($idSite, $period, $date, $segment)
    {
        $rows = self::getBotTrackerReportData($idSite, $period, $date, $segment);
        return self::getDataTable($rows);
    }

    /**
     * @return array
     */
    public static function getBotTrackerReportData($idSite, $period, $date, $segment)
    {
        Piwik::checkUserHasViewAccess($idSite);
        list($startDate, $endDate) = self::getDateRangeForPeriod($date, $period);
        $startDate = $startDate->toString();
        $endDate = $endDate->toString();
        $rows = self::getDb()->fetchAll(
            'SELECT b.*, COUNT(v.id) AS total FROM ' . Common::prefixTable('bot_db') . ' b
               LEFT JOIN ' . Common::prefixTable('bot_visits') . ' v
                 ON v.botId = b.botId AND v.date >= ? AND v.date <= ?
              WHERE b.idsite = ? GROUP BY b.botId ORDER BY b.botId',
            [$startDate . ' 00:00:00', $endDate . ' 23:59:59', $idSite]
        );
        $rows = self::convertBotLastVisitToLocalTime($rows, $idSite);

        return $rows;
    }

    public static function getBotTrackerTopTenReportPieDataTable($idSite, $period, $date, $segment)
    {
        $rows = self::getBotTrackerTopTenReportPieData($idSite, $period, $date, $segment);
        return self::getDataTable($rows);
    }

    public static function getBotTrackerTopTenReportPieData($idSite, $period, $date, $segment)
    {
        Piwik::checkUserHasViewAccess($idSite);

        list($startDate, $endDate) = self::getDateRangeForPeriod($date, $period);
        $startDate = $startDate->toString();
        $endDate = $endDate->toString();
        $rows = self::getDb()->fetchAll(
            'SELECT b.botName, COUNT(v.id) AS total FROM ' . Common::prefixTable('bot_db') . ' b
               INNER JOIN ' . Common::prefixTable('bot_visits') . ' v
                 ON v.botId = b.botId AND v.date >= ? AND v.date <= ?
              WHERE b.idsite = ? GROUP BY b.botId ORDER BY total DESC, b.botId LIMIT 10',
            [$startDate . ' 00:00:00', $endDate . ' 23:59:59', $idSite]
        );
        $rows = array_map('intval', array_column($rows, 'total', 'botName'));

        return $rows;
    }

    public static function getAllBotDataForConfig($idsite)
    {
        Piwik::checkUserHasViewAccess($idsite);
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
        Piwik::checkUserHasViewAccess($idSite);
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
     * @deprecated in 5.2.0, will be removed in 5.3.0
     */
    public static function getAllBotDataPie($idSite)
    {
        Piwik::checkUserHasViewAccess($idSite);
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

    public static function updateBot($idSite, $botName, $botKeyword, $botActive, $botId, $extraStats)
    {
        Piwik::checkUserHasSuperUserAccess();
        self::checkBot($botName, $botKeyword);
        $params = [
            'botName' => $botName,
            'botActive' => $botActive,
            'botKeyword' => $botKeyword,
            'extraStats' => $extraStats,
        ];
        try {
            self::getDb()->query(
                "UPDATE `" . Common::prefixTable('bot_db') . "`
                         SET `botName` = ?
                           , `botKeyword` = ?
                           , `botActive` = ?
                           , `extra_stats` = ?
                         WHERE `botId` = ? AND `idsite` = ?",
                [self::htmlentities2utf8($botName),
                         self::htmlentities2utf8($botKeyword),
                         $botActive ? 1 : 0,
                         $extraStats ? 1 : 0,
                         $botId,
                         $idSite]
            );
            Piwik::postEvent('BotTracker.updateBot.successful', array($idSite, $params));
        } catch (\Exception $e) {
            Piwik::postEvent('BotTracker.updateBot.failed', array($idSite, $params));
            throw $e;
        }
    }

    public static function insertBot($idSite, $botName, $botActive, $botKeyword, $extraStats, $botType = 0)
    {
        Piwik::checkUserHasSuperUserAccess();
        self::checkBot($botName, $botKeyword);
        $params = [
            'botName' => $botName,
            'botActive' => $botActive,
            'botKeyword' => $botKeyword,
            'extraStats' => $extraStats,
            'botType' => $botType,
        ];
        try {
            self::getDb()->query(
                "INSERT INTO `" . Common::prefixTable('bot_db') . "`
                   (`idsite`,`botName`, `botActive`,
                   `botKeyword`, `botCount`, `botLastVisit`, `extra_stats`, `botType`)
                    VALUES (?,?,?,?,0,?,?,?)",
                [$idSite,
                self::htmlentities2utf8($botName),
                $botActive ? 1 : 0,
                self::htmlentities2utf8($botKeyword),
                self::NEVER_VISITED,
                $extraStats ? 1 : 0,
                (int) $botType]
            );
            Piwik::postEvent('BotTracker.insertBot.successful', array($idSite, $params));
            return true;
        } catch (\Exception $e) {
            Piwik::postEvent('BotTracker.insertBot.failed', array($idSite, $params));
            throw $e;
        }
    }


    public static function defaultBots()
    {
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
        $botList[] = ['ClaudeBot','ClaudeBot'];
        $botList[] = ['Screaming Frog SEO Spider','Screaming Frog SEO Spider'];
        return $botList;
    }

    public static function insertDefaultBots($idsite = 0)
    {
        Piwik::checkUserHasSuperUserAccess();
        $i = 0;
        if ($idsite <> 0) {
            $botList = self::defaultBots();
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

    public function deleteBot($idSite, $botId)
    {
        Piwik::checkUserHasSuperUserAccess();
        try {
            $db = self::getDb();
            $query = $db->query(
                "DELETE FROM `" .
                Common::prefixTable('bot_db') .
                "` WHERE `botId` = ? AND `idsite` = ?",
                [$botId, $idSite]
            );
            Piwik::postEvent('BotTracker.deleteBot.successful', array($idSite, $botId));
            return true;
        } catch (\Exception $e) {
            Piwik::postEvent('BotTracker.deleteBot.failed', array($idSite, $botId));
            throw $e;
        }
    }

    public static function getBotByName($idSite, $botName)
    {
        Piwik::checkUserHasViewAccess($idSite);
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

        try {
            foreach ($rows as &$row) {
                if ($row['botLastVisit'] == '0000-00-00 00:00:00') {
                    $row['botLastVisit'] = " - ";
                } elseif ($row['botLastVisit'] == self::NEVER_VISITED) {
                    $row['botLastVisit'] = " - ";
                } else {
                    $botLastVisit = Date::adjustForTimezone(strtotime($row['botLastVisit']), $timezone);
                        $row['botLastVisit'] = date('Y-m-d H:i:s', $botLastVisit);
                }
            }
        } catch (\Exception $e) {
            throw $e;
        }
        return $rows;
    }
    /**
     * The API of Matomo hands text over HTML-encoded. A name and a keyword are stored as written.
     */
    private static function htmlentities2utf8($string)
    {
        return html_entity_decode((string) $string, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    private static function checkBot($botName, $botKeyword): void
    {
        foreach ([$botName, $botKeyword] as $text) {
            $text = is_string($text) ? trim(self::htmlentities2utf8($text)) : '';
            if ($text === '' || mb_strlen($text) > self::MAX_LENGTH) {
                throw new \Exception(Piwik::translate('BotTracker_Error_Bot', self::MAX_LENGTH));
            }
        }
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
        Piwik::checkUserHasViewAccess($idSite);
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
        Piwik::checkUserHasViewAccess($idSite);
        return $this->getAllBotData($idSite);
    }

    /**
     * Get Data for the Report "BotTrackerReport"
     * @param int $idSite
     * @param string $period
     * @param string $date
     * @param bool|string $segment
     */
    public function getBotTrackerReport($idSite, $period, $date, $segment = false)
    {
        Piwik::checkUserHasViewAccess($idSite);
        return $this->getBotTrackerReportDataTable($idSite, $period, $date, $segment = false);
    }


    /**
     * Get Data for the Report "BotStatsReport"
     * @param int $idSite
     * @param string $period
     * @param string $date
     * @param bool|string $segment
     */
    public function getOtherBots($idSite, $period, $date, $segment = false)
    {
        Piwik::checkUserHasViewAccess($idSite);
        return $this->getOtherBotsDataTable($idSite, $period, $date, $segment = false);
    }

    /**
     * @return DataTable
     */
    public static function getOtherBotsDataTable($idSite, $period, $date, $segment)
    {
        $rows = self::getOtherBotsData($idSite, $period, $date, $segment);
        return self::getDataTable($rows);
    }

    /**
     * @return array
     */
    public static function getOtherBotsData($idSite, $period, $date, $segment)
    {
        Piwik::checkUserHasViewAccess($idSite);
        list($startDate, $endDate) = self::getDateRangeForPeriod($date, $period);
        $startDate = $startDate->toString();
        $endDate = $endDate->toString();
        $rows = self::getDb()->fetchAll(
            "SELECT useragent, COUNT(*) as total FROM " .
            Common::prefixTable('bot_device_detector_bots') .
            " WHERE idSite= ? AND date >= ? AND date <= ? GROUP BY `useragent` ORDER BY `useragent`",
            [$idSite, $startDate . ' 00:00:00', $endDate . ' 23:59:59']
        );
        // // @todo: convert visit_timestamp to site
        return $rows;
    }


    /**
     * Get Data for the Report "BotStatsReport"
     * @param int $idSite
     * @param string $period
     * @param string $date
     * @param bool|string $segment
     */
    public function getStatsReport($idSite, $period, $date, $segment = false)
    {
        Piwik::checkUserHasViewAccess($idSite);
        return $this->getStatsReportDataTable($idSite, $period, $date, $segment = false);
    }

    /**
     * @return DataTable
     */
    public static function getStatsReportDataTable($idSite, $period, $date, $segment)
    {
        $rows = self::getStatsReportData($idSite, $period, $date, $segment);
        return self::getDataTable($rows);
    }

    /**
     * @return array
     */
    public static function getStatsReportData($idSite, $period, $date, $segment)
    {
        Piwik::checkUserHasViewAccess($idSite);
        list($startDate, $endDate) = self::getDateRangeForPeriod($date, $period);
        $startDate = $startDate->toString();
        $endDate = $endDate->toString();
        $rows = self::getDb()->fetchAll(
            'SELECT s.*, b.botName FROM ' . Common::prefixTable('bot_db_stat') . ' s
               LEFT JOIN ' . Common::prefixTable('bot_db') . ' b ON b.botId = s.botId
              WHERE s.idsite = ? AND s.visit_timestamp >= ? AND s.visit_timestamp <= ? ORDER BY s.botId',
            [$idSite, $startDate . ' 00:00:00', $endDate . ' 23:59:59']
        );
        // @todo: convert visit_timestamp to site
        return $rows;
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
        Piwik::checkUserHasViewAccess($idSite);
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
        Piwik::checkUserHasViewAccess($idSite);
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

    /**
     * @return array{0: Date, 1: Date} the first and the last day of the period
     */
    private static function getDateRangeForPeriod($date, $period)
    {
        $range = Period\Factory::build($period, $date);

        return [$range->getDateStart(), $range->getDateEnd()];
    }
}

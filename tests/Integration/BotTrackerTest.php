<?php

/**
 * BotTracker, a Matomo plugin by Digitalist Open Tech
 * Based on the work of Thomas--F (https://github.com/Thomas--F)
 * @link https://github.com/digitalist-se/MatomoPlugin-BotTracker
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\BotTracker\tests\Integration;

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


use Piwik\Tests\Fixtures\OneVisitorTwoVisits;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Plugins\BotTracker\Api as BotTrackerAPI;

/**
 * @group BotTracker
 * @group Plugins
 */
class BotTrackerTest extends IntegrationTestCase
{

    /**
     * @var OneVisitorTwoVisits
     */
    public static $fixture;

    public $dataService;
    public $config;
    public $from;
    public $to;
    public $send;
    public $uuid;
    public $idSite;
    public $botTrackerApi;

    public function setUp(): void
    {
        parent::setUp();
        $this->idSite = 1;
        $config = [];
        $config['idsite'] = $this->idSite;
        $this->config = $config;
        $this->from = '2024-03-01';
        $this->to = '2024-03-02';
        $this->send = false;
        $this->uuid = 'a314c013-c9ff-49f6-8912-2f415baf2a69-1678112428215';
        $this->botTrackerApi = new BotTrackerAPI();
    }

    public function testGetAllBotData() {
        $getAllBotData = $this->botTrackerApi->getAllBotData($this->idSite);
        $this->assertIsObject($getAllBotData);

    }

    public function testGetBotTrackerReportData() {
        $getAllBotData = $this->botTrackerApi->getBotTrackerReportData($this->idSite, 'day', $this->from, null);
        $this->assertIsObject($getAllBotData);

    }


}

// The fixture makes not much sense here. We need real requests. Could we mock?
BotTrackerTest::$fixture = new OneVisitorTwoVisits();

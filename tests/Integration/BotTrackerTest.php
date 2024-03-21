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
        $this->assertIsArray($getAllBotData);
    }

    public function testGetBotTrackerTopTenReportPieData() {
        $getPieData = $this->botTrackerApi->getBotTrackerTopTenReportPieData($this->idSite, 'day', $this->from, null);
        $this->assertIsArray($getPieData);
    }

    public function testGetBotTrackerReportDataTable() {
        $getAllBotData = $this->botTrackerApi->getBotTrackerReportDataTable($this->idSite, 'day', $this->from, null);
        $this->assertIsObject($getAllBotData);
    }

    public function testGetBotTrackerTopTenReportPieDataTable() {
        $getPieData = $this->botTrackerApi->getBotTrackerTopTenReportPieDataTable($this->idSite, 'day', $this->from, null);
        $this->assertIsObject($getPieData);
    }

    public function testInsertBotAndCheckIfItExists() {
        $insertBotFoo = $this->botTrackerApi->insertBot(1,'foo',1,'Foo',0,0);
        $insertBotBar = $this->botTrackerApi->insertBot(1,'bar',1,'Bar',0,0);
        $this->assertTrue($insertBotFoo);
        $this->assertTrue($insertBotBar);
        $checkBotFoo = $this->botTrackerApi->getBotByName(1, 'foo');
        $checkBotBar = $this->botTrackerApi->getBotByName(1, 'bar');
        $this->assertArrayHasKey('botName', $checkBotFoo[0]);
        $this->assertContains('foo', $checkBotFoo[0]);
        $this->assertArrayHasKey('botName', $checkBotBar[0]);
        $this->assertContains('bar', $checkBotBar[0]);
    }
}

// The fixture makes not much sense here. We need real requests. Could we mock?
BotTrackerTest::$fixture = new OneVisitorTwoVisits();

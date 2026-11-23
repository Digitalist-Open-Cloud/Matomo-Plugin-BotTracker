<?php

namespace Piwik\Plugins\BotTracker\tests\Integration;

use Exception;
use Piwik\NoAccessException;
use Piwik\Plugins\BotTracker\API;
use Piwik\Plugins\SitesManager\API as SitesManagerApi;
use Piwik\Tests\Framework\Mock\FakeAccess;

/**
 * @group BotTracker
 * @group Plugins
 */
class ApiTest extends BotTrackerTestCase
{
    public function testABotIsStoredAsWritten()
    {
        // The API of Matomo hands text over HTML-encoded.
        API::insertBot(1, 'Tom &amp; &quot;Jerry&quot; &lt;Co&gt;', 1, 'Tom&#039;s bot', 0);

        $bot = API::getBotByName(1, 'Tom & "Jerry" <Co>')[0];

        $this->assertSame(['Tom & "Jerry" <Co>', "Tom's bot"], [$bot['botName'], $bot['botKeyword']]);
        $this->assertEquals([1, 1, 0], [$bot['idsite'], $bot['botActive'], $bot['extra_stats']]);
    }

    /**
     * @dataProvider getBotsThatAreRefused
     */
    public function testABotNeedsANameAndAKeyword(string $name, string $keyword)
    {
        foreach (['insert', 'update'] as $how) {
            try {
                $how === 'insert' ? API::insertBot(1, $name, 1, $keyword, 0) : API::updateBot(1, $name, $keyword, 1, $this->bots[1], 0);
                $this->fail('The bot was saved.');
            } catch (Exception $e) {
                $this->assertStringContainsString('needs a name and a keyword', $e->getMessage());
            }
        }

        $this->assertSame(['Crawler of site 1', 'Crawler of site 2'], $this->getBotNames());
    }

    public function getBotsThatAreRefused(): array
    {
        return [
            'no name' => [' ', 'Keyword'],
            'no keyword' => ['Name', ''],
            'a name that is too long' => [str_repeat('n', 257), 'Keyword'],
            'a keyword that is too long' => ['Name', str_repeat('k', 257)],
        ];
    }

    public function testTheReportsCountTheVisitsOfThePeriod()
    {
        API::insertBot(1, 'Quiet bot', 1, 'Quiet', 0);
        $this->addVisit(1, $this->bots[1], '2024-03-04 23:59:59');
        $this->addVisit(1, $this->bots[1], '2024-03-05 00:00:00');
        $this->addVisit(1, $this->bots[1], '2024-03-05 23:59:59');
        $this->addVisit(1, $this->bots[1], '2024-03-06 00:00:00');
        $this->addVisit(2, $this->bots[2], '2024-03-05 12:00:00');

        $totals = array_column(API::getBotTrackerReportData(1, 'day', '2024-03-05', false), 'total', 'botName');
        $this->assertEquals(['Crawler of site 1' => 2, 'Quiet bot' => 0], $totals);
        $week = array_column(API::getBotTrackerReportData(1, 'week', '2024-03-05', false), 'total', 'botName');
        $this->assertEquals(4, $week['Crawler of site 1']);
        foreach (['range' => '2024-03-05,2024-03-06', 'day' => 'last2'] as $period => $date) {
            $date = $period === 'day' ? '2024-03-05,2024-03-06' : $date;
            $this->assertEquals(3, API::getBotTrackerReportData(1, $period, $date, false)[0]['total'], $period);
        }

        $this->assertEquals(['Crawler of site 1' => 2], API::getBotTrackerTopTenReportPieData(1, 'day', '2024-03-05', false));
        $this->assertSame([], API::getBotTrackerTopTenReportPieData(1, 'day', '2024-03-01', false));
    }

    public function testTheTopTenAreTheBotsWithMostVisits()
    {
        foreach (range(1, 12) as $number) {
            API::insertBot(1, 'Bot ' . $number, 1, 'Keyword' . $number, 0);
            $idBot = (int) API::getBotByName(1, 'Bot ' . $number)[0]['botId'];
            for ($visit = 0; $visit < $number; $visit++) {
                $this->addVisit(1, $idBot, '2024-03-05 10:00:00');
            }
        }

        $top = API::getBotTrackerTopTenReportPieData(1, 'day', '2024-03-05', false);

        $this->assertSame(['Bot 12', 'Bot 11', 'Bot 10', 'Bot 9', 'Bot 8', 'Bot 7', 'Bot 6', 'Bot 5', 'Bot 4', 'Bot 3'], array_keys($top));
        $this->assertEquals(12, $top['Bot 12']);
    }

    public function testBotDataIsOnlyReadWithViewAccessToItsWebsite()
    {
        FakeAccess::clearAccess(false, [], [1]);
        $api = API::getInstance();

        $reads = [
            'getAllBotData' => function (int $idSite) {
                return API::getAllBotData($idSite);
            },
            'getAllBotDataForConfig' => function (int $idSite) {
                return API::getAllBotDataForConfig($idSite);
            },
            'getActiveBotData' => function (int $idSite) {
                return API::getActiveBotData($idSite);
            },
            'getAllBotDataPie' => function (int $idSite) {
                return API::getAllBotDataPie($idSite);
            },
            'getBotByName' => function (int $idSite) {
                return API::getBotByName($idSite, 'Crawler of site ' . $idSite);
            },
        ];
        foreach (['getBotTrackerReportData', 'getBotTrackerTopTenReportPieData', 'getOtherBotsData', 'getStatsReportData'] as $method) {
            $reads[$method] = function (int $idSite) use ($method) {
                return API::$method($idSite, 'day', '2024-03-05', false);
            };
        }
        foreach (['getTop10', 'getBotTracker', 'getBotTrackerReport', 'getOtherBots', 'getStatsReport', 'getBotTrackerTopTenReport', 'getBotTrackerAnzeige'] as $method) {
            $reads[$method] = function (int $idSite) use ($api, $method) {
                return $api->$method($idSite, 'day', '2024-03-05');
            };
        }

        foreach ($reads as $method => $read) {
            $read(1);
            try {
                $read(2);
                $this->fail($method . ' answered for a website the user cannot view.');
            } catch (NoAccessException $e) {
                $this->addToAssertionCount(1);
            }
        }
    }

    public function testOnlyASuperUserChangesBots()
    {
        FakeAccess::clearAccess(false, [1, 2]);
        $api = API::getInstance();

        $changes = [
            'insertBot' => function () {
                API::insertBot(1, 'Planted', 1, 'Planted', 0);
            },
            'updateBot' => function () {
                API::updateBot(1, 'Renamed', 'Renamed', 0, $this->bots[1], 0);
            },
            'deleteBot' => function () use ($api) {
                $api->deleteBot(1, $this->bots[1]);
            },
            'insertDefaultBots' => function () {
                API::insertDefaultBots(1);
            },
            'addBotType' => function () use ($api) {
                $api->addBotType('Planted');
            },
        ];
        foreach ($changes as $method => $change) {
            try {
                $change();
                $this->fail($method . ' worked for an admin.');
            } catch (NoAccessException $e) {
                $this->addToAssertionCount(1);
            }
        }

        $this->assertSame(['Crawler of site 1', 'Crawler of site 2'], $this->getBotNames());
    }

    public function testABotIsChangedAndDeletedForItsWebsiteOnly()
    {
        $api = API::getInstance();

        // The bot of website 2, asked for through website 1.
        API::updateBot(1, 'Renamed', 'Renamed', 0, $this->bots[2], 0);
        $api->deleteBot(1, $this->bots[2]);
        $this->assertSame(['Crawler of site 1', 'Crawler of site 2'], $this->getBotNames());

        API::updateBot(1, 'Renamed', 'NewKeyword', 0, $this->bots[1], 0);
        $api->deleteBot(2, $this->bots[2]);
        $this->assertEquals([['idsite' => 1, 'botName' => 'Renamed', 'botKeyword' => 'NewKeyword', 'botActive' => 0, 'extra_stats' => 0]], $this->getBots());
    }

    public function testTheDefaultBotsAreAddedOnce()
    {
        $added = API::insertDefaultBots(1);

        $this->assertGreaterThan(50, $added);
        $this->assertEquals(0, API::insertDefaultBots(1));
        $this->assertCount($added + 2, $this->getBots());
    }

    public function testDeletingAWebsiteDeletesItsBotsAndTheirVisits()
    {
        $this->addVisit(1, $this->bots[1], '2024-03-05 12:00:00');
        $this->addVisit(2, $this->bots[2], '2024-03-05 12:00:00');

        SitesManagerApi::getInstance()->deleteSite(2);

        $this->assertSame(['Crawler of site 1'], $this->getBotNames());
        $this->assertSame(1, $this->countRows('bot_visits'));
    }
}

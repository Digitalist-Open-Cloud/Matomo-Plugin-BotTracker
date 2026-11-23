<?php

namespace Piwik\Plugins\BotTracker\tests\Integration;

use Piwik\Common;
use Piwik\Db;
use Piwik\Plugins\BotTracker\API;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * Two websites, each with a bot.
 */
abstract class BotTrackerTestCase extends IntegrationTestCase
{
    /**
     * @var array<int, int> the bot of each website
     */
    protected $bots = [];

    public function setUp(): void
    {
        parent::setUp();

        Fixture::loadAllTranslations();
        Fixture::createSuperUser();
        Fixture::createWebsite('2024-01-01 00:00:00');
        Fixture::createWebsite('2024-01-01 00:00:00');
        FakeAccess::clearAccess(true);
        Db::query('DELETE FROM ' . Common::prefixTable('bot_db'));
        foreach ([1, 2] as $idSite) {
            API::insertBot($idSite, 'Crawler of site ' . $idSite, 1, 'TestAgent' . $idSite, 1);
            $this->bots[$idSite] = (int) API::getBotByName($idSite, 'Crawler of site ' . $idSite)[0]['botId'];
        }
        $_GET = $_POST = $_FILES = [];
        $_SERVER['REQUEST_METHOD'] = 'GET';
    }

    public function tearDown(): void
    {
        $_GET = $_POST = $_FILES = [];
        $_SERVER['REQUEST_METHOD'] = 'GET';

        parent::tearDown();
    }

    public function provideContainerConfig()
    {
        return ['Piwik\Access' => new FakeAccess()];
    }

    protected static function configureFixture($fixture)
    {
        parent::configureFixture($fixture);

        $fixture->extraPluginsToLoad = ['BotTracker'];
    }

    protected function addVisit(int $idSite, int $idBot, string $time): void
    {
        Db::query('INSERT INTO ' . Common::prefixTable('bot_visits') . ' (botId, idsite, date) VALUES (?, ?, ?)', [$idBot, $idSite, $time]);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function getBots(): array
    {
        return Db::fetchAll('SELECT idsite, botName, botKeyword, botActive, extra_stats FROM ' . Common::prefixTable('bot_db') . ' ORDER BY botId');
    }

    /**
     * @return string[]
     */
    protected function getBotNames(): array
    {
        return array_column($this->getBots(), 'botName');
    }

    protected function countRows(string $table): int
    {
        return (int) Db::fetchOne('SELECT COUNT(*) FROM ' . Common::prefixTable($table));
    }
}

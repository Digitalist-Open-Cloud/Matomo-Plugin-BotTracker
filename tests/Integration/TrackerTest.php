<?php

namespace Piwik\Plugins\BotTracker\tests\Integration;

use Piwik\Common;
use Piwik\Db;
use Piwik\Plugins\BotTracker\SystemSettings;
use Piwik\Tests\Framework\Fixture;

/**
 * @group BotTracker
 * @group Plugins
 */
class TrackerTest extends BotTrackerTestCase
{
    private const BROWSER = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36';

    public function testAVisitOfAConfiguredBotIsCountedAndNotTracked()
    {
        $this->track(1, 'Mozilla/5.0 (compatible; TestAgent1/2.0)', 'http://example.org/priser');

        $this->assertSame(0, $this->countRows('log_visit'));
        $this->assertEquals([['botId' => $this->bots[1], 'idsite' => 1]], Db::fetchAll('SELECT botId, idsite FROM ' . Common::prefixTable('bot_visits')));
        $this->assertSame('http://example.org/priser', Db::fetchOne('SELECT page FROM ' . Common::prefixTable('bot_db_stat')));
    }

    public function testABotOfAnotherWebsiteIsAVisitor()
    {
        $this->track(1, self::BROWSER . ' TestAgent2');

        $this->assertSame(1, $this->countRows('log_visit'));
        $this->assertSame(0, $this->countRows('bot_visits'));
    }

    public function testAVisitorIsTracked()
    {
        $this->track(1, self::BROWSER);

        $this->assertSame(1, $this->countRows('log_visit'));
        $this->assertSame(0, $this->countRows('bot_visits'));
        $this->assertSame(0, $this->countRows('bot_device_detector_bots'));
    }

    public function testAnAddressLongerThanTheColumnIsCut()
    {
        $this->track(1, 'TestAgent1', 'http://example.org/' . str_repeat('å', 300));

        $this->assertSame(256, mb_strlen((string) Db::fetchOne('SELECT page FROM ' . Common::prefixTable('bot_db_stat')), 'UTF-8'));
    }

    public function testOtherBotsAreLoggedWhenTheSettingIsOn()
    {
        $googlebot = 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)';

        $this->track(1, $googlebot);
        $this->assertSame(0, $this->countRows('bot_device_detector_bots'));

        $settings = new SystemSettings();
        $settings->trackDeviceDetectorBots->setValue(true);
        $settings->save();
        $this->track(1, $googlebot);

        $this->assertSame($googlebot, Db::fetchOne('SELECT useragent FROM ' . Common::prefixTable('bot_device_detector_bots') . ' WHERE idsite = 1'));
        $this->assertSame(0, $this->countRows('log_visit'));
    }

    private function track(int $idSite, string $userAgent, string $url = 'http://example.org/'): void
    {
        $tracker = Fixture::getTracker($idSite, date('Y-m-d H:i:s'), true);
        $tracker->setUserAgent($userAgent);
        $tracker->setUrl($url);
        Fixture::checkResponse($tracker->doTrackPageView('Page'));
    }
}

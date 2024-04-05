<?php

/**
 * BotTracker, a Matomo plugin by Digitalist Open Tech
 * Based on the work of Thomas--F (https://github.com/Thomas--F)
 * @link https://github.com/digitalist-se/BotTracker
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\BotTracker\tests\Unit;

use Piwik\Plugins\BotTracker\BotTracker;

class BotTrackerTest extends \PHPUnit\Framework\TestCase
{

    private $botTracker;

    public function setUp(): void
    {
        parent::setUp();
        $this->botTracker = new BotTracker();
    }


    public function testRegisterEvents(): void
    {
        $registerEvents = $this->botTracker->registerEvents();
        $this->assertArrayHasKey('Tracker.isExcludedVisit', $registerEvents);
        $this->assertArrayHasKey('Translate.getClientSideTranslationKeys', $registerEvents);
    }
}

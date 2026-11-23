<?php

namespace Piwik\Plugins\BotTracker\tests\Integration;

use Piwik\Tests\Framework\TestCase\ConsoleCommandTestCase;

/**
 * @group BotTracker
 * @group Plugins
 */
class CommandTest extends ConsoleCommandTestCase
{
    public function testBotsAreAddedListedAndDeleted()
    {
        $this->assertSame(0, $this->applicationTester->run(['command' => 'bottracker:add-bot', '--idsite' => 1, '--name' => 'Console bot', '--agent-string' => 'ConsoleAgent']));

        $this->applicationTester->run(['command' => 'bottracker:list-bots', '--idsite' => 1]);
        $list = $this->applicationTester->getDisplay();
        $this->assertStringContainsString('Console bot', $list);
        preg_match('/(\d+)\D+Console bot/', $list, $found);

        $this->assertSame(0, $this->applicationTester->run(['command' => 'bottracker:delete-bot', '--botId' => $found[1] ?? 0]));
        $this->applicationTester->run(['command' => 'bottracker:list-bots', '--idsite' => 1]);
        $this->assertStringNotContainsString('Console bot', $this->applicationTester->getDisplay());

        $this->assertNotSame(0, $this->applicationTester->run(['command' => 'bottracker:delete-bot', '--botId' => 999999]));
    }

    protected static function configureFixture($fixture)
    {
        parent::configureFixture($fixture);

        $fixture->extraPluginsToLoad = ['BotTracker'];
    }
}

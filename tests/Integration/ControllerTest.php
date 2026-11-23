<?php

namespace Piwik\Plugins\BotTracker\tests\Integration;

use Exception;
use Piwik\Nonce;
use Piwik\Plugins\BotTracker\Controller;
use Piwik\Tests\Framework\Mock\FakeAccess;

/**
 * @group BotTracker
 * @group Plugins
 */
class ControllerTest extends BotTrackerTestCase
{
    private const CHANGES = ['saveConfig', 'addNew', 'deleteBotEntry', 'configInsertDb', 'configImport'];

    public function testABotIsAdded()
    {
        $this->post('addNew', ['new_botName' => 'New bot', 'new_botKeyword' => 'NewBot', 'new_botActive' => '1']);

        $this->assertEquals(
            ['idsite' => 1, 'botName' => 'New bot', 'botKeyword' => 'NewBot', 'botActive' => 1, 'extra_stats' => 0],
            $this->getBots()[2]
        );
    }

    public function testChangedBotsAreSaved()
    {
        $idBot = $this->bots[1];

        $this->post('saveConfig', [$idBot . '_botName' => 'Renamed', $idBot . '_botKeyword' => 'Other', $idBot . '_botActive' => '1']);

        $this->assertEquals(['idsite' => 1, 'botName' => 'Renamed', 'botKeyword' => 'Other', 'botActive' => 1, 'extra_stats' => 0], $this->getBots()[0]);
        $this->assertSame('Crawler of site 2', $this->getBots()[1]['botName']);
    }

    public function testABotWithoutANameIsNotSaved()
    {
        $idBot = $this->bots[1];

        $page = $this->post('saveConfig', [$idBot . '_botName' => '', $idBot . '_botKeyword' => 'Other']);

        $this->assertSame('TestAgent1', $this->getBots()[0]['botKeyword']);
        $this->assertStringContainsString('is empty!', $page);
    }

    public function testABotIsDeletedFromTheWebsiteOfThePage()
    {
        $this->post('deleteBotEntry', ['botId' => $this->bots[2]]);
        $this->assertCount(2, $this->getBots());

        $this->post('deleteBotEntry', ['botId' => $this->bots[1]]);
        $this->assertSame(['Crawler of site 2'], $this->getBotNames());
    }

    public function testTheDefaultBotsAreAdded()
    {
        $this->post('configInsertDb');

        $this->assertGreaterThan(50, count($this->getBots()));
    }

    /**
     * @dataProvider getChanges
     */
    public function testAChangeIsPosted(string $action)
    {
        $this->refuse($action, 'GET', ['form_nonce' => Nonce::getNonce(Controller::NONCE)]);
    }

    /**
     * @dataProvider getChanges
     */
    public function testAChangeNeedsTheNonceOfThePage(string $action)
    {
        $this->refuse($action, 'POST', []);
        $this->refuse($action, 'POST', ['form_nonce' => 'not-the-nonce']);
    }

    /**
     * @dataProvider getChanges
     */
    public function testAChangeNeedsASuperUser(string $action)
    {
        $nonce = Nonce::getNonce(Controller::NONCE);
        FakeAccess::clearAccess(false, [1, 2]);

        $this->refuse($action, 'POST', ['form_nonce' => $nonce]);
    }

    public function getChanges(): array
    {
        return array_combine(self::CHANGES, array_map(function (string $action): array {
            return [$action];
        }, self::CHANGES));
    }

    public function testTheSettingsPageIsForSuperUsers()
    {
        FakeAccess::clearAccess(false, [1, 2]);

        $this->expectException(Exception::class);
        $this->call('index', 'GET', []);
    }

    public function testTheSettingsPageEscapesBotNames()
    {
        $this->post('addNew', ['new_botName' => '<script>alert(1)</script>', 'new_botKeyword' => '"><img src=x>']);

        $page = $this->call('index', 'GET', []);

        $this->assertStringNotContainsString('<script>alert(1)', $page);
        $this->assertStringNotContainsString('<img src=x>', $page);
    }

    /**
     * @param array<string, mixed> $form
     */
    private function post(string $action, array $form = []): string
    {
        return $this->call($action, 'POST', $form + ['form_nonce' => Nonce::getNonce(Controller::NONCE)]);
    }

    /**
     * @param array<string, mixed> $form
     */
    private function refuse(string $action, string $method, array $form): void
    {
        $form += [
            'botId' => $this->bots[1],
            'new_botName' => 'Planted',
            'new_botKeyword' => 'Planted',
            $this->bots[1] . '_botName' => 'Planted',
            $this->bots[1] . '_botKeyword' => 'Planted',
        ];
        try {
            $this->call($action, $method, $method === 'GET' ? [] : $form, $method === 'GET' ? $form : []);
            $this->fail($action . ' was done.');
        } catch (Exception $e) {
            $this->assertSame(['Crawler of site 1', 'Crawler of site 2'], $this->getBotNames());
        }
    }

    /**
     * @param array<string, mixed> $form
     * @param array<string, mixed> $query
     */
    private function call(string $action, string $method, array $form, array $query = []): string
    {
        $_GET = $query + ['idSite' => 1];
        $_POST = $form;
        $_SERVER['REQUEST_METHOD'] = $method;

        ob_start();
        try {
            (new Controller())->$action();
        } finally {
            $page = (string) ob_get_clean();
        }

        return $page;
    }
}

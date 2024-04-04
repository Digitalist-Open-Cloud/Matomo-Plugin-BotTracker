<?php

/**
 * BotTracker, a Matomo plugin by Digitalist Open Tech
 * Based on the work of Thomas--F (https://github.com/Thomas--F)
 * @link https://github.com/digitalist-se/BotTracker
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\BotTracker\tests\Unit;

use Piwik\Plugins\BotTracker\Api;

class ApiTest extends \PHPUnit\Framework\TestCase
{

    private $api;

    public function setUp(): void
    {
        parent::setUp();
        $this->api = new Api();
    }


    public function testGetApi(): void
    {
        $getAPI = $this->api;
        $this->assertIsObject($getAPI);
    }
}

<?php

/**
 * BotTracker, a Matomo plugin by Digitalist Open Tech
 * Based on the work of Thomas--F (https://github.com/Thomas--F)
 * @link https://github.com/digitalist-se/BotTracker
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\BotTracker;

use Piwik\Settings\Setting;
use Piwik\Settings\FieldConfig;

/**
 * Defines Settings for BotTracker.
 */
class SystemSettings extends \Piwik\Settings\Plugin\SystemSettings
{
    /** @var Setting */
    public $trackDeviceDetectorBots;

    protected function init()
    {
        $this->trackDeviceDetectorBots = $this->trackDeviceDetectorBots();
    }


    /**
     * @return Setting
     */
    private function trackDeviceDetectorBots()
    {
        return $this->makeSetting(
            'TrackDeviceDetectorBots',
            $default = false,
            FieldConfig::TYPE_BOOL,
            function (FieldConfig $field) {
                $field->title = 'Enable logging of Matomo Device Detector Bots';
                $field->uiControl = FieldConfig::UI_CONTROL_CHECKBOX;
                $field->description = 'If enabled, detected by Matomo Device Detector,'
                  . 'and not configured by Bot Tracker, will be logged';
            }
        );
    }
}

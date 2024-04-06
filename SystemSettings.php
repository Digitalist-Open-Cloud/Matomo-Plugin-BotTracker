<?php

/**
 * BotTracker, a Matomo plugin by Digitalist Open Tech
 * Based on the work of Thomas--F (https://github.com/Thomas--F)
 *
 * @link https://github.com/digitalist-se/BotTracker
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\BotTracker;

use Piwik\Settings\FieldConfig;
use Piwik\Settings\Plugin\SystemSettings as MatomoSystemSettings;
use Piwik\Settings\Setting;

/**
 * Defines Settings for BotTracker.
 */
class SystemSettings extends MatomoSystemSettings
{

    public Setting $trackDeviceDetectorBots;

    protected function init()
    {
        $this->trackDeviceDetectorBots = $this->trackDeviceDetectorBots();
    }

    /**
     * @return \Piwik\Settings\Setting
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

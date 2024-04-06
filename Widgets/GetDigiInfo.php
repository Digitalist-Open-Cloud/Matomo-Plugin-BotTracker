<?php

namespace Piwik\Plugins\BotTracker\Widgets;

use Piwik\Piwik;
use Piwik\Widget\Widget;
use Piwik\Widget\WidgetConfig;

class GetDigiInfo extends Widget
{

    public function render()
    {
        $dir = 'plugins/BotTracker';
        $imageDir = "$dir/assets/";

        return '<div class="card">
          <div class="card-content">
          <div style="float:right">
            <a style="text-decoration:none" href="https://digitalist.se/">
              <img src="' . $imageDir  . 'dot.png" height="40" />
            </a>
          </div>
          <h2 class="title">Bot Tracker</h2>
            <p>This plugin is maintained and developed by
              <a href="https://digitalist.se/">Digitalist Open Cloud</a>
              and the Matomo community.
            </p>
          </div>
        </div>';
    }

    public static function configure(WidgetConfig $config)
    {
        $config->setCategoryId('General_Visitors');
        $config->setSubcategoryId(Piwik::translate('BotTracker_BotTracker'));
        $config->setIsWide();
        $config->setOrder(0);
        $config->setIsNotWidgetizable();
    }

}

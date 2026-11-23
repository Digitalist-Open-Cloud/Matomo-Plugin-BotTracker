<?php

namespace Piwik\Plugins\BotTracker\Widgets;

use Piwik\Piwik;
use Piwik\Widget\Widget;
use Piwik\Widget\WidgetConfig;

class GetDigiInfo extends Widget
{
    public static function configure(WidgetConfig $config)
    {
        $config->setCategoryId('General_Visitors');
        $config->setSubcategoryId('BotTracker_BotTracker');
        $config->setIsWide();
        $config->setOrder(0);
        $config->setIsNotWidgetizable();
    }

    public function render()
    {
        $dir = 'plugins/BotTracker';
        $imageDir = "$dir/assets/";

        return '<div class="card">
          <div class="card-content">
          <div style="float:right">
            <a style="text-decoration:none" href="https://digitalist.se/" rel="noreferrer noopener">
              <img src="' . $imageDir  . 'dot.png" height="40" alt="Digitalist Open Cloud" />
            </a>
          </div>
          <h2 class="title">' . Piwik::translate('BotTracker_BotTracker') . '</h2>
            <p>' . Piwik::translate('BotTracker_MaintainedBy', ['<a href="https://digitalist.se/" rel="noreferrer noopener">', '</a>']) . '</p>
          </div>
        </div>';
    }
}

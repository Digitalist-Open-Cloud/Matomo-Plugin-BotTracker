<?php

/**
 * BotTracker, a Matomo plugin by Digitalist Open Tech
 * Based on the work of Thomas--F (https://github.com/Thomas--F)
 *
 * @link https://github.com/digitalist-se/BotTracker
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\BotTracker\Reports;

use Piwik\Piwik;
use Piwik\Plugin\ViewDataTable;
use Piwik\Report\ReportWidgetFactory;
use Piwik\Widget\WidgetsList;

/**
 * Defines the GetBotStatsReport report.
 * See {@link https://developer.matomo.org/api-reference/Piwik/Plugin/Report} for more information.
 */
class GetOtherBots extends Base
{

    /**
     * @param \Piwik\Plugin\ViewDataTable $view
     */
    public function configureView(ViewDataTable $view)
    {
        $view->requestConfig->filter_limit = 10;
        $view->requestConfig->filter_sort_column = 'total';
        $view->requestConfig->filter_sort_order = 'desc';
        $view->config->columns_to_display = ["useragent", "total"];
        $view->requestConfig->filter_sort_column = 'total';
        $view->requestConfig->filter_sort_order = 'desc';
        $view->config->show_exclude_low_population = false;
        $view->config->show_search = false;
        $view->config->show_footer = true;
        $view->config->show_bar_chart = false;
    }

    /**
     * @return \Piwik\Plugin\Report[]
     */
    public function getRelatedReports()
    {
        return [];
    }

    public function configureWidgets(WidgetsList $widgetsList, ReportWidgetFactory $factory)
    {
        $config = $factory->createWidget();
        $config->setOrder(4);
        $config->setIsWide();
        $widgetsList->addWidgetConfig($config);
    }

    public function isEnabled()
    {
        return true;
    }

    protected function init()
    {
        parent::init();

        $this->name = Piwik::translate('BotTracker_Bot_Tracker_OtherBots');
        $this->subcategoryId = Piwik::translate('BotTracker_BotTracker');
        $this->documentation = Piwik::translate('BotTracker_Bot_Tracker_OtherBotsDocumentation');
        $this->order = 98;
    }

}

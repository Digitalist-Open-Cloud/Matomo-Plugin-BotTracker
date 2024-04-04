<?php

/**
 * BotTracker, a Matomo plugin by Digitalist Open Tech
 * Based on the work of Thomas--F (https://github.com/Thomas--F)
 * @link https://github.com/digitalist-se/BotTracker
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\BotTracker\Reports;

use Piwik\Piwik;
use Piwik\Plugin\ViewDataTable;
use Piwik\Widget\WidgetsList;
use Piwik\Report\ReportWidgetFactory;

/**
 * Defines the GetBotStatsReport report.
 * See {@link https://developer.matomo.org/api-reference/Piwik/Plugin/Report} for more information.
 */
class GetStatsReport extends Base
{
    protected function init()
    {
        parent::init();

        $this->name = Piwik::translate('BotTracker_Bot_Tracker_Report_Stats');
        $this->subcategoryId = Piwik::translate('BotTracker_BotTracker');
        $this->documentation = Piwik::translate('BotTracker_ReportDocumentation');
        $this->order = 98;
    }

    /**
     * @param ViewDataTable $view
     */
    public function configureView(ViewDataTable $view)
    {
        $view->config->translations['visit_timestamp'] = Piwik::translate('BotTracker_Visit_Timestamp');
        $view->requestConfig->filter_limit = 10;
        $view->requestConfig->filter_sort_column = 'total';
        $view->requestConfig->filter_sort_order = 'desc';
        $view->config->columns_to_display = ["botName", "visitId", "page", "visit_timestamp", "useragent"];
        $view->requestConfig->filter_sort_column = 'botName';
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
        $config->setOrder(3);
        $config->setIsWide();
        $widgetsList->addWidgetConfig($config);
    }

    public function isEnabled()
    {
        // @todo: Add check if there is any data in db_stats table, do not show if there is none.
        return true;
    }
}

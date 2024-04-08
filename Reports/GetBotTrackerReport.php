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
 * Defines the GetBotTrackerReport report.
 * See {@link https://developer.matomo.org/api-reference/Piwik/Plugin/Report} for more information.
 * @package Matomo_BotTracker
 */
class GetBotTrackerReport extends Base
{

    /**
     */
    public function configureView(ViewDataTable $view)
    {
        $view->config->translations['botId'] = Piwik::translate('BotTracker_BotId');
        $view->config->translations['botName'] = Piwik::translate('BotTracker_BotName');
        $view->config->translations['total'] = Piwik::translate('BotTracker_BotCount');
        $view->config->columns_to_display = ['botName', 'total'];
        $view->config->show_search = false;
        $view->config->show_footer_icons = false;
        $view->config->show_exclude_low_population = false;
        $view->config->show_table_all_columns = false;
        $view->config->show_insights = false;
        $view->config->show_related_reports = false;
        $view->config->show_pivot_by_subtable = false;
        $view->config->show_table_performance = false;
        $view->config->show_all_views_icons = false;
        $view->config->show_export = true;
        $view->requestConfig->filter_limit = 10;
        $view->requestConfig->filter_sort_column = 'total';
        $view->requestConfig->filter_sort_order = 'desc';
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
        $config->setOrder(1);
        $widgetsList->addWidgetConfig($config);
    }

    protected function init()
    {
        parent::init();

        $this->name = Piwik::translate('BotTracker_Bot_Tracker_Report');
        $this->subcategoryId = Piwik::translate('BotTracker_BotTracker');
        $this->documentation = Piwik::translate('BotTracker_ReportDocumentation');
        $this->order = 98;
    }

}

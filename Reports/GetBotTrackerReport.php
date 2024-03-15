<?php

/**
 * BotTracker, a Matomo plugin by Digitalist Open Tech
 * Based on the work of Thomas--F (https://github.com/Thomas--F)
 * @link https://github.com/digitalist-se/MatomoPlugin-BotTracker
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\BotTracker\Reports;

use Piwik\Piwik;
use Piwik\Plugin\ViewDataTable;
use Piwik\Widget\WidgetsList;
use Piwik\Report\ReportWidgetFactory;

/**
 * This class defines a new report.
 *
 * See {@link http://developer.piwik.org/api-reference/Piwik/Plugin/Report} for more information.
 */
class GetBotTrackerReport extends Base
{
    protected function init()
    {
        parent::init();

        $this->name = Piwik::translate('BotTracker_Bot_Tracker_Report');
        $this->subcategoryId = 'BotTracker';
        $this->documentation = Piwik::translate('BotTracker_ReportDocumentation');
        $this->order = 98;
    }

    /**
     * Here you can configure how your report should be displayed. For instance whether your report supports a search
     * etc. You can also change the default request config. For instance change how many rows are displayed by default.
     *
     * @param ViewDataTable $view
     */
    public function configureView(ViewDataTable $view)
    {
        $view->config->translations['botId'] = Piwik::translate('BotTracker_BotId');
        $view->config->translations['botName'] = Piwik::translate('BotTracker_BotName');
        $view->config->translations['total'] = Piwik::translate('BotTracker_BotCount');
        $view->config->columns_to_display = ['botName','total'];
        $view->config->show_search = false;
        $view->config->show_footer_icons = false;
        $view->config->show_exclude_low_population = false;
        $view->config->show_table_all_columns = false;
        $view->config->show_insights = false;
        $view->config->show_related_reports  = false;
        $view->config->show_pivot_by_subtable = false;
        $view->config->show_table_performance = false;
        $view->config->show_all_views_icons = false;
        $view->config->show_export = true;
        $view->requestConfig->filter_limit = 10;
        $view->requestConfig->filter_sort_column = 'total';
        $view->requestConfig->filter_sort_order = 'desc';
    }

    /**
     * Here you can define related reports that will be shown below the reports. Just return an array of related
     * report instances if there are any.
     *
     * @return \Piwik\Plugin\Report[]
     */
    public function getRelatedReports()
    {
        return [];
    }

    public function configureWidgets(WidgetsList $widgetsList, ReportWidgetFactory $factory)
    {
        $widgetsList->addWidgetConfig($factory->createWidget());
    }
}

<?php

/**
 * BotTracker, a Matomo plugin by Digitalist Open Tech
 * Based on the work of Thomas--F (https://github.com/Thomas--F)
 *
 * @link https://github.com/digitalist-se/BotTracker
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 * @deprecated since release 5.2.0
 */

namespace Piwik\Plugins\BotTracker\Reports;

use Piwik\Piwik;
use Piwik\Plugin\ViewDataTable;
use Piwik\Report\ReportWidgetFactory;
use Piwik\Widget\WidgetsList;

/**
 * Defines the GetBotTracker report.
 *
 * See {@link https://developer.matomo.org/api-reference/Piwik/Plugin/Report} for more information.
 *
 * @deprecated since v5.2.0, will be removed in v5.3.0
 */
class GetBotTracker extends Base
{

    /**
     * @param \Piwik\Plugin\ViewDataTable $view
     */
    public function configureView(ViewDataTable $view)
    {
        $view->config->translations['botId'] = Piwik::translate('BotTracker_BotId');
        $view->config->translations['botName'] = Piwik::translate('BotTracker_BotName');
        $view->config->translations['botKeyword'] = Piwik::translate('BotTracker_BotKeyword');
        $view->config->translations['botCount'] = Piwik::translate('BotTracker_BotCount');
        $view->config->translations['botLastVisit'] = Piwik::translate('BotTracker_BotLastVisit');
        $view->config->columns_to_display = ['botName', 'botCount', 'botLastVisit'];
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
        $view->requestConfig->filter_limit = 25;
        $view->requestConfig->filter_sort_column = 'botCount';
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
        $widgetsList->addWidgetConfig(
            $factory->createWidget()->setIsNotWidgetizable()
        );
    }

    protected function init()
    {
        parent::init();

        $this->name = Piwik::translate('BotTracker_Bot_Tracker_Total_Over_Time_Deprecated_Report');
        $this->subcategoryId = Piwik::translate('BotTracker_BotTracker');
        $this->order = 99;
    }

}

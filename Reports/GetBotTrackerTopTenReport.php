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
 * Defines the class GetBotTrackerTopTenReport report.
 * See {@link https://developer.matomo.org/api-reference/Piwik/Plugin/Report} for more information.
 */
class GetBotTrackerTopTenReport extends Base
{

    /**
     * Here you can configure how your report should be displayed. For instance whether your report supports a search
     * etc. You can also change the default request config. For instance change how many rows are displayed by default.
     */
    public function configureView(ViewDataTable $view)
    {
        $view->config->translations['value'] = Piwik::translate('BotTracker_hits_by_Bot');
        $view->config->show_footer_icons = true;
        $view->config->show_insights = false;
        $view->config->selectable_columns = ["value"];
        $view->config->show_related_reports = false;
        $view->config->show_table_all_columns = false;
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

    public function getDefaultTypeViewDataTable()
    {
        return 'graphPie';
    }

    public function alwaysUseDefaultViewDataTable()
    {
        return true;
    }

    public function configureWidgets(WidgetsList $widgetsList, ReportWidgetFactory $factory)
    {
        $config = $factory->createWidget();
        $config->setOrder(2);
        $widgetsList->addWidgetConfig($config);
    }

    protected function init()
    {
        parent::init();

        $this->name = Piwik::translate('BotTracker_Top_10_Bots');
        $this->subcategoryId = Piwik::translate('BotTracker_BotTracker');
        $this->documentation = Piwik::translate('BotTracker_TopTenDocumentation');
        // This defines in which order your report appears in the mobile app, in the menu and in the list of widgets
        $this->order = 98;
    }

}

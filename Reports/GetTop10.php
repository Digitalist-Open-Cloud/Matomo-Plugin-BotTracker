<?php

/**
 * BotTracker, a Matomo plugin by Digitalist Open Tech
 * Based on the work of Thomas--F (https://github.com/Thomas--F)
 * @link https://github.com/digitalist-se/MatomoPlugin-BotTracker
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 * @deprecated since v5.2.0, will be removed in v5.3.0
 */

namespace Piwik\Plugins\BotTracker\Reports;

use Piwik\Piwik;
use Piwik\Plugin\ViewDataTable;

/**
 * Defines the GetTop10 report.
 *
 * See {@link https://developer.matomo.org/api-reference/Piwik/Plugin/Report} for more information.
 */
class GetTop10 extends Base
{
    protected function init()
    {
        parent::init();

        $this->name = Piwik::translate('BotTracker_Top_10_Bots_Deprecated_Report');
        $this->subcategoryId = 'BotTracker';
        // This defines in which order your report appears in the mobile app, in the menu and in the list of widgets
        $this->order = 99;
    }

    /**
     * @param ViewDataTable $view
     */
    public function configureView(ViewDataTable $view)
    {
        $view->config->translations['value'] = Piwik::translate('BotTracker_hits_by_Bot');
        $view->config->show_footer_icons = true;
        $view->config->show_insights = false;
        $view->config->selectable_columns = ["value"];
        $view->config->show_related_reports  = false;
        $view->config->show_table_all_columns = false;
    }

    /**
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
}

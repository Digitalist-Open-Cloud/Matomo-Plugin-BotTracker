<?php

/**
 * BotTracker, a Matomo plugin by Digitalist Open Tech
 * Based on the work of Thomas--F (https://github.com/Thomas--F)
 *
 * @link https://github.com/digitalist-se/BotTracker
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\BotTracker;

use Piwik\Menu\MenuAdmin;
use Piwik\Piwik;
use Piwik\Plugin\Menu as MatomoMenu;

/**
 * @package Matomo_BotTracker
 */
class Menu extends MatomoMenu
{

    public function configureAdminMenu(MenuAdmin $menu)
    {
        if (Piwik::isUserHasSomeAdminAccess()) {
            $menu->registerMenuIcon('BotTracker_BotTracker', 'icon-drop');
            $menu->addItem('BotTracker_BotTracker', null, $this->urlForAction('index'), $order = 50);
            $menu->addItem('BotTracker_BotTracker', 'BotTracker_Config', $this->urlForAction('index'), $order = 51);
            $menu->addItem(
                'BotTracker_BotTracker',
                'BotTracker_Documentation',
                $this->urlForAction('docs'),
                $order = 52
            );
        }
    }

}

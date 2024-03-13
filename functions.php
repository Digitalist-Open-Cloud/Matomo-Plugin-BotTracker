<?php
/**
 * BotTracker, a Matomo plugin by Digitalist Open Tech
 * Based on the work of Thomas--F (https://github.com/Thomas--F)
 * @link https://github.com/digitalist-se/MatomoPlugin-BotTracker
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\BotTracker;

//use Piwik\Piwik;

function getActiveIcon($botActive)
{
    if ($botActive == 1) {
        $pathWithCode = 'plugins/BotTracker/images/ok.png';
    } else {
        $pathWithCode = 'plugins/BotTracker/images/delete.png';
    }

    return $pathWithCode;
}

function getRequest($key)
{
    return (isset($_REQUEST[$key]) && $_REQUEST[$key]) ? $_REQUEST[$key] : '';
}
function getServer($key)
{
    return (isset($_SERVER[$key]) && $_SERVER[$key]) ? $_SERVER[$key] : '';
}

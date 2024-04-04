<?php

/**
 * BotTracker, a Matomo plugin by Digitalist Open Tech
 * Based on the work of Thomas--F (https://github.com/Thomas--F)
 * @link https://github.com/digitalist-se/BotTracker
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\BotTracker;

use Piwik\Nonce;
use Piwik\Notification\Manager as NotificationManager;
use Piwik\Piwik;
use Piwik\View;
use Piwik\Plugins\SitesManager\API as APISitesManager;
use Piwik\Plugins\BotTracker\API as APIBotTracker;
use Piwik\Request;
use Piwik\Plugin\ControllerAdmin;

class Controller extends ControllerAdmin
{
    public string $nonce;
    /**
     * @property string $nonce
     */
    public function index($siteID = 0, $errorList = [])
    {
        Piwik::checkUserHasSuperUserAccess();

        if ($siteID == 0) {
            $request = Request::fromRequest();
            $siteID = $request->getIntegerParameter('idSite', 0);
        }

        $sitesList = APISitesManager::getInstance()->getSitesWithAdminAccess();
        $botList = APIBotTracker::getAllBotDataForConfig($siteID);

        $view = new View('@BotTracker/index');

        $this->setBasicVariablesView($view);
        $view->assign('sitesList', $sitesList);
        $view->assign('botList', $botList);
        $view->assign('idSite', $siteID);
        $view->assign('errorList', $errorList);

        $view->nonce = Nonce::getNonce('BotTracker.saveConfig');
        $view->notifications = NotificationManager::getAllNotificationsToDisplay();

        echo $view->render();
    }

    public function docs()
    {
        Piwik::checkUserHasSomeViewAccess();
        $info = "Bot Tracker Docs";
        return $this->renderTemplate('docs', array(
            'info' => $info
        ));
    }

    public function configReload()
    {
        Piwik::checkUserHasSuperUserAccess();

        $request = Request::fromRequest();
        $siteID = $request->getIntegerParameter('idSite', 0);

        $this->index($siteID);
    }

    public function configImport()
    {
        Piwik::checkUserHasSuperUserAccess();

        $errorList = [];
        $request = Request::fromRequest();
        $siteID = $request->getIntegerParameter('idSite', 0);

        if (is_uploaded_file($_FILES['importfile']['tmp_name'])) {
            $fileData = file_get_contents($_FILES['importfile']['tmp_name']);
            // remove linefeeds
            $order = ["\r\n", "\n", "\r"];
            $data = str_replace($order, '', $fileData);
            // divide data
            $parts = explode("|", $data);
            $count = 0;
            if (count($parts) % 2 == 0) {
                for ($i = 0; $i < count($parts); $i = $i + 2) {
                    $botX = APIBotTracker::getBotByName($siteID, $parts[$i]);
                    if (empty($botX)) {
                        APIBotTracker::insertBot($siteID, $parts[$i], 1, $parts[$i + 1], 0);
                        $count++;
                    }
                }
                $errorList[] = $count . " " . Piwik::translate('BotTracker_Message_bot_inserted');
            } else {
                    $errorList[] = Piwik::translate('BotTracker_Error_Fileimport_Not_Even');
            }
        } else {
                $errorList[] = Piwik::translate('BotTracker_Error_Fileimport_Upload');
        }
        $this->index($siteID, $errorList);
    }

    public function saveConfig()
    {
        try {
            Piwik::checkUserHasSuperUserAccess();
            $request = Request::fromRequest();
            $siteID = $request->getIntegerParameter('idSite', 0);

            $botList = APIBotTracker::getAllBotDataForConfig($siteID);

            $errorList = [];

            foreach ($botList as $bot) {
                $botName = trim(Request::fromRequest()->getStringParameter($bot['botId'] . '_botName', ''));
                $botKeyword = trim(Request::fromRequest()->getStringParameter($bot['botId'] . '_botKeyword', ''));
                $botActive = trim(Request::fromRequest()->getStringParameter($bot['botId'] . '_botActive', '0'));
                $extraStats = trim(Request::fromRequest()->getStringParameter($bot['botId'] . '_extraStats', '0'));

                if ($botName != $bot['botName'] ||
                    $botKeyword != $bot['botKeyword'] ||
                    $botActive != $bot['botActive'] ||
                    $extraStats != $bot['extra_stats']
                ) {
                    if (empty($botName)) {
                        $errorList[] = Piwik::translate('BotTracker_BotName') .
                        ' ' .
                        $bot['botId'] .
                        Piwik::translate('BotTracker_Error_empty');
                    } elseif (empty($botKeyword)) {
                        $errorList[] = Piwik::translate('BotTracker_BotKeyword') .
                        ' ' .
                        $bot['botId'] .
                        Piwik::translate('BotTracker_Error_empty');
                    } else {
                        APIBotTracker::updateBot($botName, $botKeyword, $botActive, $bot['botId'], $extraStats);
                    }
                }
            }
            $this->index($siteID, $errorList);
        } catch (\Exception $e) {
            echo $e;
        }
    }


    public function addNew()
    {
        try {
            Piwik::checkUserHasSuperUserAccess();
            $request = Request::fromRequest();
            $siteID = $request->getIntegerParameter('idSite', 0);

            // @remove?
            //$botList = APIBotTracker::getAllBotDataForConfig($siteID);

            $errorList = [];

            $botName = trim(Request::fromRequest()->getStringParameter('new_botName', ''));
            $botKeyword = trim(Request::fromRequest()->getStringParameter('new_botKeyword', ''));
            $botActive = trim(Request::fromRequest()->getStringParameter('new_botActive', '0'));
            $extraStats = trim(Request::fromRequest()->getStringParameter('new_extraStats', '0'));
            if ($botName    != '' ||
                $botKeyword != ''
            ) {
                if (empty($botName)) {
                        $errorList[] = Piwik::translate('BotTracker_BotName') .
                        Piwik::translate('BotTracker_Error_empty');
                } elseif (empty($botKeyword)) {
                        $errorList[] = Piwik::translate('BotTracker_BotKeyword') .
                        Piwik::translate('BotTracker_Error_empty');
                } else {
                    APIBotTracker::insertBot($siteID, $botName, $botActive, $botKeyword, $extraStats);
                }
            }
            $errorList[] = 'Bot ' . $botName . ' ' . Piwik::translate('BotTracker_Added');

            $this->index($siteID, $errorList);
        } catch (\Exception $e) {
            echo $e;
        }
    }

    public function deleteBotEntry()
    {
        try {
            Piwik::checkUserHasSuperUserAccess();
            $request = Request::fromRequest();
            $siteID = $request->getIntegerParameter('idSite', 0);
            $botId = trim(Request::fromRequest()->getStringParameter('botId', ''));

            $errorList = [];

            $APIBotTracker = new APIBotTracker();
            $APIBotTracker->deleteBot($botId);

            $errorList[] = 'Bot ' . $botId . ' ' . Piwik::translate('BotTracker_Message_deleted');
            $this->index($siteID, $errorList);
        } catch (\Exception $e) {
            echo $e;
        }
    }

    public function configInsertDb()
    {
        try {
            Piwik::checkUserHasSuperUserAccess();
            $request = Request::fromRequest();
            $siteID = $request->getIntegerParameter('idSite', 0);

            $errorList = [];
            $i = APIBotTracker::insertDefaultBots($siteID);
            $errorList[] = $i . " " . Piwik::translate('BotTracker_Message_bot_inserted');

            $this->index($siteID, $errorList);
        } catch (\Exception $e) {
            echo $e;
        }
    }
}

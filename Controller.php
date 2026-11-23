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
    public const NONCE = 'BotTracker.saveConfig';

    // What an import file may hold. A list of some hundred bots is a few kilobytes.
    private const MAX_IMPORT_BYTES = 50000;

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

        $view->nonce = Nonce::getNonce(self::NONCE);
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

    /**
     * Adds the bots of a posted file: names and keywords separated by "|".
     */
    public function configImport()
    {
        $siteID = $this->checkPostAndGetSite();
        $errorList = [];

        $file = $_FILES['importfile'] ?? null;
        if (is_array($file) && is_string($file['tmp_name'] ?? null) && is_uploaded_file($file['tmp_name']) && filesize($file['tmp_name']) <= self::MAX_IMPORT_BYTES) {
            $errorList = $this->importBots($siteID, (string) file_get_contents($file['tmp_name']));
        } else {
            $errorList[] = Piwik::translate('BotTracker_Error_Fileimport_Upload');
        }

        $this->index($siteID, $errorList);
    }

    /**
     * @return string[] what to tell the user
     */
    private function importBots(int $siteID, string $data): array
    {
        $parts = explode('|', str_replace(["\r\n", "\n", "\r"], '', $data));
        if (count($parts) % 2 !== 0) {
            return [Piwik::translate('BotTracker_Error_Fileimport_Not_Even')];
        }

        $count = 0;
        try {
            for ($i = 0; $i < count($parts); $i += 2) {
                if (empty(APIBotTracker::getBotByName($siteID, $parts[$i]))) {
                    APIBotTracker::insertBot($siteID, $parts[$i], 1, $parts[$i + 1], 0);
                    $count++;
                }
            }
        } catch (\Exception $e) {
            return [$count . ' ' . Piwik::translate('BotTracker_Message_bot_inserted'), $e->getMessage()];
        }

        return [$count . ' ' . Piwik::translate('BotTracker_Message_bot_inserted')];
    }

    public function saveConfig()
    {
        $siteID = $this->checkPostAndGetSite();
        $post = Request::fromPost();
        $errorList = [];

        foreach (APIBotTracker::getAllBotDataForConfig($siteID) as $bot) {
            $botName = trim($post->getStringParameter($bot['botId'] . '_botName', ''));
            $botKeyword = trim($post->getStringParameter($bot['botId'] . '_botKeyword', ''));
            $botActive = trim($post->getStringParameter($bot['botId'] . '_botActive', '0'));
            $extraStats = trim($post->getStringParameter($bot['botId'] . '_extraStats', '0'));
            if (
                $botName == $bot['botName'] &&
                $botKeyword == $bot['botKeyword'] &&
                $botActive == $bot['botActive'] &&
                $extraStats == $bot['extra_stats']
            ) {
                continue;
            }

            if ($botName === '') {
                $errorList[] = Piwik::translate('BotTracker_BotName') . ' ' . $bot['botId'] . Piwik::translate('BotTracker_Error_empty');
            } elseif ($botKeyword === '') {
                $errorList[] = Piwik::translate('BotTracker_BotKeyword') . ' ' . $bot['botId'] . Piwik::translate('BotTracker_Error_empty');
            } else {
                try {
                    APIBotTracker::updateBot($siteID, $botName, $botKeyword, $botActive, $bot['botId'], $extraStats);
                } catch (\Exception $e) {
                    $errorList[] = $e->getMessage();
                }
            }
        }

        $this->index($siteID, $errorList);
    }

    public function addNew()
    {
        $siteID = $this->checkPostAndGetSite();
        $post = Request::fromPost();
        $errorList = [];

        $botName = trim($post->getStringParameter('new_botName', ''));
        $botKeyword = trim($post->getStringParameter('new_botKeyword', ''));
        if ($botName === '') {
            $errorList[] = Piwik::translate('BotTracker_BotName') . Piwik::translate('BotTracker_Error_empty');
        } elseif ($botKeyword === '') {
            $errorList[] = Piwik::translate('BotTracker_BotKeyword') . Piwik::translate('BotTracker_Error_empty');
        } else {
            try {
                APIBotTracker::insertBot($siteID, $botName, $post->getStringParameter('new_botActive', '0'), $botKeyword, $post->getStringParameter('new_extraStats', '0'));
                $errorList[] = 'Bot ' . $botName . ' ' . Piwik::translate('BotTracker_Added');
            } catch (\Exception $e) {
                $errorList[] = $e->getMessage();
            }
        }

        $this->index($siteID, $errorList);
    }

    public function deleteBotEntry()
    {
        $siteID = $this->checkPostAndGetSite();
        $botId = Request::fromPost()->getIntegerParameter('botId', 0);

        (new APIBotTracker())->deleteBot($siteID, $botId);

        $this->index($siteID, ['Bot ' . $botId . ' ' . Piwik::translate('BotTracker_Message_deleted')]);
    }

    public function configInsertDb()
    {
        $siteID = $this->checkPostAndGetSite();

        $count = APIBotTracker::insertDefaultBots($siteID);

        $this->index($siteID, [$count . ' ' . Piwik::translate('BotTracker_Message_bot_inserted')]);
    }

    /**
     * A change is posted by a super user with the nonce of the page it comes from.
     *
     * @return int the website the change is for
     */
    private function checkPostAndGetSite(): int
    {
        Piwik::checkUserHasSuperUserAccess();

        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            throw new \Exception(Piwik::translate('BotTracker_Error_Not_Posted'));
        }
        Nonce::checkNonce(self::NONCE, Request::fromPost()->getStringParameter('form_nonce', ''));

        return Request::fromRequest()->getIntegerParameter('idSite', 0);
    }
}

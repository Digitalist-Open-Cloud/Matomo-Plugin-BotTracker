<?php

/**
 * BotTracker, a Matomo plugin by Digitalist Open Tech
 * Based on the work of Thomas--F (https://github.com/Thomas--F)
 * @link https://github.com/digitalist-se/MatomoPlugin-BotTracker
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\BotTracker\Commands;

use Piwik\Plugin\ConsoleCommand;
use Symfony\Component\Console\Input\InputOption;
use Piwik\Plugins\BotTracker\API;

/**
 * Add new bot.
 */
class DeleteBot extends ConsoleCommand
{
    protected function configure()
    {
        $HelpText = 'The <info>%command.name%</info> command will add a new bot.
<comment>Samples:</comment>
To run:
<info>%command.name% --botId=1</info>';
        $this->setHelp($HelpText);
        $this->setName('bottracker:delete-bot');
        $this->setDescription('Deletes a bot.');
        $this->setDefinition(
            [
                new InputOption(
                    'botId',
                    null,
                    InputOption::VALUE_REQUIRED,
                    'Id of bot',
                    null
                )
            ]
        );
    }

    /**
     * Execute the command like: ./console bottracker:delete-bot"
     */
    protected function doExecute(): int
    {
        $input = $this->getInput();
        $output = $this->getOutput();
        $botId = $input->getOption('botId');

        if (!isset($botId)) {
            $output->write("<error>You need to provide the bot id (--botId)</error>\n");
            return self::FAILURE;
        }

        $botAPI = new API();
        $botAPI->deleteBot($botId);
        $output->write("<info>Bot with id $botId deleted.</info>\n");
        return self::SUCCESS;
    }
}

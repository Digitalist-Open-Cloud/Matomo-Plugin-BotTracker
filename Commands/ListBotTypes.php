<?php

/**
 * BotTracker, a Matomo plugin by Digitalist Open Tech
 * Based on the work of Thomas--F (https://github.com/Thomas--F)
 * @link https://github.com/digitalist-se/BotTracker
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\BotTracker\Commands;

use Piwik\Plugin\ConsoleCommand;
use Piwik\Plugins\BotTracker\API;

/**
 * List bot types.
 */
class ListBotTypes extends ConsoleCommand
{
    protected function configure()
    {
        $HelpText = 'The <info>%command.name%</info> will add the default bots included.
<comment>Samples:</comment>
To run:
<info>%command.name%</info>';
        $this->setHelp($HelpText);
        $this->setName('bottracker:list-bot-types');
        $this->setDescription('List bot types added.');
    }

    /**
     * Execute the command like: ./console bottracker:list-bot-types"
     */
    protected function doExecute(): int
    {

        $output = $this->getOutput();
        $botAPI = new API();

        $types = $botAPI->getBotTypes();
        $output->writeln("<info>*****</info>");
        $output->writeln("<info>Bot type <comment>name</comment> and <comment>id</comment> in database</info>");
        $output->writeln("<info>*****</info>");
        foreach ($types as $key => $botType) {
            foreach ($botType as $key => $botType) {
                $output->write("<info>$key: </info>");
                $output->writeln("<info><comment>$botType</comment></info>");
            }
            $output->writeln("<info>*****</info>");
        }
        return self::SUCCESS;
    }
}

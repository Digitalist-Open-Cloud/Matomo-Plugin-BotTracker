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
class ListBots extends ConsoleCommand
{
    protected function configure()
    {
        $HelpText = 'The <info>%command.name%</info> command will list bots for site id.
<comment>Samples:</comment>
To run:
<info>%command.name% --idsite=1</info>';
        $this->setHelp($HelpText);
        $this->setName('bottracker:list-bots');
        $this->setDescription('List bots for site id.');
        $this->setDefinition(
            [
                new InputOption(
                    'idsite',
                    null,
                    InputOption::VALUE_REQUIRED,
                    'SiteId',
                    null
                )
            ]
        );
    }

    /**
     * Execute the command like: ./console bottracker:list-bots"
     */
    protected function doExecute(): int
    {
        $input = $this->getInput();
        $output = $this->getOutput();
        $idsite = $input->getOption('idsite');

        $botAPI = new API();

        if (!isset($idsite)) {
            $output->write("<error>You need to provide a site id for the bot</error>\n");
            return self::FAILURE;
        }
        $bots = $botAPI->getAllBotDataForConfig($idsite);

        $output->writeln("<info>*****</info>");
        $output->writeln("<info>Bot type <comment>name</comment> and <comment>id</comment> in database</info>");
        $output->writeln("<info>*****</info>");
        foreach ($bots as $key => $bot) {
            foreach ($bot as $key => $bot) {
                $output->write("<info>$key: </info>");
                $output->writeln("<info><comment>$bot</comment></info>");
            }
            $output->writeln("<info>*****</info>");
        }
        return self::SUCCESS;
    }
}

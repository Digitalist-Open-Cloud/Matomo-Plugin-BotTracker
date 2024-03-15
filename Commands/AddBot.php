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
class AddBot extends ConsoleCommand
{
    protected function configure()
    {
        $HelpText = 'The <info>%command.name%</info> command will add a new bot.
<comment>Samples:</comment>
To run:
<info>%command.name% --idsite=1 --name=Foo --agent-string=foo-bot</info>';
        $this->setHelp($HelpText);
        $this->setName('bottracker:add-bot');
        $this->setDescription('Add new bot.');
        $this->setDefinition(
            [
                new InputOption(
                    'idsite',
                    null,
                    InputOption::VALUE_REQUIRED,
                    'SiteId',
                    null
                ),
                new InputOption(
                    'name',
                    null,
                    InputOption::VALUE_REQUIRED,
                    'Name',
                    null
                ),
                new InputOption(
                    'agent-string',
                    null,
                    InputOption::VALUE_REQUIRED,
                    'Agent string',
                    null
                ),
            ]
        );
    }

    /**
     * Execute the command like: ./console bottracker:add-bot-type"
     */
    protected function doExecute(): int
    {
        $input = $this->getInput();
        $output = $this->getOutput();
        $idsite = $input->getOption('idsite');
        $name = $input->getOption('name');
        $agent = $input->getOption('agent-string');

        $botAPI = new API();

        if (!isset($idsite)) {
            $output->write("<error>You need to provide a site id for the bot</error>\n");
            return self::FAILURE;
        }
        if (!isset($name)) {
            $output->write("<error>You need to provide a name for the bot</error>\n");
            return self::FAILURE;
        }
        if (!isset($agent)) {
            $output->write("<error>You need to provide a agent string for the bot</error>\n");
            return self::FAILURE;
        }
        $botAPI->insertBot($idsite, $name, 1, $agent, 0, 0);
        $output->write("<info>New bot added, $name</info>\n");
        return self::SUCCESS;
    }
}

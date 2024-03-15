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
 * Add default bots.
 */
class AddDefaultBots extends ConsoleCommand
{
    protected function configure()
    {
        $HelpText = 'The <info>%command.name%</info> will add the default bots included.
<comment>Samples:</comment>
To run:
<info>%command.name%</info>';
        $this->setHelp($HelpText);
        $this->setName('bottracker:add-default-bots');
        $this->setDescription('Add default bots.');
        $this->setDefinition(
            [
                new InputOption(
                    'idsite',
                    null,
                    InputOption::VALUE_OPTIONAL,
                    'SiteId',
                    null
                )
            ]
        );
    }

    /**
     * Execute the command like: ./console bottracker:add-default-bots"
     */
    protected function doExecute(): int
    {
        $input = $this->getInput();
        $output = $this->getOutput();
        $site = $input->getOption('idsite');

        $botAPI = new API();

        if (isset($site)) {
            $botAPI->insertDefaultBots($site);
            $output->write("<info>Default bots added</info>\n");
            return self::SUCCESS;
        } else {
            $output->write("<error>You need to provide the idsite argument</error>\n");
            return self::FAILURE;
        }
    }
}

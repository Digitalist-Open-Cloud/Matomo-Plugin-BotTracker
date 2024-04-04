<?php

/**
 * BotTracker, a Matomo plugin by Digitalist Open Tech
 * Based on the work of Thomas--F (https://github.com/Thomas--F)
 * @link https://github.com/digitalist-se/BotTracker
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\BotTracker\Commands;

use Piwik\Plugin\ConsoleCommand;
use Symfony\Component\Console\Input\InputOption;
use Piwik\Plugins\BotTracker\API;

/**
 * Add new bot type.
 */
class AddBotType extends ConsoleCommand
{
    protected function configure()
    {
        $HelpText = 'The <info>%command.name%</info> command will add a new bot type.
<comment>Samples:</comment>
To run:
<info>%command.name% --type=FooBar</info>';
        $this->setHelp($HelpText);
        $this->setName('bottracker:add-bot-type');
        $this->setDescription('Add new bot type.');
        $this->setDefinition(
            [
                new InputOption(
                    'type',
                    null,
                    InputOption::VALUE_REQUIRED,
                    'Type',
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
        $type = $input->getOption('type');

        $botAPI = new API();

        if (isset($type)) {
            $botAPI->addBotType($type);
            $output->write("<info>New bot type, $type added</info>\n");
            return self::SUCCESS;
        } else {
            $output->write("<error>You need to provide a type for the bot</error>\n");
            return self::FAILURE;
        }
    }
}

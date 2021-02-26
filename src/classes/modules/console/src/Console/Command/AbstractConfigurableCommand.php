<?php
/**
 *
 *
 * @package     i-doit
 * @subpackage
 * @author      Pavel Abduramanov <pabduramanov@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */

namespace idoit\Module\Console\Console\Command;

use Exception;
use idoit\Module\Console\Option\Option;
use idoit\Module\Console\Steps\Message\ErrorLevel;
use idoit\Module\Console\Steps\Message\Message;
use idoit\Module\Console\Steps\Message\Messages;
use idoit\Module\Console\Steps\Message\ResultMessage;
use idoit\Module\Console\Steps\Step;
use idoit\Module\Console\Steps\Undoable;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

abstract class AbstractConfigurableCommand extends Command
{
    /**
     * @var Option[]
     */
    private $values = [];

    protected function addValue(Option $option)
    {
        $this->values[$option->getSettingName()] = $option;

        return $this;
    }

    protected function getValue($name)
    {
        if (!isset($this->values[$name])) {
            return null;
        }

        return $this->values[$name]->getValue();
    }

    /**
     * @param string $name
     *
     * @return mixed
     */
    protected function getOptionValue($name)
    {
        foreach ($this->values as $value) {
            $option = $value->getOption();

            if ($option === null) {
                continue;
            }

            if ($option->getName() === $name) {
                return $value->getValue();
            }
        }
        return null;
    }

    /**
     * Configure the command
     */
    protected function configure()
    {
        $definition = $this->getDefinition();
        array_walk(
            $this->values,
            function ($option) use (&$definition) {
                if (!$option instanceof Option || !$option->getOption()) {
                    return;
                }
                $definition->addOption($option->getOption());
            }
        );
        parent::configure();
    }

    /**
     * Process the command - ask user to provide values, create step and process it
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int|mixed|null|void
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        \isys_module_manager::instance()->module_loader();

        $exitCode = Command::SUCCESS;

        foreach ($this->values as $option) {
            if ($option instanceof Option && $option->getOption()) {
                $option->setValue($input->getOption($option->getOption()
                                                        ->getName()));
            }
        }

        $style = new SymfonyStyle($input, $output);

        if ($input->isInteractive()) {
            foreach ($this->values as $option) {
                if ($option instanceof Option && $option->getOption()) {
                    $option->setValue($option->ask($style));
                }
            }
        }

        $required = [];

        foreach ($this->values as $option) {
            if ($option instanceof Option && $option->getOption()) {
                $inputOption = $option->getOption();
                if ($inputOption->isValueRequired() && empty($option->getValue())) {
                    $required[] = $option->getOption()
                        ->getName();
                }
            }
        }

        if (!empty($required)) {
            throw new Exception('Following options has to be defined: ' . implode(', ', $required));
        }

        $step = $this->createStep();

        if (!$step) {
            return -1;
        }

        $messages = new Messages();

        $result = $step->process($messages);

        if (!$result) {
            $exitCode = -1;

            if ($step instanceof Undoable) {
                $step->undo($messages);
            }
        }

        $this->renderMessages($messages, $input, $output);

        return $exitCode;
    }

    /**
     * Create the work
     *
     * @return Step
     */
    abstract protected function createStep();

    /**
     * Render messages
     *
     * @param Messages        $messages
     * @param InputInterface  $input
     * @param OutputInterface $output
     */
    protected function renderMessages(Messages $messages, InputInterface $input, OutputInterface $output)
    {
        $style = new SymfonyStyle($input, $output);
        $style->table(
            ['Message', 'Result'],
            array_filter(array_map(
                function ($message) use ($output) {
                    if (!$message instanceof Message || $message->getMessage() === '') {
                        return null;
                    }

                    $status = '';

                    switch ($message->getLevel()) {
                        case ErrorLevel::ERROR:
                        case ErrorLevel::FATAL:
                            $status = '<error>FAIL</error>';
                            break;
                        case ErrorLevel::INFO:
                            $status = '<info>OK</info>';
                            break;
                        case ErrorLevel::NOTIFICATION:
                            $status = '<fg=yellow>WARN</fg=yellow>';
                            break;
                        case ErrorLevel::DEBUG:
                            if ($output->getVerbosity() <= OutputInterface::VERBOSITY_NORMAL) {
                                return null;
                            }
                            $status = '<info>OK</info>';
                            break;
                    }

                    if ($message instanceof ResultMessage) {
                        if (!$message->getResult()) {
                            $status = '<error>FAIL</error>';
                        }
                    }

                    return [
                      $message->getMessage(),
                      $status
                    ];
                },
                $messages->getMessages()
            ))
        );
    }
}

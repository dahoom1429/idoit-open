<?php

namespace idoit\Console\Command\Cleanup;

use idoit\Console\Command\AbstractCommand;
use isys_auth_module_dao;
use isys_contact_dao_person;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ClearCredentialsCommand extends AbstractCommand
{
    const NAME = 'clear-credentials';

    /**
     * Get name for command
     *
     * @return string
     */
    public function getCommandName()
    {
        return self::NAME;
    }

    /**
     * Get description for command
     *
     * @return string
     */
    public function getCommandDescription()
    {
        return 'It removes both attributes `username` and `password` from the users "login" category';
    }

    /**
     * Retrieve Command InputDefinition
     *
     * @return InputDefinition
     */
    public function getCommandDefinition()
    {
        $definition = new InputDefinition();
        $definition->addOption(new InputOption('object', null, InputOption::VALUE_REQUIRED, 'the user object id'));

        return $definition;
    }

    /**
     * Checks if a command can have a config file via --config
     *
     * @return bool
     */
    public function isConfigurable()
    {
        return false;
    }

    /**
     * Returns an array of command usages
     *
     * @return string[]
     */
    public function getCommandUsages()
    {
        return [];
    }

    protected function getObjectId(InputInterface $input)
    {
        $objectId = (int)$input->getOption('object');

        return $objectId;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $personObjectId = $this->getObjectId($input);

            $output->writeln("Looking for person with object ID = $personObjectId");

            $personDao = isys_contact_dao_person::instance($this->container->database);

            $personData = $personDao->get_data_by_id($personObjectId)->get_row();

            if (!$personData) {
                $output->writeln("Person with this object ID not found");
                return Command::FAILURE;
            }
            $output->writeln("Person '" . $personData['isys_cats_person_list__title'] . "' found.");
            $output->writeln("Clear credentials for this person.");

            $sql = "UPDATE isys_cats_person_list
                SET isys_cats_person_list__user_pass = NULL, 
                isys_cats_person_list__title = NULL 
                WHERE isys_cats_person_list__isys_obj__id = " . $personObjectId;

            $personDao->update($sql);
            $personDao->apply_update();

            $output->writeln("Clearing done.");
        } catch (\Exception $e) {
            $output->writeln("<error>There was an error while clearing credentials.</error>");
            return Command::FAILURE;
        }
        return Command::SUCCESS;
    }
}

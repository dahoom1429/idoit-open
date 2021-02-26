<?php

namespace idoit\Console\Command\Import\Csv;

use idoit\Component\Logger;
use idoit\Console\Command\AbstractCommand;
use idoit\Console\Exception\MissingFileException;
use isys_format_json;
use isys_module_dao_import_log;
use isys_module_import_csv;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ImportCsvCommand extends AbstractCommand
{
    const NAME = 'import-csv';

    private static $multiValueModes = [
        'row',
        'column',
        'comma'
    ];

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
        return 'Imports CSV formatted files (Using a predefined CSV Import filter, defined in the GUI)';
    }

    /**
     * Retrieve Command InputDefinition
     *
     * @return InputDefinition
     */
    public function getCommandDefinition()
    {
        $definition = new InputDefinition();
        $definition->addOption(new InputOption('importFile', null, InputOption::VALUE_REQUIRED, 'CSV file for import'));
        $definition->addOption(new InputOption('importProfileId', null, InputOption::VALUE_REQUIRED, 'Profile which should be used to map import file'));
        $definition->addOption(new InputOption('csvSeparator', null, InputOption::VALUE_REQUIRED, 'Separator of import file'));
        $definition->addOption(new InputOption('multiValueMode', null, InputOption::VALUE_REQUIRED, 'Multivalue mode. Possible modes are "row", "column" or "comma"'));

        return $definition;
    }

    /**
     * Checks if a command can have a config file via --config
     *
     * @return bool
     */
    public function isConfigurable()
    {
        return true;
    }

    /**
     * Returns an array of command usages
     *
     * @return string[]
     */
    public function getCommandUsages()
    {
        return [
            '-u admin -p admin -i 1 --importFile /var/www/imports/idoit-Demo-CSV-Import.csv --importProfileId 1 --csvSeparator ";" --multiValueMode column'
        ];
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<info>csv-Import handler initialized at </info>' . date('Y-m-d H:i:s'));

        try {
            $this->process($input, $output);
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
            return Command::FAILURE;
        }
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @throws MissingFileException
     * @throws \idoit\Exception\JsonException
     * @throws \isys_exception_database
     */
    private function process(InputInterface $input, OutputInterface $output)
    {
        // Initialize:
        $l_file = $input->getOption('importFile');
        $l_profile_id = $input->getOption('importProfileId');
        $l_delimiter = $input->getOption('csvSeparator');
        $l_multivalue_mode = $input->getOption('multiValueMode');
        $l_object_type_assignment = [];
        $l_identificationCounter = 1;

        // Ensure that setted multivalue mode is valid.
        if (!in_array($l_multivalue_mode, self::$multiValueModes)) {
            throw new InvalidArgumentException(sprintf('Given mode %s is not allowed, choose one of these: %s', $l_multivalue_mode, implode(',', self::$multiValueModes)));
        }

        // Check file:
        if (!file_exists($l_file)) {
            throw new MissingFileException('File "' . $l_file . '" does not exist.');
        }

        // Load Profile.
        if (is_numeric($l_profile_id)) {
            $l_profiles = isys_module_import_csv::get_profiles($l_profile_id);
            $output->writeln('Retrieve profile with id #' . $l_profile_id . '...');

            if (is_array($l_profiles) && count($l_profiles)) {
                // Get first profile.
                $l_profile = $l_profiles[0];

                // Decode data attribute into array.
                $l_profile['data'] = isys_format_json::decode($l_profile['data']);

                // Check for filled profile
                if (is_array($l_profile['data'])) {
                    // Some transformation work
                    $l_key_data = [];
                    $l_transformed_assignment = [];

                    foreach ($l_profile['data']['assignments'] as $l_index => $l_data) {
                        if (empty($l_data['category'])) {
                            continue;
                        }

                        // Empty property means we have object_title, category.
                        if (!defined($l_data['category']) && empty($l_data['property'])) {
                            $l_key_data[$l_data['category']] = $l_index;
                        } else {
                            if (strpos(isys_module_import_csv::CL__MULTIVALUE_TYPE__COLUMN, $l_multivalue_mode) === false) {
                                $l_transformed_assignment[$l_data['category']][$l_data['property']] = $l_index;
                            } else {
                                $l_transformed_assignment[$l_index] = $l_data;
                            }

                            if (isset($l_data['object_type']) && isset($l_data['create_object'])) {
                                $l_object_type_assignment[$l_index] = [
                                    'object-type'   => $l_data['object_type'],
                                    'create-object' => (int)$l_data['create_object']
                                ];
                            }
                        }
                    }
                } else {
                    throw new RuntimeException('Profile does not have any data.');
                }

                $output->writeln('Profile ' . $l_profile['title'] . ' succesfully loaded.');
            } else {
                throw new RuntimeException('Unable to load profile with ID #' . $l_profile_id);
            }
        } else {
            throw new InvalidArgumentException('The given profile ID is not numeric');
        }

        // Collect necessary information for the import process
        $output->writeln('Initializing csv-import...');

        if (defined($l_profile['data']['globalObjectType'])) {
            $l_profile['data']['globalObjectType'] = constant($l_profile['data']['globalObjectType']);
        }

        switch ($output->getVerbosity()) {
            default:
            case $output::VERBOSITY_NORMAL:
                $importVerbosity = Logger::WARNING;
                break;

            case $output::VERBOSITY_VERBOSE:
                $importVerbosity = Logger::NOTICE;
                break;

            case $output::VERBOSITY_VERY_VERBOSE:
                $importVerbosity = Logger::INFO;
                break;

            case $output::VERBOSITY_DEBUG:
                $importVerbosity = Logger::DEBUG;
                break;
        }

        // @see  ID-7076  Fix the additional property search definition.
        $additionalPropertySearch = [];

        if (\is_array($l_profile['data']['assignments']) && \is_array($l_profile['data']['additionalPropertySearch'])) {
            foreach ($l_profile['data']['additionalPropertySearch'] as $index => $definition) {
                if (!isset($l_profile['data']['assignments'][$index]['category'])) {
                    continue;
                }

                $key = $l_profile['data']['assignments'][$index]['category'] . '_' . $index;

                $additionalPropertySearch[$key] = isys_format_json::encode($definition);
            }
        }

        // Initialize csv module
        $l_module_csv = new isys_module_import_csv(
            $l_file,
            $l_delimiter,
            $l_multivalue_mode,
            $l_key_data['object_title'],
            $l_profile['data']['globalObjectType'],
            $l_key_data['object_type_dynamic'],
            $l_key_data['object_purpose'],
            $l_key_data['object_category'],
            $l_key_data['object_sysid'],
            $l_key_data['object_cmdbstatus'],
            $l_key_data['object_description'],
            (bool)$l_profile['data']['headlines'],
            $l_profile['data']['defaultTemplate'],
            $additionalPropertySearch,
            $l_profile['data']['multivalueUpdateMode'],
            $importVerbosity
        );

        if (is_array($l_profile['data']['identificationKeys']) && count($l_profile['data']['identificationKeys']) > 0) {
            $l_csv_idents = [];
            $l_identifiers = [];

            // See ID-4162
            if (isset($l_profile['data']['identificationCounter']) && $l_profile['data']['identificationCounter'] > 1) {
                $l_identificationCounter = (int)$l_profile['data']['identificationCounter'];
            }

            foreach ($l_profile['data']['identificationKeys'] as $l_data) {
                $l_csv_idents[] = $l_data['csvIdent'];
                $l_identifiers[] = $l_data['localIdent'];
            }

            $l_module_csv->set_matching_csv_identifiers($l_csv_idents);
            $l_module_csv->set_matching_identifiers($l_identifiers);
            // @see  ID-6908  Set the "min match limit" in order to fix the bug.
            $l_module_csv->set_matching_min_match_limit($l_identificationCounter);

            $output->writeln('Found ' . count($l_profile['data']['identificationKeys']) . ' identification fields of which at least ' . $l_identificationCounter .
                ' need to match!');
        }

        $l_module_csv->initialize($l_transformed_assignment, $l_object_type_assignment);

        // Trigger import.
        $l_module_csv->import();

        // ID-2890 - Log changes.
        $l_import_dao = new isys_module_dao_import_log($this->container->database);
        $l_import_entry = $l_import_dao->add_import_entry(
            $l_import_dao->get_import_type_by_const('C__IMPORT_TYPE__CSV'),
            basename($l_file),
            null
        );

        $l_module_csv->save_log($l_import_entry);

        // Output log
        if (file_exists($l_module_csv->get_log_path()) && is_readable($l_module_csv->get_log_path())) {
            $output->writeln(file_get_contents($l_module_csv->get_log_path()));
        }

        $output->writeln("Successfully imported data.");
    }
}

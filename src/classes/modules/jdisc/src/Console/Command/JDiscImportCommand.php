<?php

namespace idoit\Module\JDisc\Console\Command;

use Exception;
use idoit\Console\Command\AbstractCommand;
use idoit\Console\Command\IsysLogWrapper;
use idoit\Context\Context;
use idoit\Module\Cmdb\Search\Index\Signals as SearchIndexSignals;
use isys_application;
use isys_array;
use isys_cmdb_dao_category_g_cloud_subscriptions;
use isys_cmdb_dao_category_g_identifier;
use isys_import_handler_cmdb;
use isys_jdisc_dao_category_connection_endpoint;
use isys_jdisc_dao_cluster;
use isys_jdisc_dao_devices;
use isys_jdisc_dao_matching;
use isys_jdisc_dao_network;
use isys_jdisc_dao_software;
use isys_jdisc_dao_software_database;
use isys_log;
use isys_module_jdisc;
use isys_cmdb_dao_jdisc;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\StreamOutput;

class JDiscImportCommand extends AbstractCommand
{
    const NAME = 'import-jdisc';

    /**
     * Log instance.
     *
     * @var  isys_log
     */
    protected $log = null;

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int|void|null
     * @throws Exception
     * @todo NEEDS REFACTORING!
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->log = IsysLogWrapper::instance();
        $this->log->setOutput(new StreamOutput(fopen('log/import_jdisc_' . date('Y-m-d_H_i_s') . '.log', 'a')));

        // Start logging.
        if ($input->getOption('detailedLogging')) {
            $this->log->set_log_level(isys_log::C__ALL & ~isys_log::C__DEBUG)
                ->set_verbose_level(isys_log::C__FATAL | isys_log::C__ERROR | isys_log::C__WARNING | isys_log::C__NOTICE);
        }

        $this->log->set_verbose_level(isys_log::C__WARNING | isys_log::C__ERROR | isys_log::C__FATAL);

        // JDisc module
        $l_jdisc = isys_module_jdisc::factory();

        if ($input->getOption('listProfiles')) {
            $this->listProfiles($l_jdisc->get_jdisc_profiles(), $output);

            return Command::SUCCESS;
        }

        $l_import_counter = 0;

        if (!$input->getOption('profile')) {
            $output->writeln('<error>Please provide the jdisc profile!</error>');
            return Command::FAILURE;
        }

        // Retrieving the profile.
        if ($input->getOption('profile')) {
            $l_profile = $input->getOption('profile');
        }

        // Retrieving the jdisc server.
        if ($input->getOption('server')) {
            $l_jdisc_server = $input->getOption('server');
        }

        // Retrieving info if overlapped host addresses should be overwritten or not.
        $l_overwrite_host_addresses = (bool)$input->getOption('overwriteHost');

        if (!is_numeric($l_jdisc_server)) {
            $output->writeln("No jdisc server selected. Using default server for import.");
            $l_res_jdisc_server = $l_jdisc->get_jdisc_servers();
            while ($l_row = $l_res_jdisc_server->get_row()) {
                if ($l_row['isys_jdisc_db__default_server'] > 0) {
                    $l_jdisc_server = $l_row['isys_jdisc_db__id'];
                    break;
                }
            }

            if (!$l_jdisc_server) {
                $output->writeln("No Server found. Please specify a default server or select an active server.");

                return Command::FAILURE;
            }
            $l_jdisc->switch_database($l_jdisc_server);
        } else {
            $l_jdisc_server = (int)$l_jdisc_server;

            if (!$l_jdisc->switch_database($l_jdisc_server)) {
                $output->writeln("Could not connect to the selected JDisc server. Please confirm if the credentials for this server are correct.");

                return Command::FAILURE;
            }
        }

        if (!is_numeric($l_profile)) {
            $output->writeln("Profile ID has to be from type Integer.");

            return Command::FAILURE;
        } else {
            $output->writeln('Checking Profile ID.');
            $l_profile = (int)$l_profile;

            if (!$l_jdisc->check_profile($l_profile)) {
                $output->writeln("Specified profile ID does not exist.", true);

                return Command::FAILURE;
            }

            if (is_numeric($l_jdisc_server)) {
                if (!$l_jdisc->check_profile_in_server($l_profile, $l_jdisc_server)) {
                    $output->writeln("Specified profile ID is not assigned to the selected JDisc server. Please use another profile or assign the profile to the selected JDisc server.");

                    return Command::FAILURE;
                }
            }
        }

        // Retrieving the mode.
        $l_mode = (int)($input->getOption('mode') ?: isys_import_handler_cmdb::C__UPDATE);
        // Groups are optional, profiles not.
        $l_group = (is_numeric($input->getOption('group')) ? $input->getOption('group') : null);
        $l_clear_options['clear_identifiers'] = false;
        $l_clear_options['clear_single_identifier'] = false;

        // Retrieving indicator if search index should be regenerated
        $l_regenerate_search_index = (bool)$input->getOption('regenerateSearchIndex');

        switch ($l_mode) {
            case 1:
                $l_mode = isys_import_handler_cmdb::C__APPEND;
                break;
            case 5:
                $l_clear_options['clear_identifiers'] = true;
                // no break
            case 3:
                $l_jdisc->set_clear_mode(isys_import_handler_cmdb::C__OVERWRITE);
                $l_mode = isys_import_handler_cmdb::C__UPDATE;
                break;
            case 4:
                $l_clear_options['clear_identifiers'] = true;
                $l_mode = isys_import_handler_cmdb::C__UPDATE;
                break;
            case 6: // @see ID-7392 option append new only
                $l_clear_options['clear_identifiers'] = false;
                $l_mode = isys_import_handler_cmdb::C__APPEND;
                break;
            case 2:
            default:
                $l_mode = isys_import_handler_cmdb::C__UPDATE;
                break;
        }

        // Prepare the import-array.
        $output->writeln('Begin to retrieve data and prepare the environment...');
        $l_jdisc->set_mode($l_mode)
            ->prepare_environment($l_profile);

        $l_is_jedi = $l_jdisc->is_jedi();

        // Getting the PDO.
        $output->writeln('Receiving the PDO instance... ');
        $l_pdo = null;
        try {
            $l_pdo = $l_jdisc->get_connection();
            $output->writeln('Success!', false);
        } catch (Exception $e) {
            $output->writeln('Failure: ' . $e->getMessage());
        }

        // Retrieve the result set for the objects to be imported.
        $output->writeln('Receiving the JDisc data... ');
        $l_obj_res = $l_jdisc->retrieve_object_result($l_group, $l_profile);

        if ($l_obj_res) {
            try {
                $l_total_objects = $l_pdo->num_rows($l_obj_res);
                $l_start_time = microtime(true);

                // Display the number of objects, that will be imported.
                $output->writeln('Found ' . $l_total_objects . ' objects! Collecting Data ...', true);

                // Create an instance of the CMDB import
                $l_import = new isys_import_handler_cmdb($this->log, $this->container->database, isys_cmdb_dao_jdisc::instance($this->container->database));
                $l_import->set_empty_fields_mode(isys_import_handler_cmdb::C__KEEP);

                // Decide if overlapping host addresses should be overwritten or not
                $l_import->set_overwrite_ip_conflicts($l_overwrite_host_addresses);
                $l_import->set_general_header('JDisc');
                $l_import->set_logbook_source(defined_or_default('C__LOGBOOK_SOURCE__JDISC'));

                // Matching from JDisc device id to i-doit object id:
                $l_jdisc_to_idoit = [];

                // Cached object identifiers:
                $l_object_ids = [];

                // Cached devices
                $l_arr_device_ids = [];

                if ($l_total_objects > 0) {
                    /**
                     * Disconnect the onAfterCategoryEntrySave event to not always reindex the object in every category
                     * This is extremely important!
                     *
                     * An Index is done for all objects at the end of the request, if enabled via parameter.
                     */
                    SearchIndexSignals::instance()
                        ->disconnectOnAfterCategoryEntrySave();

                    $l_not_defined_types = [];
                    $l_already_used = new isys_array();
                    $l_clear_options['clear_single_identifier'] = false;

                    // Clear all JDisc identifiers if mode has been selected
                    if ($l_clear_options['clear_identifiers'] === true) {
                        isys_jdisc_dao_matching::instance()
                            ->clear_identifiers(defined_or_default('C__CATG__IDENTIFIER_TYPE__JDISC'), 'deviceid-' . $l_jdisc_server, null);
                    }
                    $simpleDatabaseModel = isys_jdisc_dao_software::instance($this->container->get('database'))->isSimpleDatabaseModel();

                    // Retrieve devices
                    $l_device_arr = $l_jdisc->prepare_devices($l_obj_res, $l_clear_options);

                    // @see ID-7391 transform imported objects titles
                    $l_device_arr = $l_jdisc->transformJdiscObjectsTitles($l_profile, $l_device_arr);
                    // END @see ID-7391 transform imported objects titles

                    // see @ID-7392 import only new jdisc objects
                    if ($l_mode === 6) {
                        $l_device_arr_processed = [];
                        foreach ($l_device_arr as $device) {
                            if ($device['identifierObjId'] !== null) {
                                continue;
                            }
                            $l_device_arr_processed[] = $device;
                        }
                        $l_device_arr = $l_device_arr_processed;
                    }
                    $l_import_count = count($l_device_arr);
                    // END see @ID-7392 import only new jdisc objects

                    //while ($l_obj_row = $l_pdo->fetch_row_assoc($l_obj_res))
                    foreach ($l_device_arr as $l_obj_row) {
                        unset($l_prepared_data);

                        if (!isset($l_obj_row['idoit_obj_type']) || $l_obj_row['idoit_obj_type'] === null) {
                            if (!isset($l_not_defined_types[$l_obj_row['type_name']])) {
                                $output->writeln('JDisc type "' . $l_obj_row['type_name'] . '" is not properly defined in the profile. Skipping devices with JDisc type "' .
                                    $l_obj_row['type_name'] . '".');
                                $l_not_defined_types[$l_obj_row['type_name']] = true;
                            }
                            continue;
                        }

                        if (in_array($l_obj_row['deviceid'], $l_arr_device_ids)) {
                            continue;
                        }

                        $l_import_counter++;

                        $l_arr_device_ids[] = $l_obj_row['deviceid'];

                        $l_prepared_data = $l_jdisc->prepare_object_data($l_obj_row, $l_jdisc_to_idoit, $l_object_ids);

                        $output->writeln('Importing object "' . $l_obj_row['name'] . '": ' . $l_import_counter . '/' . $l_total_objects . '.', true);

                        if (!isset($l_prepared_data['object']) || !is_array($l_prepared_data['object'])) {
                            $l_prepared_data['object'] = [];
                        }
                        if (!isset($l_prepared_data['connections']) || !is_array($l_prepared_data['connections'])) {
                            $l_prepared_data['connections'] = [];
                        }
                        if (!isset($l_object_ids) || !is_array($l_object_ids)) {
                            $l_object_ids = [];
                        }

                        Context::instance()
                            ->setContextTechnical(Context::CONTEXT_IMPORT_XML)
                            ->setGroup(Context::CONTEXT_GROUP_IMPORT)
                            ->setContextCustomer(Context::CONTEXT_IMPORT_JDISC)
                            ->setImmutable(true);

                        // Prepare and import the data.
                        $l_import->reset()
                            ->set_scantime()
                            ->set_prepared_data($l_prepared_data['object'])
                            ->set_connection_info($l_prepared_data['connections'])
                            ->set_mode($l_mode)
                            ->set_object_created_by_others(true)
                            ->set_object_ids($l_object_ids)
                            ->set_logbook_entries(isys_jdisc_dao_devices::instance($this->container->database)
                                ->get_logbook_entries())
                            ->import();
                        $output->writeln('Done!', false);

                        // The last id is the prepared object:
                        $l_last_object_id = $l_import::get_stored_objectID();
                        $l_already_used[$l_last_object_id] = $l_obj_row['name'];

                        $l_jdisc_to_idoit[$l_obj_row['id']] = $l_last_object_id;
                        isys_jdisc_dao_devices::instance($this->container->database)
                            ->set_jdisc_to_idoit_objects($l_obj_row['id'], $l_last_object_id);
                        $l_object_ids[$l_last_object_id] = $l_last_object_id;

                        /* Update CMDB Status */
                        if (isset($l_prepared_data['object'][$l_last_object_id]['cmdb_status']) && $l_prepared_data['object'][$l_last_object_id]['cmdb_status'] > 0) {
                            isys_cmdb_dao_category_g_identifier::instance($this->container->database)
                                ->set_object_cmdb_status($l_last_object_id, $l_prepared_data['object'][$l_last_object_id]['cmdb_status']);
                        }
                    }
                    $l_import->set_overwrite_ip_conflicts(false);
                    unset($l_import);

                    Context::instance()
                        ->setImmutable(false)
                        ->setContextTechnical(Context::CONTEXT_DAO_UPDATE)
                        ->setGroup(Context::CONTEXT_GROUP_IMPORT)
                        ->setContextCustomer(Context::CONTEXT_IMPORT_JDISC)
                        ->setImmutable(true);

                    $output->writeln('Starting the final step of the import: Referencing the data.', true);

                    $output->writeln('Step 1: Updating cluster assignments.', true);
                    isys_jdisc_dao_cluster::instance($this->container->database)
                        ->assign_clusters($l_jdisc_to_idoit, isys_jdisc_dao_network::instance($this->container->database)
                            ->get_vrrp_addresses());

                    $output->writeln('Step 2: Updating cluster members.', true);
                    isys_jdisc_dao_cluster::instance($this->container->database)
                        ->update_cluster_members($l_jdisc_to_idoit);

                    $output->writeln('Step 3: Creating blade connections.', true);
                    isys_jdisc_dao_devices::instance($this->container->database)
                        ->create_blade_connections($l_jdisc_to_idoit);

                    $output->writeln('Step 4: Creating module connections.', true);
                    isys_jdisc_dao_devices::instance($this->container->database)
                        ->create_module_connections($l_jdisc_to_idoit, isys_jdisc_dao_network::instance($this->container->database)
                            ->get_import_type_interfaces());

                    $output->writeln('Step 5: Creating software licenses.', true);

                    if (!$l_is_jedi && $l_jdisc->check_import_software_licences()) {
                        isys_jdisc_dao_software::instance($this->container->database)
                            ->create_software_licenses();
                    }

                    // To save memory leak we iterate through all imported objects
                    $l_counter = 1;
                    // It could be possible that referenced objects have been added to the imported objects which have to be updated
                    $newCount = count($l_jdisc_to_idoit);
                    foreach ($l_jdisc_to_idoit as $l_device_id => $l_object_id) {
                        $output->writeln('Processing Object "' . $l_already_used[$l_object_id] . '" with Object-ID #' . $l_object_id . '. (' . $l_counter++ . '/' .
                            $newCount . ')');

                        $l_cache_network = isys_jdisc_dao_network::instance($this->container->database)
                            ->load_cache($l_object_id);
                        $l_cache_software = isys_jdisc_dao_software::instance($this->container->database)
                            ->load_cache($l_object_id);
                        $cacheEndpointConnectionsLoaded = isys_jdisc_dao_category_connection_endpoint::instance($this->container->database)
                            ->load_cache($l_object_id);

                        if ($simpleDatabaseModel) {
                            $l_cache_database_loaded = isys_jdisc_dao_software_database::instance($this->container->get('database'))
                                ->load_cache($l_object_id);
                        }

                        if ($l_cache_network || $l_cache_software || $l_cache_database_loaded || $cacheEndpointConnectionsLoaded) {
                            if (!$l_is_jedi && $l_jdisc->check_import_software_licences()) {
                                // Create software license
                                isys_jdisc_dao_software::instance($this->container->database)
                                    ->handle_software_licenses($l_object_id, $l_device_id);
                            }

                            // Create net listeners
                            isys_jdisc_dao_software::instance($this->container->database)
                                ->create_net_listener_connections($l_object_id, $l_device_id);

                            if ($l_jdisc->isImportConnectionEndpoint()) {
                                isys_jdisc_dao_category_connection_endpoint::instance($this->container->database)
                                    ->createEndpointConnections();
                            } else {

                                // Create port connections
                                isys_jdisc_dao_network::instance($this->container->database)
                                    ->create_port_connections()// Update ip to port assignments
                                    ->update_ip_port_assignments($l_object_id)// Create port map
                                    ->create_port_map($l_object_id);
                            }

                            if ($simpleDatabaseModel) {
                                // Create database model connections
                                isys_jdisc_dao_software_database::instance($this->container->get('database'))
                                    ->createDatabaseConnections($l_object_id, $l_device_id);
                            }

                            // Assign interfaces to the ports
                            isys_jdisc_dao_network::instance($this->container->database)
                                ->create_network_interface_connections($l_object_id)// This function takes more time than the others
                                ->update_vlan_assignments($l_object_id, $l_device_id);
                        }

                        $output->writeln('Done!', true);
                    }

                    // Recover identifiers
                    if (defined('C__CATG__IDENTIFIER_TYPE__JDISC')) {
                        isys_cmdb_dao_category_g_identifier::instance($this->container->database)
                            ->recover_identifiers(C__CATG__IDENTIFIER_TYPE__JDISC, 'deviceid-' . $l_jdisc_server);

                        if ($l_clear_options['clear_identifiers'] === true) {
                            isys_cmdb_dao_category_g_identifier::instance($this->container->database)
                                ->deleteArchivedEntries(C__CATG__IDENTIFIER_TYPE__JDISC, 'deviceid-' . $l_jdisc_server);
                        }
                    }

                    // Remove temporary table
                    isys_jdisc_dao_network::instance($this->container->database)
                        ->drop_cache_table();

                    $l_affected_categories = $l_jdisc->get_cached_profile()['categories'];
                    if (is_array($l_affected_categories) && count($l_affected_categories)) {
                        $startTimeIndexCreation = microtime(true);

                        /* Adding additional categories*/
                        $l_categories = array_keys($l_affected_categories);
                        if (defined('C__CATG__NETWORK_LOG_PORT')) {
                            $l_categories[] = C__CATG__NETWORK_LOG_PORT;
                        }
                        if (defined('C__CATG__JDISC_CA')) {
                            $l_categories[] = C__CATG__JDISC_CA;
                        }

                        // Regenerate Search index
                        if ($l_regenerate_search_index) {
                            $output->writeln('Regenerating search index..');
                            SearchIndexSignals::instance()->setOutput($output);
                            SearchIndexSignals::instance()
                                ->onPostImport($l_start_time, $l_categories, filter_defined_constants([
                                    'C__CATS__SERVICE',
                                    'C__CATS__APPLICATION',
                                    'C__CATS__DATABASE_SCHEMA',
                                    'C__CATS__DBMS',
                                    'C__CATS__DATABASE_INSTANCE',
                                    'C__CATS__ACCESS_POINT',
                                    'C__CATS__NET'
                                ]));

                            $output->writeln("Index creation took " . number_format(microtime(true) - $startTimeIndexCreation, 2) . " secs.");
                        }
                    }

                    $output->writeln('Complete process took: ' . isys_glob_seconds_to_human_readable((int)(microtime(true) - $l_start_time)));
                    $output->writeln('Memory peak usage: ' . number_format(memory_get_peak_usage() / 1024 / 1024, 2, '.', '') . ' MB');
                } else {
                    $output->writeln('No objects found, sorry.', true);
                }

                $output->writeln('Import finished.', true);
                $output->writeln($l_import_counter . ' objects affected.', true);

                $this->log->info('Import finished.');
            } catch (Exception $e) {
                $l_error_msg = $e->getMessage() . '. File: ' . $e->getFile() . ' Line: ' . $e->getLine();
                $output->writeln('Import failed with message: ');
                $output->writeln($l_error_msg);
                $this->log->error('Import failed with message: ' . $l_error_msg);
                return Command::FAILURE;
            }
        } else {
            $output->writeln('Import failed with message: ');
            $output->writeln('"There are no object types defined in the JDisc profile or are deactivated in the object type configuration."');
            $this->log->error('Import failed with message: "There are no object types defined in the JDisc profile or are deactivated in the object type configuration."');
            return Command::FAILURE;
        }

        try {
            if ($l_jdisc->importCloudSubscriptions) {
                $database = isys_application::instance()->database;
                $l_cloudSubscriptionsDao = isys_cmdb_dao_category_g_cloud_subscriptions::factory($database);
                if ($l_cloudSubscriptionsDao->importSubscriptions($l_jdisc)) {
                    $output->writeln('Cloud Subscriptions import succeeded.', true);
                } else {
                    $output->writeln('Cloud Subscriptions were not imported.', true);
                }
            }
        } catch (Exception $e) {
            $l_error_msg = $e->getMessage() . '. File: ' . $e->getFile() . ' Line: ' . $e->getLine();
            $output->writeln('Cloud Subscriptions Import failed with message: ');
            $output->writeln($l_error_msg);
            $this->log->error('Cloud Subscriptions Import failed with message: ' . $l_error_msg);
            return Command::FAILURE;
        }

        $this->log->info($l_import_counter . ' objects affected.');
        return Command::SUCCESS;
    }

    /**
     * Retrieve Command InputDefinition
     *
     * @return InputDefinition
     */
    public function getCommandDefinition()
    {
        $definition = new InputDefinition();

        $definition->addOption(new InputOption('profile', 'r', InputOption::VALUE_REQUIRED, 'Jdisc Profile ID'));

        $definition->addOption(new InputOption('group', 'g', InputOption::VALUE_OPTIONAL, 'Group ID'));

        $definition->addOption(new InputOption(
            'mode',
            'x',
            InputOption::VALUE_REQUIRED,
            "Possible modes are:\n" . "1: Append - The import will only create new objects.\n" . "2: Update - The import will try to update already existing objects.\n" .
            "3: Overwrite - The import behaves like the update mode but clears all list categories of the existing object.\n" .
            "4: Update (newly discovered) - The import clears all existing identification keys before the Update mode is triggered.\n" .
            "5: Overwrite (newly discovered) - The import clears all existing identification keys before the Overwrite mode is triggered.\n" .
            "6: Only create newly scanned devices - The import creates only newly scanned jdisc devices, existing ones are skipped.\n"
        ));

        $definition->addOption(new InputOption('server', 's', InputOption::VALUE_REQUIRED, 'Jdisc Server ID'));

        $definition->addOption(new InputOption('overwriteHost', 'o', InputOption::VALUE_NONE, 'Indicator for overwriting overlapped host addresses'));

        $definition->addOption(new InputOption('detailedLogging', 'l', InputOption::VALUE_NONE));

        $definition->addOption(new InputOption('regenerateSearchIndex', 'b', InputOption::VALUE_NONE));

        $definition->addOption(new InputOption('listProfiles', null, InputOption::VALUE_NONE, 'List all available profiles'));

        return $definition;
    }

    /**
     * Get description for command
     *
     * @return string
     */
    public function getCommandDescription()
    {
        return 'Imports data from a JDisc server (SQL server access is defined in the GUI)';
    }

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
     * Returns an array of command usages
     *
     * @return string[]
     */
    public function getCommandUsages()
    {
        return [];
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

    private function listProfiles($jDiscProfiles, OutputInterface $output)
    {
        $output->writeln('<info>JDisc Profiles:</info>');
        foreach ($jDiscProfiles as $profile) {
            $output->writeln('  ' . $profile['id'] . '. ' . $profile['title']);
        }
    }
}

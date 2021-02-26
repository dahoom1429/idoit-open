<?php

namespace idoit\Console\Command\Import;

use Exception;
use idoit\Component\Helper\Ip;
use idoit\Console\Command\Import\Ocs\Hardware;
use idoit\Console\Command\AbstractCommand;
use idoit\Console\Command\Import\Ocs\OcsMatcher;
use idoit\Console\Command\Import\Ocs\Snmp;
use idoit\Context\Context;
use idoit\Module\Cmdb\Search\Index\Signals;
use isys_application;
use isys_cmdb_dao_category_g_cpu;
use isys_cmdb_dao_category_g_drive;
use isys_cmdb_dao_category_g_global;
use isys_cmdb_dao_category_g_graphic;
use isys_cmdb_dao_category_g_guest_systems;
use isys_cmdb_dao_category_g_ip;
use isys_cmdb_dao_category_g_memory;
use isys_cmdb_dao_category_g_model;
use isys_cmdb_dao_category_g_network_port;
use isys_cmdb_dao_category_g_power_consumer;
use isys_cmdb_dao_category_g_relation;
use isys_cmdb_dao_category_g_sound;
use isys_cmdb_dao_category_g_stack_member;
use isys_cmdb_dao_category_g_stor;
use isys_cmdb_dao_category_g_ui;
use isys_cmdb_dao_category_g_virtual_machine;
use isys_cmdb_dao_category_s_net;
use isys_component_signalcollection;
use isys_format_json;
use isys_log;
use isys_module_logbook;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\StreamOutput;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use isys_tenantsettings;
use isys_component_dao_ocs;
use isys_event_manager;
use isys_cmdb_dao;
use isys_component_database;
use isys_helper_crypt;
use idoit\Console\Command\IsysLogWrapper;
use idoit\Module\Cmdb\Model\Summary\SummarySignals;

class ImportOcsCommand extends AbstractCommand
{
    const NAME = 'import-ocs';

    /**
     * @var \idoit\Module\Cmdb\Model\Matcher\Ci\CiMatcher
     */
    private $ciMatcher = [];

    /**
     * @var []
     */
    private $ocsDatabaseSettings = null;

    /**
     * @var array
     */
    private $categories = [];

    /**
     * @var array
     */
    private $m_objtypes = [];

    /**
     * @var IsysLogWrapper
     */
    private $m_log = null;

    /**
     * @var array
     */
    private $m_objtype_blacklist = [];

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
        return 'Imports data from an OCS inventory NG server (SQL server access is defined in the GUI)';
    }

    /**
     * Retrieve Command InputDefinition
     *
     * @return InputDefinition
     */
    public function getCommandDefinition()
    {
        $definition = new InputDefinition();

        // Option which determines if all ips and ports of an updated object should be overwritten or not
        $definition->addOption(new InputOption(
            'ipPortOverwrite',
            null,
            InputOption::VALUE_REQUIRED,
            'Determines if hostaddresses and ports should be deleted first for each imported device. 1 = Active; 0 = Inactive (Default)',
            0
        ));

        // Option which determines if all cats selected for import should be purged before new import
        $definition->addOption(new InputOption(
            'allCatsOverwrite',
            null,
            InputOption::VALUE_REQUIRED,
            'Determines if all cats selected for import should be purged before new import. 1 = Active; 0 = Inactive (Default)',
            0
        ));

        // Option for the default object type which will be used if no default object type has been defined
        $definition->addOption(new InputOption(
            'databaseSchema',
            null,
            InputOption::VALUE_REQUIRED,
            'Import from selected database schema. If not set default database schema will be used in the configuration.'
        ));

        // Option for the default object type which will be used if no default object type has been defined
        $definition->addOption(new InputOption('objectType', null, InputOption::VALUE_REQUIRED, 'Default objecttype constant from the object type configuration.'));

        $definition->addOption(new InputOption('recalculateObjectType', null, InputOption::VALUE_REQUIRED, 'Recalculation of object types for already existing objects based on tags.', false));

        // Option for a source file which contains hostnames which will be imported/updated.
        $definition->addOption(new InputOption('file', null, InputOption::VALUE_REQUIRED, 'File which contains a list of hostnames.'));

        // Option for a comma separated list of hostnames which will be imported/updated.
        $definition->addOption(new InputOption('hosts', null, InputOption::VALUE_REQUIRED, 'Comma separated list of Hostnames which will be imported.'));

        // Option for the switch if snmp devices should be imported or not
        $definition->addOption(new InputOption('snmpDevices', null, InputOption::VALUE_REQUIRED, 'Switch to snmp device import.'));

        // Option for a comma separated list of categories which will be used for the import
        $definition->addOption(new InputOption(
            'categories',
            null,
            InputOption::VALUE_REQUIRED,
            'Categories which will be imported. Possible Values: drive,ui,sound,application,memory,model,graphic,net,stor,operating_system,cpu'
        ));

        // Option which determines the logging level
        $definition->addOption(new InputOption('logging', null, InputOption::VALUE_REQUIRED, 'Activate file logging. 1 = Normal Log; 2 = Debug Log', 0));

        // Option which lists all possible object types
        $definition->addOption(new InputOption('listObjectTypes', null, InputOption::VALUE_NONE, 'Lists all possible object types'));

        // Option which lists all possible categories
        $definition->addOption(new InputOption('listCategories', null, InputOption::VALUE_NONE, 'Lists all possible categories'));

        $definition->addOption(new InputOption('usage', null, InputOption::VALUE_NONE));

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
        return [];
    }

    /**
     * @param OutputInterface $output
     * @param isys_cmdb_dao   $dao
     *
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    private function listObjectTypes(OutputInterface $output, isys_cmdb_dao $dao)
    {
        $output->writeln("<info>Object-Types:</info>");
        $result = $dao->get_types();
        while ($objectTypeData = $result->get_row()) {
            $output->writeln('  ' . $objectTypeData['isys_obj_type__id'] . ': ' . $objectTypeData['isys_obj_type__const']);
        }
    }

    /**
     * @param OutputInterface $output
     * @param isys_cmdb_dao   $dao
     *
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    private function listCategories(OutputInterface $output, isys_cmdb_dao $dao)
    {
        $output->writeln("<info>Allowed categories:</info>");
        foreach ($this->categories as $categoryId => $unused) {
            if ($categoryId == 'operating_system') {
                $output->writeln('  operating_system: ' . isys_application::instance()->container->get('language')
                        ->get('LC__CATG__OPERATING_SYSTEM'));
                continue;
            }
            $categoryTitle = $dao->get_catg_name_by_id_as_string($categoryId);
            $output->writeln('  ' . $unused . ': ' . isys_application::instance()->container->get('language')
                    ->get($categoryTitle));
        }
    }

    /**
     * Prints out the usage of the import handler
     *
     * @param OutputInterface $output
     */
    private function usage(OutputInterface $output, $level = null)
    {
        switch ($level) {
            case 2:
                // no hosts selected
                $output->writeln("Please select one or more hosts to be imported.");
                break;
            case 3:
                $output->writeln("<error>Could not connect to OCS Database.</error>");
                $output->writeln("<info>Please check your OCS database configuration.</info>");
                break;
            case 1:
                $output->writeln("<error>Missing database!</error>");
                $output->writeln("<info>Please set a default OCS database or use the option --databaseSchema.</info>");
                // no break
            default:

                $style = new OutputFormatterStyle('yellow', 'black', []);
                $dao = new isys_cmdb_dao($this->container->database);
                $output->getFormatter()
                    ->setStyle('yellow', $style);

                $output->writeln("<yellow>Usage</yellow>:");
                $output->writeln(" import-ocs [--snmpDevices] [--databaseSchema] [--objectType] [--file|--hosts] [--categories] [--logging]");
                $output->writeln('');
                $output->writeln("Example for importing objects from an OCS inventory database with specified hosts, categories and activated logging lvl 2:");
                $output->writeln("import-ocs --databaseSchema=ocsweb --hosts=device1,device2 --logging=2 --objectType=C__OBJTYPE__CLIENT --categories=model,cpu,ui");
                $output->writeln('');
                $output->writeln("  --databaseSchema: Retrieves the OCS configuration from i-doit via schema name which will be used as import source.");
                $output->writeln("  --hosts: Comma separated list of hosts which will be searched and imported from the OCS database.");
                $output->writeln("  --logging: Specifies the log level of the import.");
                $output->writeln("  --objectType: All newly imported devices which could not be automatically identified are being created with the specified object type. Default from the configuration will be used if not specified.");
                $output->writeln("  --categories: Comma separated list of categories which will be imported.");
                $output->writeln("  --allCatsOverwrite=0 or 1: Determines, whether to purge selected cats for complete renewal during import");
                $output->writeln("  --recalculateObjectType=0 or 1: Determines, whether to use calculated object type for already existing objects");
                $output->writeln('');

                // Output all possible object types
                $this->listObjectTypes($output, $dao);

                $output->writeln('');

                // Output all possible categories
                $this->listCategories($output, $dao);

                break;
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $style = new OutputFormatterStyle('red', 'black', []);
        $output->getFormatter()
            ->setStyle('red', $style);

        $output->writeln("------------------------------------------------");
        $output->writeln("i-do<red>it</red> OCS Import");
        $output->writeln("------------------------------------------------");

        $output->writeln("OCS-Handler initialized (" . date("Y-m-d H:i:s") . ")");

        $this->categories = ["operating_system" => "operating_system"] + filter_array_by_keys_of_defined_constants([
                'C__CATG__CPU'                 => 'cpu',
                'C__CATG__MEMORY'              => 'memory',
                'C__CATG__APPLICATION'         => 'application',
                'C__CATG__NETWORK'             => 'net',
                'C__CATG__STORAGE'             => 'stor',
                'C__CATG__DRIVE'               => 'drive',
                'C__CATG__GRAPHIC'             => 'graphic',
                'C__CATG__SOUND'               => 'sound',
                'C__CATG__MODEL'               => 'model',
                'C__CATG__UNIVERSAL_INTERFACE' => 'ui'
            ]);

        if ($input->getOption('usage')) {
            $this->usage($output);

            return Command::SUCCESS;
        }

        /* Process import */
        try {
            $ocsDatabaseId = $this->getOcsDatabaseId($input);

            if (!$ocsDatabaseId) {
                $this->usage($output, 1);

                return Command::SUCCESS;
            }

            $this->ocsDatabaseSettings = (new isys_component_dao_ocs($this->container->database))->getOCSDB($ocsDatabaseId);

            if (!is_array($this->ocsDatabaseSettings)) {
                $this->usage($output, 3);

                return Command::SUCCESS;
            }

            $this->process($input, $output);

            return Command::SUCCESS;
        } catch (Exception $e) {
            $output->writeln("<error>Error-Message: " . $e->getMessage() . "</error>");
        }

        return Command::FAILURE;
    }

    public function process(InputInterface $input, OutputInterface $output)
    {
        Context::instance()
            ->setContextTechnical(Context::CONTEXT_IMPORT_OCS)
            ->setGroup(Context::CONTEXT_GROUP_IMPORT)
            ->setContextCustomer(Context::CONTEXT_IMPORT_OCS);

        /**
         * Import start time, used to identify the updated objects within this import
         */
        $startTime = microtime(true);

        /**
         * Disconnect the onAfterCategoryEntrySave event to not always reindex the object in every category
         * This is extremely important!
         *
         * An Index is done for all objects at the end of the request, if enabled via checkbox.
         */
        Signals::instance()
            ->disconnectOnAfterCategoryEntrySave();

        $l_config_obj_types = filter_array_by_keys_of_defined_constants([
            'C__OBJTYPE__SERVER'  => (isys_tenantsettings::get('ocs.prefix.server') ?: null),
            'C__OBJTYPE__CLIENT'  => (isys_tenantsettings::get('ocs.prefix.client') ?: null),
            'C__OBJTYPE__SWITCH'  => (isys_tenantsettings::get('ocs.prefix.switch') ?: null),
            'C__OBJTYPE__ROUTER'  => (isys_tenantsettings::get('ocs.prefix.router') ?: null),
            'C__OBJTYPE__PRINTER' => (isys_tenantsettings::get('ocs.prefix.printer') ?: null),
        ]);

        $language = isys_application::instance()->container->get('language');
        $emptyValue = isys_tenantsettings::get('gui.empty_value', '-');
        $l_regApp = isys_tenantsettings::get('ocs.application');
        $l_regAppAssign = isys_tenantsettings::get('ocs.application.assignment');
        $l_logb_active = isys_tenantsettings::get('ocs.logbook.active');
        $l_cmdb_status_id = isys_tenantsettings::get('ocs.default_status', defined_or_default('C__CMDB_STATUS__IN_OPERATION'));

        $l_daoCMDB = new isys_cmdb_dao($this->container->database);
        $l_dao_logb = new isys_module_logbook();

        if (empty($this->ocsDatabaseSettings["isys_ocs_db__host"])) {
            $output->writeln("<error>You have to configure the OCS connector first</error>");

            return;
        }

        $l_ocsdb = isys_component_database::get_database(
            "mysqli",
            $this->ocsDatabaseSettings["isys_ocs_db__host"],
            $this->ocsDatabaseSettings["isys_ocs_db__port"],
            $this->ocsDatabaseSettings["isys_ocs_db__user"],
            isys_helper_crypt::decrypt($this->ocsDatabaseSettings["isys_ocs_db__pass"]),
            $this->ocsDatabaseSettings["isys_ocs_db__schema"]
        );

        $l_daoOCS = new isys_component_dao_ocs($l_ocsdb);

        $l_gui = false;
        $l_overwrite_ip_port = $recalculateObjectType = false;
        $l_categories = [];
        $l_log_lvl = 0;
        $l_objtype_arr = $l_objtype_snmp_arr = $l_snmpIDs = $l_hardwareIDs = [];

        if (!empty($_SERVER['HTTP_HOST'])) {
            $l_cached = $this->categories;
            unset($this->categories);
            foreach ($l_cached as $l_key => $l_val) {
                $this->categories[] = $l_val;
            }
            // Import one ocs object
            if (isys_glob_get_param("hardwareID")) {
                $l_snmp = html_entity_decode(isys_glob_get_param('snmp'));
                if ($l_snmp && $l_snmp !== 'false') {
                    $l_snmpIDs[] = html_entity_decode(isys_glob_get_param("hardwareID"));
                } else {
                    $l_hardwareIDs[] = html_entity_decode(isys_glob_get_param("hardwareID"));
                }

                $l_objType = html_entity_decode(isys_glob_get_param("objTypeID"));
            } else {
                $l_hardwareIDs = html_entity_decode(isys_glob_get_param("id"));
                $l_snmpIDs = html_entity_decode(isys_glob_get_param("id_snmp"));
                $l_objtype_arr = html_entity_decode(isys_glob_get_param("objtypes"));
                $l_objtype_snmp_arr = html_entity_decode(isys_glob_get_param("objtypes_snmp"));

                $l_objType = isys_tenantsettings::get('ocs.default.objtype');
            }

            // Default Object type as string
            $l_default_objtype = $language->get($l_daoCMDB->get_objtype_name_by_id_as_string($l_objType));
            $l_log_lvl = html_entity_decode(isys_glob_get_param("ocs_logging"));

            $l_categories = html_entity_decode(isys_glob_get_param("category"));

            if (!is_array($l_objtype_snmp_arr) && isys_format_json::is_json($l_objtype_snmp_arr)) {
                $l_objtype_snmp_arr = isys_format_json::decode($l_objtype_snmp_arr);
            }

            if (!is_array($l_objtype_arr) && isys_format_json::is_json($l_objtype_arr)) {
                $l_objtype_arr = isys_format_json::decode($l_objtype_arr);
            }

            if (isys_format_json::is_json($l_categories)) {
                $l_categories = isys_format_json::decode($l_categories);
            }

            if (!is_array($l_hardwareIDs) && isys_format_json::is_json($l_hardwareIDs)) {
                $l_hardwareIDs = isys_format_json::decode($l_hardwareIDs);
            }

            if (!is_array($l_snmpIDs) && isys_format_json::is_json($l_snmpIDs)) {
                $l_snmpIDs = isys_format_json::decode($l_snmpIDs);
            }

            // @deprecated
            $l_overwrite_ip_port = (bool)html_entity_decode(isys_glob_get_param("overwrite_ip_port"));
            $l_overwrite_all_cats = (bool)html_entity_decode(isys_glob_get_param("overwrite_all_cats"));

            $l_gui = true;
        } else {
            $l_file = $input->getOption('file') ?: null;
            $l_hosts = $input->getOption("hosts") ?: null;
            $l_import_categories = $input->getOption('categories') ?: null;
            $l_snmp = (bool)$input->getOption("snmpDevices");
            $l_log_lvl = $input->getOption('logging') ?: 0;
            $l_standardObjType = $input->getOption('objectType') ?: null;
            $l_overwrite_ip_port = (bool)$input->getOption('ipPortOverwrite');
            $l_overwrite_all_cats = (bool)$input->getOption('allCatsOverwrite');
            $l_hardware = true;
            $l_arHosts = $l_temp = [];
            $recalculateObjectType = (bool)$input->getOption('recalculateObjectType') ?: false;

            $listObjecttypes = $input->getOption('listObjectTypes');
            $listCategories = $input->getOption('listCategories');

            if ($listObjecttypes) {
                $this->listObjectTypes($output, $l_daoCMDB);

                return true;
            }

            if ($listCategories) {
                $this->listCategories($output, $l_daoCMDB);

                return true;
            }

            if ($l_file !== null && $l_hosts !== null) {
                $output->writeln('<error>Only one source can be chosen.</error>');

                return false;
            }

            if ($l_file !== null) {
                if (!file_exists($l_file)) {
                    $output->writeln('<error>Input file ' . $l_file . '</error> not found!!');

                    return false;
                }

                $l_arHosts = explode("\n", file_get_contents($l_file));
            } elseif ($l_hosts !== null) {
                $l_arHosts = explode(',', $l_hosts);
            }

            if (count($l_arHosts) > 0) {
                foreach ($l_arHosts as $l_hostname) {
                    $l_hostname = trim($l_hostname);
                    if ($l_hostname == "") {
                        continue;
                    }
                    $l_temp[] = $l_hostname;
                }

                if ($l_hardware) {
                    $l_resHW = $l_daoOCS->getHardwareIDs($l_temp);

                    while ($l_row = $l_resHW->get_row()) {
                        $l_hardwareIDs[] = $l_row["ID"];
                    }
                }
                if ($l_snmp) {
                    $l_resHW = $l_daoOCS->getHardwareSnmpIDs($l_temp);

                    while ($l_row = $l_resHW->get_row()) {
                        $l_snmpIDs[] = $l_row["ID"];
                    }
                }
            } elseif ($l_file === null && $l_hosts === null) {
                // Import all Devices from OCS
                if ($l_hardware) {
                    $l_res = $l_daoOCS->getHardware();

                    while ($l_row = $l_res->get_row()) {
                        $l_hardwareIDs[] = $l_row["ID"];
                    }
                }
                if ($l_snmp) {
                    $l_resHW = $l_daoOCS->getHardwareSnmp();

                    $l_already_set = [];
                    while ($l_row = $l_resHW->get_row()) {
                        if (isset($l_already_set[$l_row['NAME']])) {
                            continue;
                        }

                        $l_already_set[$l_row['NAME']] = true;
                        $l_snmpIDs[] = $l_row["ID"];
                    }
                }
            } else {
                // No objects found in the OCS database
                $output->writeln('<info>No objects found.</info>');

                return false;
            }

            if ($l_import_categories) {
                $l_import_categories = explode(',', $l_import_categories);
                $l_cached_arr = array_flip($this->categories);

                foreach ($l_cached_arr as $l_key => $l_val) {
                    if (in_array($l_key, $l_import_categories)) {
                        $l_categories[] = $l_val;
                    }
                }
            } else {
                $l_categories = array_flip($this->categories);
            }

            /* Is an standard object type is setted */
            if ($l_standardObjType !== null) {
                if (is_numeric($l_standardObjType)) {
                    $l_objType = $l_standardObjType;
                } elseif (is_string($l_standardObjType)) {

                    /* Retrieve object type by constant */
                    $l_objType = $l_daoCMDB->get_object_type(null, $l_standardObjType, null);

                    /* Set $l_objType */
                    if (is_array($l_objType)) {
                        $l_objType = $l_objType['isys_obj_type__id'];
                    }
                }
            } else {
                /* Retrieve standard object type by i-doit registry */
                $l_objType = isys_tenantsettings::get('ocs.default.objtype');
            }

            $l_default_objtype = $language->get($l_daoCMDB->get_objtype_name_by_id_as_string($l_objType));
        }

        $l_logging = true;
        $l_loglevel = null;

        switch ($l_log_lvl) {
            case 2:
                $l_loglevel = isys_log::C__ALL;
                break;
            case 1:
                $l_loglevel = isys_log::C__ALL & ~isys_log::C__DEBUG;
                break;
            default:
                $l_logging = false;
                break;
        }

        if (is_countable($l_hardwareIDs) && count($l_hardwareIDs) == 0 && is_countable($l_snmpIDs) && count($l_snmpIDs) == 0) {
            $this->usage($output, 2);

            return false;
        }

        $this->m_log = IsysLogWrapper::instance();
        $logbookDao = new isys_module_logbook();

        // Ocs Matcher
        $matcher = new OcsMatcher();
        $matcher->setContainer($this->container);
        $matcher->setOutput($output);
        $matcher->setLogger($this->m_log);

        // Hardware Devices which has been discovered via OCS Agent
        $ocsHardware = new Hardware();
        $ocsHardware->setContainer($this->container);
        $ocsHardware->setMatcher($matcher);
        $ocsHardware->setLogbook($logbookDao);
        $ocsHardware->setLogger($this->m_log);

        // Devices which has been discovered via SNMP
        $ocsSnmp = new Snmp();
        $ocsSnmp->setContainer($this->container);
        $ocsSnmp->setMatcher($matcher);
        $ocsSnmp->setLogbook($logbookDao);
        $ocsSnmp->setLogger($this->m_log);

        if ($l_logging) {
            $this->m_log->setOutput(new StreamOutput(fopen('log/import_ocs_' . date('Y-m-d_H_i_s') . '.log', 'a')));
            $this->m_log->set_log_level($l_loglevel)
                ->set_verbose_level(isys_log::C__FATAL | isys_log::C__ERROR | isys_log::C__WARNING | isys_log::C__NOTICE);
        } else {
            $this->m_log->setOutput(new NullOutput());
        }

        /**
         * Typehinting:
         *
         * @var  $l_daoGl              isys_cmdb_dao_category_g_global
         * @var  $l_daoNet_s           isys_cmdb_dao_category_s_net
         * @var  $l_daoPort            isys_cmdb_dao_category_g_network_port
         * @var  $l_daoIP              isys_cmdb_dao_category_g_ip
         */
        $l_daoGl = isys_cmdb_dao_category_g_global::instance($this->container->database);
        $l_daoNet_s = isys_cmdb_dao_category_s_net::instance($this->container->database);

        $l_add_cpu = false;
        $l_add_model = false;
        $l_add_memory = false;
        $l_add_application = false;
        $l_add_graphic = false;
        $l_add_sound = false;
        $l_add_storage = false;
        $l_add_drive = false;
        $l_add_net = false;
        $l_add_ui = false;
        $l_add_os = false;
        $l_add_virtual_machine = false;
        $this->container->database->begin();

        $l_category_selection_as_string = '';
        if (array_search('operating_system', $l_categories) !== false) {
            $l_add_os = true;
            $l_category_selection_as_string .= 'Operating System, ';
        }

        if (defined('C__CATG__CPU') && (array_search(C__CATG__CPU, $l_categories) !== false || (array_key_exists(C__CATG__CPU, $l_categories) !== false && !$l_gui))) {
            $l_add_cpu = true;
            $l_daoCPU = isys_cmdb_dao_category_g_cpu::instance($this->container->database);
            $l_category_selection_as_string .= 'CPU, ';
            $l_categories[] = 'C__CATG__CPU';
        }
        if (defined('C__CATG__MEMORY') && (array_search(C__CATG__MEMORY, $l_categories) !== false || (array_key_exists(C__CATG__MEMORY, $l_categories) !== false && !$l_gui))) {
            $l_add_memory = true;
            $l_daoMemory = isys_cmdb_dao_category_g_memory::instance($this->container->database);
            $l_category_selection_as_string .= 'Memory, ';
            $l_categories[] = 'C__CATG__MEMORY';
        }

        if (defined('C__CATG__APPLICATION') && (array_search(C__CATG__APPLICATION, $l_categories) !== false || (array_key_exists(C__CATG__APPLICATION, $l_categories) !== false && !$l_gui))) {
            $l_add_application = true;
            $l_relation_dao = isys_cmdb_dao_category_g_relation::instance($this->container->database);
            $l_relation_data = $l_relation_dao->get_relation_type(defined_or_default('C__RELATION_TYPE__SOFTWARE'), null, true);
            $l_category_selection_as_string .= 'Software assignment, ';
            $l_categories[] = 'C__CATG__APPLICATION';
        }

        if (defined('C__CATG__NETWORK') && (array_search(C__CATG__NETWORK, $l_categories) !== false || (array_key_exists(C__CATG__NETWORK, $l_categories) !== false && !$l_gui))) {
            $l_add_net = true;
            $l_dao_power_consumer = isys_cmdb_dao_category_g_power_consumer::instance($this->container->database);
            $l_category_selection_as_string .= 'Network, ';
            $l_categories[] = 'C__CATG__NETWORK';
        }

        if (defined('C__CATG__STORAGE') && (array_search(C__CATG__STORAGE, $l_categories) !== false || (array_key_exists(C__CATG__STORAGE, $l_categories) !== false && !$l_gui))) {
            $l_add_storage = true;
            $l_daoStor = isys_cmdb_dao_category_g_stor::instance($this->container->database);
            $l_category_selection_as_string .= 'Devices, ';
            $l_categories[] = 'C__CATG__STORAGE';
        }

        if (defined('C__CATG__GRAPHIC') && (array_search(C__CATG__GRAPHIC, $l_categories) !== false || (array_key_exists(C__CATG__GRAPHIC, $l_categories) !== false && !$l_gui))) {
            $l_add_graphic = true;
            $l_dao_graphic = isys_cmdb_dao_category_g_graphic::instance($this->container->database);
            $l_category_selection_as_string .= 'Graphic card, ';
            $l_categories[] = 'C__CATG__GRAPHIC';
        }

        if (defined('C__CATG__SOUND') && (array_search(C__CATG__SOUND, $l_categories) !== false || (array_key_exists(C__CATG__SOUND, $l_categories) !== false && !$l_gui))) {
            $l_add_sound = true;
            $l_dao_sound = isys_cmdb_dao_category_g_sound::instance($this->container->database);
            $l_category_selection_as_string .= 'Sound card, ';
            $l_categories[] = 'C__CATG__SOUND';
        }

        if (defined('C__CATG__MODEL') && (array_search(C__CATG__MODEL, $l_categories) !== false || (array_key_exists(C__CATG__MODEL, $l_categories) !== false && !$l_gui))) {
            $l_add_model = true;
            $l_daoModel = isys_cmdb_dao_category_g_model::instance($this->container->database);
            $l_daoStackMember = isys_cmdb_dao_category_g_stack_member::instance($this->container->database);
            $l_category_selection_as_string .= 'Model, ';
            $l_categories[] = 'C__CATG__MODEL';
        }

        if (defined('C__CATG__UNIVERSAL_INTERFACE') && (array_search(C__CATG__UNIVERSAL_INTERFACE, $l_categories) !== false || (array_key_exists(C__CATG__UNIVERSAL_INTERFACE, $l_categories) !== false && !$l_gui))) {
            $l_add_ui = true;
            $l_daoUI = isys_cmdb_dao_category_g_ui::instance($this->container->database);
            $l_category_selection_as_string .= 'Interface, ';
            $l_categories[] = 'C__CATG__UNIVERSAL_INTERFACE';
        }

        if (defined('C__CATG__DRIVE') && (array_search(C__CATG__DRIVE, $l_categories) !== false || (array_key_exists(C__CATG__DRIVE, $l_categories) !== false && !$l_gui))) {
            $l_add_drive = true;
            $l_dao_drive = isys_cmdb_dao_category_g_drive::instance($this->container->database);
            $l_category_selection_as_string .= 'Drives, ';
            $l_categories[] = 'C__CATG__DRIVE';
        }

        if (defined('C__CATG__VIRTUAL_MACHINE') && (array_search(C__CATG__VIRTUAL_MACHINE, $l_categories) !== false || (array_key_exists(C__CATG__VIRTUAL_MACHINE, $l_categories) !== false && !$l_gui))) {
            $l_add_virtual_machine = true;
            $l_dao_vm = isys_cmdb_dao_category_g_virtual_machine::instance($this->container->database);
            $l_dao_gs = isys_cmdb_dao_category_g_guest_systems::instance($this->container->database);
            $l_categories[] = 'C__CATG__VIRTUAL_MACHINE';
        }

        if ($l_category_selection_as_string != '') {
            $this->m_log->info('Following categories are selected for the import: ' . $l_category_selection_as_string);
        } else {
            $this->m_log->warning('No categories selected for the import.');
        }

        try {
            $this->m_log->info('Preparing environment data for the import.');


            $l_capacityUnitMB = $l_daoCMDB->retrieve("SELECT isys_memory_unit__id FROM isys_memory_unit WHERE isys_memory_unit__const = 'C__MEMORY_UNIT__MB'")
                ->get_row_value('isys_memory_unit__id');
            $l_capacityUnitKB = $l_daoCMDB->retrieve("SELECT isys_memory_unit__id FROM isys_memory_unit WHERE isys_memory_unit__const = 'C__MEMORY_UNIT__KB'")
                ->get_row_value('isys_memory_unit__id');

            $l_frequency_unit = $l_daoCMDB->retrieve("SELECT isys_frequency_unit__id FROM isys_frequency_unit WHERE isys_frequency_unit__const = 'C__FREQUENCY_UNIT__GHZ'")
                ->get_row_value('isys_frequency_unit__id');
            $l_model_default_manufact = $l_daoCMDB->retrieve("SELECT isys_model_manufacturer__title FROM isys_model_manufacturer WHERE isys_model_manufacturer__const = 'C__MODEL_NOT_SPECIFIED'")
                ->get_row_value('isys_model_manufacturer__title');

            $l_app_manufacturer = [];
            if ($l_add_application) {
                $l_res = $l_daoCMDB->retrieve("SELECT isys_application_manufacturer__id, isys_application_manufacturer__title FROM isys_application_manufacturer");
                while ($l_row = $l_res->get_row()) {
                    $l_app_manufacturer[$l_row['isys_application_manufacturer__id']] = trim($language->get($l_row['isys_application_manufacturer__title']));
                }
            }

            $l_vm_types = [];
            if ($l_add_virtual_machine) {
                $l_res = $l_daoCMDB->retrieve("SELECT isys_vm_type__id, isys_vm_type__title FROM isys_vm_type");
                while ($l_row = $l_res->get_row()) {
                    $l_vm_types[$l_row['isys_vm_type__id']] = trim($language->get($l_row['isys_vm_type__title']));
                }
            }

            if ($l_add_net) {
                $l_resNet_s = $l_daoNet_s->get_data();
                while ($l_rowNetS = $l_resNet_s->get_row()) {
                    $l_net_address = null;
                    $cidrSuffix = $l_rowNetS['isys_cats_net_list__cidr_suffix'];
                    if ($l_rowNetS['isys_cats_net_list__address'] === null || $l_rowNetS['isys_cats_net_list__address'] == '') {
                        $l_net_address = substr($l_rowNetS['isys_obj__title'], 0, strpos($l_rowNetS['isys_obj__title'], '/'));
                        if (!Ip::validate_net_ip($l_net_address)) {
                            continue;
                        }
                    } else {
                        $l_net_address = $l_rowNetS['isys_cats_net_list__address'];
                    }
                    if ($l_net_address !== null) {
                        $l_net_arr[$l_net_address . '|' . $cidrSuffix] = [
                            'row_data' => $l_rowNetS,
                        ];
                    }
                }
            }

            $l_conTypeTitle = $l_daoCMDB->retrieve("SELECT isys_ui_con_type__title FROM isys_ui_con_type WHERE isys_ui_con_type__const = 'C__UI_CON_TYPE__OTHER'")
                ->get_row_value('isys_ui_con_type__title');

            if ($l_capacityUnitMB == null) {
                $this->m_log->debug("Internal error: ID for capacity unit MB could not be retrieved");

                // throw new Exception("Internal error: ID for capacity unit MB could not be retrieved");
                return;
            }

            if (is_countable($l_hardwareIDs) && count($l_hardwareIDs) > 0) {
                $output->writeln('<info>Starting Import.</info>');
                $this->m_log->info('Device count: ' . count($l_hardwareIDs));
                foreach ($l_hardwareIDs as $l_position => $l_hardwareID) {
                    $l_objID = false;
                    $l_object_updated = false;

                    $l_hw_data = $ocsHardware->getDeviceInfo(
                        $l_daoOCS,
                        $l_hardwareID,
                        $l_add_model,
                        $l_add_memory,
                        $l_add_application,
                        $l_add_graphic,
                        $l_add_sound,
                        $l_add_storage,
                        $l_add_net,
                        $l_add_ui,
                        $l_add_drive,
                        $l_add_virtual_machine,
                        $l_add_cpu
                    );
                    $l_inventory = $l_hw_data['inventory'];
                    $l_inventory["NAME"] = trim($l_inventory["NAME"]);

                    $l_thisObjTypeID = null;

                    if ($l_inventory == null || empty($l_inventory["NAME"])) {
                        $output->writeln("Object with ID " . $l_hardwareID . " does not exist");
                        $this->m_log->debug("Object with ID \"" . $l_hardwareID . "\" does not exist. Skipping to next device.");
                        continue;
                    }

                    // Ignore Loopback devices
                    if ($l_inventory["IPADDR"] == '127.0.0.1') {
                        $output->writeln("Object with ID " . $l_hardwareID . " has a loopback address and will be skipped.");
                        $this->m_log->debug("Object with ID " . $l_hardwareID . " has a loopback address and will be skipped.");
                        continue;
                    }

                    $output->writeln("Processing device: \"" . $l_inventory["NAME"] . "\".");
                    $this->m_log->info("Processing device: \"" . $l_inventory["NAME"] . "\".");

                    // Object matching variables
                    $l_macaddresses = [];
                    $l_serialnumber = null;
                    $l_objectTitle = $l_inventory["NAME"];

                    // New object, or update existing?
                    if (is_countable($l_inventory['macaddr']) && count($l_inventory['macaddr'])) {
                        $this->m_log->debug("MAC-addresses found for " . $l_inventory["NAME"] . ".");
                        $l_macaddresses = $l_inventory['macaddr'];
                    } else {
                        $output->writeln("No MAC-Addresses found for \"" . $l_inventory["NAME"] . "\".");
                        $this->m_log->debug("No MAC-Addresses found for \"" . $l_inventory["NAME"] . "\".");
                    }

                    if ($l_inventory['SSN'] != '') {
                        $this->m_log->debug("Serial found for \"" . $l_inventory["NAME"] . "\".");
                        $l_serialnumber = $l_inventory['SSN'];
                    } elseif ($l_inventory["SSN"] == '') {
                        $output->writeln("No Serial found for \"" . $l_inventory["NAME"] . "\".");
                        $this->m_log->debug("No Serial found for \"" . $l_inventory["NAME"] . "\".");
                    }

                    if (strpos($l_inventory["NAME"], '.') !== false) {
                        $output->writeln("Possible FQDN found \"" . $l_inventory["NAME"] . "\".");
                        $this->m_log->debug("Possible FQDN found \"" . $l_inventory["NAME"] . "\".");
                    } else {
                        $output->writeln("No possible FQDN found for \"" . $l_inventory["NAME"] . "\".");
                        $this->m_log->debug("No possible FQDN found for \"" . $l_inventory["NAME"] . "\".");
                    }

                    $this->m_log->debug("Checking object type.");
                    $l_objectTypeMatch = true;
                    if (isset($l_objtype_arr[$l_position]) && $l_objtype_arr[$l_position] > 0) {
                        $l_thisObjTypeID = $l_objtype_arr[$l_position];
                        $this->m_log->debug("Object type is set in the dialog. Using selected Object type " . $language->get($l_daoCMDB->get_objtype_name_by_id_as_string($l_thisObjTypeID)) . " for \"" . $l_inventory["NAME"] . "\".");
                    } else {
                        foreach ($l_config_obj_types as $l_conf_objtype_id => $l_prefix) {
                            if ($l_thisObjTypeID === null) {
                                $l_prefix_arr = null;
                                if (strpos($l_prefix, ',') !== false) {
                                    $l_prefix_arr = explode(',', $l_prefix);
                                }

                                if (is_array($l_prefix_arr)) {
                                    foreach ($l_prefix_arr as $l_sub_prefix) {
                                        if ($ocsHardware->check_pattern_for_objtype($l_sub_prefix, $l_inventory["TAG"], $l_inventory["NAME"])) {
                                            $l_thisObjTypeID = $l_conf_objtype_id;
                                            break;
                                        }
                                    }
                                } else {
                                    if ($ocsHardware->check_pattern_for_objtype($l_prefix, $l_inventory["TAG"], $l_inventory["NAME"])) {
                                        $l_thisObjTypeID = $l_conf_objtype_id;
                                    }
                                }
                            } else {
                                break;
                            }
                        }

                        // Use Default Object type
                        if ($l_thisObjTypeID === null) {
                            if ($l_objType > 0) {
                                $output->writeln("Could not determine object type from configuration. Using default object type: " . $l_default_objtype . ".");
                                $this->m_log->debug("Could not determine object type from configuration. Using default object type \"" . $l_default_objtype . "\" for \"" .
                                    $l_inventory["NAME"] . "\".");
                                $l_thisObjTypeID = $l_objType;
                                $l_objectTypeMatch = false;
                            } else {
                                $output->writeln("No default object type has been defined.");
                            }
                        }
                    }

                    $l_objtype_const = null;

                    if ($l_thisObjTypeID > 0) {
                        if (isset($this->m_objtypes[$l_thisObjTypeID])) {
                            $l_objtype_const = $this->m_objtypes[$l_thisObjTypeID];
                        } else {
                            $this->m_objtypes[$l_thisObjTypeID] = $l_objtype_const = $l_daoCMDB->get_object_type($l_thisObjTypeID)['isys_obj_type__const'];
                        }
                    }

                    // Get object id by object matching
                    $l_objID = $matcher->get_object_id_by_matching($l_serialnumber, $l_macaddresses, $l_objectTitle, $l_objtype_const, null, null);

                    // first check
                    if ($l_objID) {
                        $this->m_log->info("Object found. Updating object-ID: " . $l_objID);
                        $l_object_updated = true;
                        // Update existing object
                        $l_row = $l_daoCMDB->get_object_by_id($l_objID)
                            ->get_row();

                        $l_thisObjTypeID = ($l_objtype_arr[$l_position] === 'recalculate' || $recalculateObjectType) && is_numeric($l_thisObjTypeID) ? $l_thisObjTypeID : $l_row["isys_obj__isys_obj_type__id"];

                        if ($l_thisObjTypeID > 0 && in_array($l_thisObjTypeID, $this->m_objtype_blacklist)) {
                            $l_error_msg = "Error: Import object '" . $l_inventory["NAME"] . "' (#" . $l_objID . ") is of blacklisted object type '" .
                                $language->get($l_daoCMDB->get_objtype_name_by_id_as_string(intval($l_row['isys_obj_type__id'])) . "'");

                            $output->writeln($l_error_msg);
                            $this->m_log->debug($l_error_msg);
                            continue;
                        }
                        $l_update_msg = "Updating existing object " . $l_inventory["NAME"] . " (" . $language->get($l_daoCMDB->get_objtype_name_by_id_as_string(intval($l_row['isys_obj_type__id']))) . ")";
                        $output->writeln($l_update_msg);

                        if ($l_thisObjTypeID != $l_row["isys_obj__isys_obj_type__id"]) {
                            $output->writeln('Changing object type to recalculated value \'' . $language->get($l_daoCMDB->get_objtype_name_by_id_as_string(intval($l_thisObjTypeID))) . '\'.');
                            $l_daoCMDB->update_object($l_objID, $l_thisObjTypeID);
                        }

                        $l_update_title = '';
                        if ($l_row['isys_obj__title'] !== $l_inventory['NAME']) {
                            $this->m_log->debug("Object title is differnt: " . $l_row['isys_obj__title'] . " (i-doit) !== " . $l_inventory["NAME"] . " (OCS).");
                            if (is_array($l_daoGl->validate(['title' => $l_inventory['NAME']])) || isys_tenantsettings::get('cmdb.unique.object-title')) {
                                $l_title = $l_daoCMDB->generate_unique_obj_title($l_inventory['NAME']);
                                $l_update_title = "isys_obj__title = " . $l_daoCMDB->convert_sql_text($l_title) . ", ";
                            } else {
                                $l_update_title = "isys_obj__title = " . $l_daoCMDB->convert_sql_text($l_inventory['NAME']) . ", ";
                            }
                        }

                        $l_update = "UPDATE isys_obj SET " . $l_update_title . "isys_obj__hostname = " . $l_daoCMDB->convert_sql_text($l_inventory["NAME"]) . ", " .
                            "isys_obj__updated     =    NOW(), " . "isys_obj__updated_by  =    '" . $this->container->session->get_current_username() . "', " .
                            "isys_obj__imported    =    NOW() ";

                        if (isset($l_objtype_arr[$l_position]) && $l_objtype_arr[$l_position] > 0) {
                            $this->m_log->debug("Updating object with object type: " . $l_daoCMDB->get_objtype_name_by_id_as_string($l_objtype_arr[$l_position]) . ".");
                            $l_update .= ", isys_obj__isys_obj_type__id = " . $l_daoCMDB->convert_sql_id($l_objtype_arr[$l_position]) . " ";
                        }

                        /**
                         * Main object data
                         */
                        $l_daoCMDB->update($l_update . "WHERE isys_obj__id = " . $l_daoCMDB->convert_sql_id($l_row["isys_obj__id"]) . ";");
                    } else {
                        if ($l_thisObjTypeID === null) {
                            $output->writeln("Object type could not be identified and no default object type is set in the configuration. Skipping device!!");
                            continue;
                        }

                        $output->writeln("Creating new object " . $l_inventory["NAME"] . " (" . $language->get($l_daoCMDB->get_objtype_name_by_id_as_string($l_thisObjTypeID)) . ")");
                        $this->m_log->info("Object not found. Creating new object " . $l_inventory["NAME"] . " (" . $language->get($l_daoCMDB->get_objtype_name_by_id_as_string($l_thisObjTypeID)) . ")");

                        $l_objID = $l_daoCMDB->insert_new_obj(
                            $l_thisObjTypeID,
                            false,
                            $l_inventory["NAME"] ?: $emptyValue,
                            null,
                            C__RECORD_STATUS__NORMAL,
                            $l_inventory["NAME"],
                            null,
                            true,
                            null,
                            null,
                            null,
                            null,
                            null,
                            null,
                            $l_cmdb_status_id
                        );

                        if ($l_logb_active) {
                            isys_event_manager::getInstance()
                                ->triggerCMDBEvent("C__LOGBOOK_EVENT__OBJECT_CREATED", "-object imported from OCS-", $l_objID, $l_thisObjTypeID);
                        }
                    }

                    /*
                     * Clear categories hostaddress and port
                     */
                    if ($l_overwrite_ip_port) {
                        $l_daoCMDB->clear_data($l_objID, 'isys_catg_ip_list', true);
                        $l_daoCMDB->clear_data($l_objID, 'isys_catg_port_list', true);
                        $output->writeln("Categories hostaddress and port cleared.", true);
                        $this->m_log->debug("Categories hostaddress and port cleared.");
                    }

                    /**
                     * Claer all categories
                     */
                    if ($l_overwrite_all_cats) {
                        $translation = isys_application::instance()->container->get('language');
                        $db = $l_daoCMDB->get_database_component();

                        foreach ($l_categories as $categoryConstant) {
                            if (stripos($categoryConstant, 'C__CATG') === false) {
                                continue;
                            }
                            $sql = "SELECT isysgui_catg__title as title, isysgui_catg__source_table as tb FROM isysgui_catg 
                                WHERE isysgui_catg__const LIKE '" . $categoryConstant . "' LIMIT 1";
                            $result = $db->query($sql);
                            $categoryRow = $result->fetch_assoc();
                            $tableName = $categoryRow['tb'] . '_list';
                            if ($db->is_table_existent($tableName)) {
                                $l_daoCMDB->clear_data($l_objID, $tableName, true);
                                $title = $translation->get($categoryRow['title']);
                                $output->writeln("Global Category " . $title . " cleared.", true);
                                $this->m_log->debug("Global Category " . $title . " cleared.");
                            }
                        }

                        if ($l_add_os === true) {
                            $tableName = 'isys_catg_application_list';
                            \isys_cmdb_dao_category_g_operating_system::instance($db)->clear_data($l_objID, $tableName);
                            $output->writeln("Global Category Operating System cleared.", true);
                            $this->m_log->debug("Global Category Operating System cleared.");
                        }
                    }

                    /**
                     * Model
                     */
                    if (isset($l_hw_data['model']) && isset($l_daoModel)) {
                        $output->writeln("Processing model");
                        $this->m_log->info("Processing model");

                        $ocsHardware->handleModel($l_objID, $l_thisObjTypeID, $l_hw_data['model'], $l_logb_active);
                    }

                    /**
                     * Processors
                     */
                    if ($l_add_cpu && isset($l_daoCPU)) {
                        $output->writeln("Processing CPUs");
                        $this->m_log->info("Processing CPUs");

                        // @See ID-5415 Special case because the hardware source data is identical snmp source data
                        $ocsSnmp->handleCpu($l_objID, $l_thisObjTypeID, $l_hw_data['cpu'], $l_frequency_unit, $l_logb_active);
                    }

                    /**
                     * Memory
                     */
                    if (isset($l_hw_data['memory']) && isset($l_daoMemory)) {
                        $output->writeln("Processing memory");
                        $this->m_log->info("Processing memory");

                        $ocsHardware->handleMemory($l_objID, $l_thisObjTypeID, $l_hw_data['memory'], $l_capacityUnitMB, $l_logb_active);
                    }

                    /**
                     * Operating system
                     */
                    if ($l_add_os) {
                        $output->writeln("Processing Operating system");
                        $this->m_log->info("Processing Operating system");

                        $ocsHardware->handleOperatingSystem($l_objID, $l_thisObjTypeID, $l_inventory, $l_logb_active);
                    }

                    /**
                     * Applications
                     */
                    if (isset($l_hw_data['application'])) {
                        $output->writeln("Processing applications");
                        $this->m_log->info("Processing applications");

                        $ocsHardware->handleApplications(
                            $l_objID,
                            $l_thisObjTypeID,
                            $l_hw_data['application'],
                            $l_app_manufacturer,
                            $l_relation_data,
                            $l_inventory["NAME"],
                            $l_logb_active
                        );
                    }

                    /**
                     * Graphics adapter
                     */
                    if (isset($l_hw_data['graphic']) && isset($l_dao_graphic)) {
                        $output->writeln("Processing graphics adapter");
                        $this->m_log->info("Processing graphics adapter");

                        $ocsHardware->handleGraphic($l_objID, $l_thisObjTypeID, $l_hw_data['graphic'], $l_capacityUnitMB, $l_logb_active);
                    }

                    /**
                     * Sound adapter
                     */
                    if (isset($l_hw_data['sound']) && isset($l_dao_sound)) {
                        $output->writeln("Processing sound adapter");
                        $this->m_log->info("Processing sound adapter");

                        $ocsHardware->handleSound($l_objID, $l_thisObjTypeID, $l_hw_data['sound'], $l_logb_active);
                    }

                    /**
                     * Drives
                     */
                    if (isset($l_hw_data['drive']) && isset($l_dao_drive)) {
                        $output->writeln("Processing drives");
                        $this->m_log->info("Processing drives");

                        $ocsHardware->handleDrive($l_objID, $l_thisObjTypeID, $l_hw_data['drive'], $l_logb_active);
                    }

                    /**
                     * Network
                     */
                    if (isset($l_hw_data['net'])) {
                        $output->writeln("Processing network");
                        $this->m_log->info("Processing network");
                        unset($l_check_ip, $l_check_net, $l_check_iface, $l_check_port, $l_already_imported_ips);

                        $ocsHardware->handleNet($l_objID, $l_thisObjTypeID, $l_hw_data['net'], $l_inventory['NAME'], $l_net_arr, $l_logb_active, $l_overwrite_ip_port);
                    }

                    /**
                     * Universal interfaces
                     */
                    if (isset($l_hw_data['ui']) && isset($l_daoUI)) {
                        $output->writeln("Processing UI");
                        $this->m_log->info("Processing UI");

                        $ocsHardware->handleUi($l_objID, $l_thisObjTypeID, $l_hw_data['ui'], $l_conTypeTitle, $l_logb_active);
                    }

                    /**
                     * Storage
                     */
                    if (isset($l_hw_data['stor']) && isset($l_daoStor)) {
                        $output->writeln("Processing storage");
                        $this->m_log->info("Processing storage");

                        $ocsHardware->handleStorage($l_objID, $l_thisObjTypeID, $l_hw_data['stor'], $l_capacityUnitMB, $l_logb_active);
                    }

                    if ($l_object_updated === true) {
                        $output->writeln($l_inventory["NAME"] . " succesfully updated\n\n");
                        $this->m_log->info("\"" . $l_inventory["NAME"] . "\" succesfully updated.");
                    } else {
                        $output->writeln($l_inventory["NAME"] . " succesfully imported\n\n");
                        $this->m_log->info("\"" . $l_inventory["NAME"] . "\" succesfully imported.");
                    }

                    $this->m_log->flush_log(true, false);
                }
            }

            /**
             * @TODO Memory in ocs database the value is puzzling
             */
            if (is_countable($l_snmpIDs) && count($l_snmpIDs) > 0) {
                $this->m_log->info("Found " . count($l_snmpIDs) . " SNMP devices.");
                $this->m_log->info("Starting import for SNMP devices.");
                $l_object_ids = [];
                foreach ($l_snmpIDs as $l_position => $l_snmp_id) {
                    if (!($l_hw_data = $ocsSnmp->getDeviceInfo(
                        $l_daoOCS,
                        $l_snmp_id,
                        $l_add_memory,
                        $l_add_storage,
                        $l_add_net,
                        $l_add_cpu,
                        $l_add_ui,
                        $l_add_model,
                        $l_add_application,
                        $l_add_graphic,
                        $l_add_sound,
                        $l_add_drive,
                        $l_add_virtual_machine
                    ))) {
                        // Device has already been imported
                        continue;
                    }

                    $l_thisObjTypeID = null;
                    $l_object_updated = false;
                    $l_serialnumber = null;
                    $l_objectTitle = null;
                    $l_macaddresses = [];
                    $l_objectTypeMatch = true;

                    $l_inventory = $l_hw_data['inventory'];
                    $l_objectTitle = $l_inventory["NAME"] = trim($l_inventory["NAME"]);

                    if ($l_inventory == null) {
                        $output->writeln("Object with ID " . $l_snmp_id . " does not exist");
                        $this->m_log->debug("Object wit ID " . $l_snmp_id . " does not exist. Skipping to next device.");
                        continue;
                    }

                    if (isset($l_hw_data['model']) && is_countable($l_hw_data['model']) && count($l_hw_data['model']) === 1) {
                        $l_inventory['SSN'] = $l_hw_data['model'][0]['SERIALNUMBER'];
                    }

                    if (isset($l_inventory['SSN'])) {
                        $this->m_log->debug("Serialnumber found for " . $l_inventory["NAME"] . ".");
                        $l_serialnumber = $l_inventory['SSN'];
                    } else {
                        if (is_countable($l_hw_data['model']) && count($l_hw_data['model']) > 1) {
                            $output->writeln("Could not identify Serialnumber for device \"" . $l_inventory["NAME"] . "\".");
                            $this->m_log->debug("Could not identify Serialnumber for device \"" . $l_inventory["NAME"] . "\".");
                        } else {
                            $output->writeln("No Serialnumber found for \"" . $l_inventory["NAME"] . "\".");
                            $this->m_log->debug("No Serialnumber found for \"" . $l_inventory["NAME"] . "\".");
                        }
                    }

                    // New object, or update existing?
                    if (is_countable($l_inventory['macaddr']) && count($l_inventory['macaddr'])) {
                        $this->m_log->debug("MAC-addresses found for " . $l_inventory["NAME"] . ".");
                        $l_macaddresses = $l_inventory['macaddr'];
                    } else {
                        $output->writeln("No MAC-Addresses found for \"" . $l_inventory["NAME"] . "\".");
                        $this->m_log->debug("No MAC-Addresses found for \"" . $l_inventory["NAME"] . "\".");
                    }

                    if (strpos($l_inventory["NAME"], '.') !== false) {
                        $output->writeln("Possible FQDN found \"" . $l_inventory["NAME"] . "\".");
                        $this->m_log->debug("Possible FQDN found \"" . $l_inventory["NAME"] . "\".");
                    } else {
                        $output->writeln("No possible FQDN found for \"" . $l_inventory["NAME"] . "\".");
                        $this->m_log->debug("No possible FQDN found for \"" . $l_inventory["NAME"] . "\".");
                    }

                    if (isset($l_objtype_snmp_arr[$l_position]) && $l_objtype_snmp_arr[$l_position] > 0) {
                        $this->m_log->debug("Object type is set in dialog. Using \"" . $l_daoCMDB->get_objtype_name_by_id_as_string($l_objtype_snmp_arr[$l_position]) .
                            "\" as object type.");
                        $l_thisObjTypeID = $l_objtype_snmp_arr[$l_position];
                    } else {
                        foreach ($l_config_obj_types as $l_conf_objtype_id => $l_prefix) {
                            if ($l_thisObjTypeID === null) {
                                $l_prefix_arr = null;
                                if (strpos($l_prefix, ',') !== false) {
                                    $l_prefix_arr = explode(',', $l_prefix);
                                }

                                if (is_array($l_prefix_arr)) {
                                    foreach ($l_prefix_arr as $l_sub_prefix) {
                                        if ($ocsHardware->check_pattern_for_objtype($l_sub_prefix, $l_inventory["TAG"], $l_inventory["NAME"])) {
                                            $l_thisObjTypeID = $l_conf_objtype_id;
                                            break;
                                        }
                                    }
                                } else {
                                    if ($ocsHardware->check_pattern_for_objtype($l_prefix, $l_inventory["TAG"], $l_inventory["NAME"])) {
                                        $l_thisObjTypeID = $l_conf_objtype_id;
                                    }
                                }
                            } else {
                                break;
                            }
                        }

                        if ($l_thisObjTypeID === null) {
                            // Get Object type from ocs
                            switch ($l_inventory['OBJTYPE']) {
                                case 'blade':
                                    $l_thisObjTypeID = defined_or_default('C__OBJTYPE__BLADE_CHASSIS');
                                    break;
                                case 'printer':
                                    $l_thisObjTypeID = defined_or_default('C__OBJTYPE__PRINTER');
                                    break;
                                case 'switch':
                                    $l_thisObjTypeID = defined_or_default('C__OBJTYPE__SWITCH');
                                    break;
                                case 'router':
                                    $l_thisObjTypeID = defined_or_default('C__OBJTYPE__ROUTER');
                                    break;
                                case 'server':
                                    $l_thisObjTypeID = defined_or_default('C__OBJTYPE__SERVER');
                                    break;
                                default:
                                    $l_thisObjTypeID = $l_objType;
                                    break;
                            }
                            $output->writeln("Could not determine object type from configuration. Using object type " . $l_inventory['OBJTYPE'] . " with object type id " .
                                $l_thisObjTypeID . ".");
                            $this->m_log->debug("Could not determine object type from configuration. Using object type " .
                                $l_daoCMDB->get_objtype_name_by_id_as_string($l_inventory['OBJTYPE']) . " with object type id " .
                                $l_daoCMDB->get_objtype_name_by_id_as_string($l_thisObjTypeID) . ".");

                            $l_objectTypeMatch = false;
                        }
                    }

                    $l_objtype_const = null;

                    if ($l_thisObjTypeID > 0) {
                        if (isset($this->m_objtypes[$l_thisObjTypeID])) {
                            $l_objtype_const = $this->m_objtypes[$l_thisObjTypeID];
                        } else {
                            $this->m_objtypes[$l_thisObjTypeID] = $l_objtype_const = $l_daoCMDB->get_object_type($l_thisObjTypeID)['isys_obj_type__const'];
                        }
                    }

                    // Get object id by object matching
                    $l_objID = $matcher->get_object_id_by_matching($l_serialnumber, $l_macaddresses, $l_objectTitle, $l_objtype_const, null, null);

                    if ($l_objID) {
                        $this->m_log->info("Object found. Updating object-ID: " . $l_objID);
                        $l_object_updated = true;
                        // Update existing object
                        $l_row = $l_daoCMDB->get_object_by_id($l_objID)
                            ->get_row();

                        $l_thisObjTypeID = ($l_objtype_snmp_arr[$l_position] === 'recalculate' || $recalculateObjectType) && is_numeric($l_thisObjTypeID) ? $l_thisObjTypeID : $l_row["isys_obj__isys_obj_type__id"];

                        $l_update_msg = "Updating existing object " . $l_inventory["NAME"] . " (" . $language->get($l_daoCMDB->get_objtype_name_by_id_as_string(intval($l_row['isys_obj_type__id']))) . ")";

                        $output->writeln($l_update_msg);
                        $this->m_log->debug($l_update_msg);

                        if ($l_thisObjTypeID != $l_row["isys_obj__isys_obj_type__id"]) {
                            $output->writeln('Changing object type to recalculated value \'' . $language->get($l_daoCMDB->get_objtype_name_by_id_as_string(intval($l_thisObjTypeID))) . '\'.');
                            $l_daoCMDB->update_object($l_objID, $l_thisObjTypeID);
                        }

                        $l_update_title = '';

                        /**
                         * Main object data
                         */
                        if ($l_row['isys_obj__title'] !== $l_inventory['NAME']) {
                            $this->m_log->debug("Object title is differnt: \"" . $l_row['isys_obj__title'] . "\" (i-doit) !== \"" . $l_inventory["NAME"] . "\" (OCS).");
                            if (is_array($l_daoGl->validate(['title' => $l_inventory['NAME']])) || isys_tenantsettings::get('cmdb.unique.object-title')) {
                                $l_title = $l_daoCMDB->generate_unique_obj_title($l_inventory['NAME']);
                                $l_update_title = "isys_obj__title = " . $l_daoCMDB->convert_sql_text($l_title) . ", ";
                            } else {
                                $l_update_title = "isys_obj__title = " . $l_daoCMDB->convert_sql_text($l_inventory['NAME']) . ", ";
                            }
                        }

                        $l_update = "UPDATE isys_obj SET " . $l_update_title . "isys_obj__hostname = " . $l_daoCMDB->convert_sql_text($l_inventory["NAME"]) . ", " .
                            "isys_obj__updated     =    NOW(), " . "isys_obj__updated_by  =    '" . $this->container->session->get_current_username() . "', " .
                            "isys_obj__imported    =    NOW() ";

                        if ($l_row['isys_obj__description'] == '') {
                            $l_update .= ", isys_obj__description = " . $l_daoCMDB->convert_sql_text($l_inventory['DESCRIPTION']) . " ";
                        }

                        if (isset($l_objtype_snmp_arr[$l_position]) && $l_objtype_snmp_arr[$l_position] > 0) {
                            $this->m_log->debug("Updating object with object type: " . $l_daoCMDB->get_objtype_name_by_id_as_string($l_objtype_snmp_arr[$l_position]) . ".");
                            $l_update .= ", isys_obj__isys_obj_type__id = " . $l_daoCMDB->convert_sql_id($l_objtype_snmp_arr[$l_position]) . " ";
                        }

                        $l_daoCMDB->update($l_update . " WHERE isys_obj__id    =    " . $l_daoCMDB->convert_sql_text($l_row["isys_obj__id"]) . ";");
                    } else {
                        if ($l_thisObjTypeID === null) {
                            $output->writeln("Object type could not be identified and no default object type is set in the configuration. Skipping device!!\n");
                            continue;
                        }

                        $output->writeln("Creating new object " . $l_inventory["NAME"] . " (" . $language->get($l_daoCMDB->get_objtype_name_by_id_as_string($l_thisObjTypeID)) . ")");
                        $this->m_log->info("Object not found. Creating new object " . $l_inventory["NAME"] . " (" . $language->get($l_daoCMDB->get_objtype_name_by_id_as_string($l_thisObjTypeID)) . ")");

                        $l_objID = $l_daoCMDB->insert_new_obj(
                            $l_thisObjTypeID,
                            false,
                            $l_inventory["NAME"] ?: $emptyValue,
                            null,
                            C__RECORD_STATUS__NORMAL,
                            $l_inventory["NAME"],
                            null,
                            true,
                            null,
                            null,
                            null,
                            null,
                            null,
                            null,
                            $l_cmdb_status_id
                        );

                        if ($l_logb_active) {
                            isys_event_manager::getInstance()
                                ->triggerCMDBEvent("C__LOGBOOK_EVENT__OBJECT_CREATED", "-object imported from OCS-", $l_objID, $l_thisObjTypeID);
                        }
                    }

                    /*
                     * Clear categories hostaddress and port
                     */
                    if ($l_overwrite_ip_port) {
                        $l_daoCMDB->clear_data($l_objID, 'isys_catg_ip_list', true);
                        $l_daoCMDB->clear_data($l_objID, 'isys_catg_port_list', true);
                        $output->writeln("Categories hostaddress and port cleared.", true);
                        $this->m_log->info("Categories hostaddress and port cleared.");
                    }

                    $l_object_ids[] = $l_objID;

                    /**
                     * Model
                     */
                    if (isset($l_hw_data['model'])) {
                        $output->writeln("Processing model");
                        $this->m_log->info("Processing model");

                        $ocsSnmp->handleModel($l_objID, $l_thisObjTypeID, $l_hw_data['model'], $l_inventory['NAME'], $l_model_default_manufact, $l_logb_active);
                    }

                    if (isset($l_hw_data['cpu'])) {
                        $output->writeln("Processing CPUs");
                        $this->m_log->info("Processing CPUs");

                        $ocsSnmp->handleCpu($l_objID, $l_thisObjTypeID, $l_hw_data['cpu'], $l_frequency_unit, $l_logb_active);
                    }

                    if (isset($l_hw_data['memory'])) {
                        $output->writeln("Processing memory");
                        $this->m_log->info("Processing memory");

                        $ocsSnmp->handleMemory($l_objID, $l_thisObjTypeID, $l_hw_data['memory'], $l_capacityUnitKB, $l_logb_active);
                    }

                    /**
                     * Network
                     */
                    if (isset($l_hw_data['net'])) {
                        $output->writeln("Processing network");
                        $this->m_log->info("Processing network");

                        $ocsSnmp->handleNet($l_objID, $l_thisObjTypeID, $l_hw_data['net'], $l_inventory['NAME'], $l_net_arr, $l_logb_active, $l_overwrite_ip_port);
                    }

                    /**
                     * Universal interfaces
                     */
                    if (isset($l_hw_data['ui'])) {
                        $output->writeln("Processing UI");
                        $this->m_log->info("Processing UI");

                        $ocsSnmp->handleUi($l_objID, $l_thisObjTypeID, $l_hw_data['ui'], $l_conTypeTitle, $l_logb_active);
                    }

                    /**
                     * Storage
                     */
                    if (isset($l_hw_data['stor'])) {
                        $output->writeln("Processing storage");
                        $this->m_log->info("Processing storage");

                        $ocsSnmp->handleStorage($l_objID, $l_thisObjTypeID, $l_hw_data['stor'], $l_capacityUnitMB, $l_logb_active);
                    }

                    if (isset($l_hw_data['application'])) {
                        $output->writeln("Processing applications");
                        $this->m_log->info("Processing applications");

                        $ocsSnmp->handleApplications($l_objID, $l_thisObjTypeID, $l_hw_data['application'], $l_relation_data, $l_inventory["NAME"], $l_logb_active);
                    }

                    if ($l_object_updated === true) {
                        $output->writeln($l_inventory["NAME"] . " succesfully updated\n\n");
                        $this->m_log->info("\"" . $l_inventory["NAME"] . "\" succesfully updated.");
                    } else {
                        $output->writeln($l_inventory["NAME"] . " succesfully imported\n\n");
                        $this->m_log->info("\"" . $l_inventory["NAME"] . "\" succesfully imported.");
                    }

                    $this->m_log->flush_log(true, false);
                }
            }
            $this->container->database->commit();

            /**
             * Create index for imported/updated objects, based on the start time of this import
             */
            $output->writeln("Regenerating search index..");
            $this->m_log->info("Regenerating search index..");

            if (defined('C__CATG__OPERATING_SYSTEM') && ($osIndex = array_search('operating_system', $l_categories))) {
                $l_categories[$osIndex] = 'C__CATG__OPERATING_SYSTEM';
            }

            if (defined('C__CATG__GLOBAL') && in_array('C__CATG__GLOBAL', $l_categories) === false) {
                $l_categories[] = 'C__CATG__GLOBAL';
            }

            $startTimeIndexCreation = microtime(true);
            Signals::instance()
                ->onPostImport($startTime, $l_categories, [defined_or_default('C__CATS__APPLICATION')]);
            $indexCreationText = "Index creation took " . number_format(microtime(true) - $startTimeIndexCreation, 2) . " secs.";
            $output->writeln($indexCreationText);
            $this->m_log->info($indexCreationText);

            $output->writeln("Import successful");
            $this->m_log->info("Import successful");
        } catch (Exception $e) {
            $this->container->database->rollback();
            $output->writeln("Import failed");
            $this->m_log->info("Import failed");
            throw $e;
        }

        $this->m_log->flush_log();
    }

    /**
     * @author Van Quyen Hoang <qhoang@i-doit.org>
     * @return int|boolean
     */
    private function getOcsDatabaseId(InputInterface $input)
    {
        if (!empty($_SERVER['HTTP_HOST'])) {
            return isys_glob_get_param('selected_ocsdb');
        } else {
            if (($databaseSchema = $input->getOption('databaseSchema')) !== null) {
                if (is_numeric($databaseSchema)) {
                    return $databaseSchema;
                } elseif (is_string($databaseSchema)) {
                    return ((new isys_component_dao_ocs($this->container->database))->get_ocs_db_id_by_schema($databaseSchema) ?: null);
                }
            } else {
                return isys_tenantsettings::get('ocs.default.db', null);
            }
        }

        return false;
    }

    public function __construct($name = null)
    {
        $this->m_objtype_blacklist = filter_defined_constants([
            'C__OBJTYPE__PERSON',
            'C__OBJTYPE__PERSON_GROUP',
            'C__OBJTYPE__ORGANIZATION'
        ]);
        parent::__construct($name);
    }
}

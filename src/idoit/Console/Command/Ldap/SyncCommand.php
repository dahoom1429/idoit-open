<?php

namespace idoit\Console\Command\Ldap;

use idoit\Component\Helper\Memory;
use idoit\Console\Command\AbstractCommand;
use idoit\Console\Exception\MissingModuleException;
use idoit\Context\Context;
use idoit\Module\Cmdb\Search\Index\Signals;
use isys_caching;
use isys_cmdb_dao;
use isys_cmdb_dao_category_g_contact;
use isys_cmdb_dao_category_s_person_master;
use isys_component_signalcollection;
use isys_event_manager;
use isys_exception_validation;
use isys_factory;
use isys_format_json;
use isys_helper_crypt;
use isys_import_handler_cmdb;
use isys_ldap_dao;
use isys_module_ldap;
use isys_tenantsettings;
use ldapi;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class SyncCommand extends AbstractCommand
{
    const NAME = 'ldap-sync';

    /**
     * @var array
     */
    private const GROUP_CLASSES = ['group', 'posixgroup'];

    /**
     * @var array
     */
    private const PERSON_CLASSES = ['person', 'inetorgperson'];

    /**
     * @var string
     */
    private const GROUPS_ARCHIVE = 'archive';

    /**
     * @var string
     */
    private const GROUPS_DELETE = 'delete';

    /**
     * @var  integer
     */
    private $m_default_company = null;

    /**
     * @var  array
     */
    private $m_room = [];

    /**
     * Configuration
     *
     * @var array
     */
    private $ldapConfig = [];

    /**
     * Start time of sync
     *
     * @var int
     */
    private $m_start_time = 0;

    /**
     * Contains all users whose status will be ignored in the Reactivation
     *
     * @var array
     */
    private $ignoreUserStatus = [];

    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * Call defined configured ignoreFunction via callLanguageConstructFunction
     *
     * @var bool
     */
    private $callLanguageConstruct = false;

    /**
     * Call defined configured ignoreFunction via call_user_func
     *
     * @var bool
     */
    private $callCallableFunction = false;

    /**
     * Contains language constructs which are supported and make sense
     *
     * @var array
     */
    private static $languageConstructs = [
        'empty',
        '!empty',
        'isset',
        '!isset'
    ];

    /**
     * Contains unique attributes for each server if defined
     *
     * @var array
     */
    private $uniqueAttributes = [];

    /**
     * @var int|null
     */
    private $defaultPersonObjectType = null;

    /**
     * @var array
     */
    private $preSyncUser2GroupMapping = [];

    /**
     * return int
     */
    private function getPersonObjectType()
    {
        if ($this->defaultPersonObjectType === null) {
            $this->defaultPersonObjectType = defined_or_default(isys_cmdb_dao_category_s_person_master::instance($this->container->get('database'))->getDefaultPersonObjectType()) ?:
                defined_or_default('C__OBJTYPE__PERSON');
        }

        return $this->defaultPersonObjectType;
    }

    /**
     * @param $languageConstruct
     * @param $value
     *
     * @return bool
     */
    private function callLanguageConstructFunction($languageConstruct, $value)
    {
        switch ($languageConstruct) {
            case 'empty':
                return empty($value);
            case '!empty':
                return !empty($value);
            case 'isset':
                return isset($value);
            case '!isset':
                return !isset($value);
            default:
                return false;
        }
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
     * Get description for command
     *
     * @return string
     */
    public function getCommandDescription()
    {
        return 'Synchronizes LDAP user accounts with i-doit user objects';
    }

    /**
     * Retrieve Command InputDefinition
     *
     * @return InputDefinition
     */
    public function getCommandDefinition()
    {
        $definition = new InputDefinition();
        $definition->addOption(new InputOption(
            'ldapServerId',
            'l',
            InputOption::VALUE_REQUIRED,
            'Configuration Id of the server that should be synced with, else every configured server will be synced'
        ));

        $definition->addOption(new InputOption(
            'dumpConfig',
            null,
            InputOption::VALUE_NONE,
            'Dump used LDAP config and exit command after'
        ));

        $definition->addOption(new InputOption(
            'connectionRankingActive',
            null,
            InputOption::VALUE_OPTIONAL,
            "Configuration which reactivates all connections from all reactivated Users. \n<comment>Default configuration is expert setting 'ldap.connection-ranking-active' (Tenant-wide) with value '1'</comment>."
        ));

        $definition->addOption(new InputOption(
            'dropExistingRelations',
            null,
            InputOption::VALUE_OPTIONAL,
            "If an existing ldap group has group member users, outside of these synced users, those will be purged. \n1 = drop existing relations, 0 = ignore existing relations",
            0
        ));

        $definition->addOption(new InputOption(
            'archiveDeletedGroups',
            null,
            InputOption::VALUE_OPTIONAL,
            "If a deleted ldap group remains in i-doit, archive or delete it.",
            false
        ));

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

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;

        $outputStyle = new OutputFormatterStyle('yellow', 'black', ['bold']);
        $this->output->getFormatter()->setStyle('warning', $outputStyle);

        if (!empty($this->config)) {
            $this->ldapConfig = $this->config;
            $this->output->writeln('Uses LDAP Settings from input .ini file ');
        } else {
            if (isys_tenantsettings::has('ldap.config')) {
                $this->ldapConfig = isys_tenantsettings::get('ldap.config') ?: [];
                $this->output->writeln('Uses LDAP Tenant settings');
            } else {
                $this->output->writeln('No LDAP Settings specified');
            }
        }

        if (is_string($this->ldapConfig)) {
            $this->ldapConfig = isys_format_json::decode($this->ldapConfig);
        }

        if (isset($this->ldapConfig['rooms']) && is_array($this->ldapConfig['rooms']) && count($this->ldapConfig['rooms'])) {
            foreach ($this->ldapConfig['rooms'] as $roomName => $contacts) {
                $roomName = trim($roomName);

                if ($roomName === '') {
                    $this->output->writeln('<error>!!</error> Skipped a room with no name (please check your config).');
                    continue;
                }

                $this->output->writeln('<info>Processing users for room</info> <comment>' . $roomName . '</comment>', OutputInterface::VERBOSITY_DEBUG);

                if (!isys_format_json::is_json_array($contacts)) {
                    $this->output->writeln('<error>!!</error> Contacts need to be provided in a certain syntax <comment>["user a", "user b"]</comment>', OutputInterface::VERBOSITY_VERBOSE);
                    continue;
                }

                $this->m_room[$roomName] = (array)isys_format_json::decode($contacts);
                $this->output->writeln(' > Users <comment>' . implode('</comment>, <comment>', $this->m_room[$roomName]) . '</comment>', OutputInterface::VERBOSITY_VERBOSE);
            }
        }

        if ($input->getOption('dumpConfig')) {
            echo json_encode($this->ldapConfig, JSON_PRETTY_PRINT);
            echo PHP_EOL;
            echo json_encode($this->m_room, JSON_PRETTY_PRINT);

            return;
        }

        if (isset($this->ldapConfig['ignoreFunction'])) {
            if (in_array($this->ldapConfig['ignoreFunction'], self::$languageConstructs)) {
                $this->callLanguageConstruct = true;
            }

            if (is_callable($this->ldapConfig['ignoreFunction'])) {
                $this->callCallableFunction = true;
            }
        }

        $this->ldapConfig['connectionRankingActive'] = (bool) (($input->getOption('connectionRankingActive') !== null)
            ? $input->getOption('connectionRankingActive')
            : isys_tenantsettings::get('ldap.connection-ranking-active', 1));
        $this->ldapConfig['dropExistingRelations'] = 0;

        $dropExistingRelations = $input->getOption('dropExistingRelations');

        // option is set but with no value or option is set with value 1
        if ($dropExistingRelations === null || (int)$dropExistingRelations === 1) {
            $this->ldapConfig['dropExistingRelations'] = 1;
        }

        $archiveDeletedGroups = $input->getOption('archiveDeletedGroups');
        if ($archiveDeletedGroups === false) {
            // option is not passed
            $this->ldapConfig['archiveDeletedGroups'] = null;
        } elseif ($archiveDeletedGroups === null) {
            // option is set but with no value
            $this->ldapConfig['archiveDeletedGroups'] = self::GROUPS_ARCHIVE;
        } else {
            // option is set with value
            $this->ldapConfig['archiveDeletedGroups'] = $archiveDeletedGroups;
            $arrayValues = [self::GROUPS_ARCHIVE, self::GROUPS_DELETE];
            if (!in_array($this->ldapConfig['archiveDeletedGroups'], $arrayValues)) {
                $this->output->writeln('<error>Possible archiveDeletedGroups option values are only "archive" or "delete"</error>');
                return Command::FAILURE;
            }
        }

        try {
            $this->sync($input->getOption('ldapServerId'));
        } catch (\Exception $exception) {
            $this->output->writeln('<error>' . $exception->getMessage() . '</error>');
            return Command::FAILURE;
        }
        return Command::SUCCESS;
    }

    /**
     * Start the sync job.
     *
     * @param int $l_server_id
     * @param int $p_forceStatus
     *
     * @throws MissingModuleException
     * @throws \Exception
     * @throws \idoit\Exception\OutOfMemoryException
     * @throws \isys_exception_dao
     * @throws \isys_exception_database
     * @throws \isys_exception_ldap
     */
    private function sync($l_server_id = null, $p_forceStatus = C__RECORD_STATUS__NORMAL)
    {
        $l_memory = Memory::instance();
        $this->m_start_time = time();
        $regenerateSearchIndex = false;

        if (!class_exists("isys_module_ldap")) {
            throw new MissingModuleException("LDAP Module not installed! Please (re-)install via the update manager and latest i-doit update.");
        }

        /**
         * Disconnect the onAfterCategoryEntrySave event to not always reindex the object in every category
         * This is extremely important!
         *
         * An Index is done for all objects at the end of the request, if enabled via parameter.
         */
        Signals::instance()
            ->disconnectOnAfterCategoryEntrySave();

        Context::instance()
            ->setContextTechnical(Context::CONTEXT_LDAP_SYNC)
            ->setGroup(Context::CONTEXT_GROUP_IMPORT)
            ->setContextCustomer(Context::CONTEXT_LDAP_SYNC)
            ->setImmutable(true);

        $l_ldap_module = new isys_module_ldap();
        $l_ldap_dao = new isys_ldap_dao($this->container->database);
        $l_person_dao = new isys_cmdb_dao_category_s_person_master($l_ldap_dao->get_database_component());
        $l_dao = new isys_cmdb_dao($l_ldap_dao->get_database_component());
        $l_person_group_dao = new \isys_cmdb_dao_category_s_person_group($l_ldap_dao->get_database_component());
        $l_servers = $l_ldap_dao->get_active_servers($l_server_id);

        if ($l_servers->num_rows() == 0) {
            throw new RuntimeException("No LDAP server configured.");
        }

        $l_synced_users = [];
        $l_synced_groups = [];
        $syncedPersons = [];
        $controls = [];

        while ($l_row = $l_servers->get_row()) {
            $l_hostname = $l_row["isys_ldap__hostname"];
            $l_port = $l_row["isys_ldap__port"];
            $l_dn = $l_row["isys_ldap__dn"];
            $l_filter = $l_row["isys_ldap__filter"];
            $l_password = isys_helper_crypt::decrypt($l_row["isys_ldap__password"]);
            $l_mapping = unserialize($l_row["isys_ldap_directory__mapping"]);
            $l_recursive = (int)$l_row['isys_ldap__recursive'];
            $l_tls = $l_row['isys_ldap__tls'];
            $l_version = (int)$l_row['isys_ldap__version'] ?: 3;
            $l_disabled = 0;
            $pageLimit = (int)$l_row['isys_ldap__page_limit'];
            $pagedResultEnabled = (bool)$l_row['isys_ldap__enable_paging'];

            // Set map which object ids should be ignored in status update
            $this->output->writeln('Syncing LDAP-Server <info>' . $l_hostname . '</info> (<comment>' . $l_row["isys_ldap_directory__title"] . '</comment>)');

            if ($pagedResultEnabled === false) {
                $this->output->writeln('Option "<error>Enable LDAP Paging</error>" is deactivated using <warning>sizelimit</warning> configuration from the Server.');
            } else {
                $this->output->writeln('Option "<warning>Enable LDAP Paging</warning>" is activated using configured <warning>LDAP Page Limit (' . $pageLimit . ')</warning> from configuration.');
            }

            $l_coninfo = null;
            $l_ldap_lib = $l_ldap_module->get_library($l_hostname, $l_dn, $l_password, $l_port, $l_version, $l_tls);
            if (PHP_VERSION_ID < ldapi::PHP_VERSION_LDAP_PAGING_DEPRECATION) {
                $pagedResultEnabled = (bool)$l_row['isys_ldap__enable_paging'] && $l_ldap_lib->isPagedResultSupported();
            }
            $ldapConnection = $l_ldap_lib->get_connection();

            // First disable all found users
            if ($l_row['isys_ldap_directory__const'] === 'C__LDAP__AD') {
                $pagedResultCookie = '';

                do {
                    if (PHP_VERSION_ID < ldapi::PHP_VERSION_LDAP_PAGING_DEPRECATION) {
                        if ($pagedResultEnabled) {
                            $l_ldap_lib->ldapControlPagedResult($pageLimit, true, $pagedResultCookie);
                        }
                        $l_search = $l_ldap_lib->search(
                            $l_row["isys_ldap__user_search"],
                            "(&(userAccountControl:1.2.840.113556.1.4.803:=2)(objectclass=user))",
                            [],
                            0,
                            null,
                            null,
                            null,
                            $l_recursive
                        );
                    } else {
                        $l_ldap_lib->set_option(LDAP_OPT_PROTOCOL_VERSION, 3);
                        $l_ldap_lib->set_option(LDAP_OPT_REFERRALS, false);

                        if ($pagedResultEnabled) {
                            $controls = [
                                LDAP_CONTROL_PAGEDRESULTS => [ // phpcs:ignore
                                    'oid'        => LDAP_CONTROL_PAGEDRESULTS, // phpcs:ignore
                                    'isCritical' => true,
                                    'value'      => [
                                        'size'   => $pageLimit,
                                        'cookie' => $pagedResultCookie,
                                    ],
                                ],
                            ];
                            $l_ldap_lib->set_option(LDAP_OPT_SERVER_CONTROLS, $controls);
                        }

                        $l_search = $l_ldap_lib->search(
                            $l_row["isys_ldap__user_search"],
                            "(&(userAccountControl:1.2.840.113556.1.4.803:=2)(objectclass=user))",
                            [],
                            0,
                            null,
                            null,
                            null,
                            $l_recursive,
                            $controls
                        );
                    }

                    if ($l_search) {
                        $l_attributes = $l_ldap_lib->get_entries($l_search);

                        if ($l_attributes['count'] === null) {
                            $l_disabled = 0;
                        } else {
                            $l_disabled = $l_attributes['count'];
                        }

                        for ($l_i = 0; $l_i <= $l_attributes["count"]; $l_i++) {
                            if (!isset($l_attributes[$l_i]["dn"])) {
                                continue;
                            }

                            $l_username = $l_attributes[$l_i][strtolower($l_mapping[C__LDAP_MAPPING__USERNAME])][0];

                            if (!$l_username) {
                                continue;
                            }

                            // @see  ID-6754  Convert `objectSID` to a readable value.
                            if (isset($l_attributes[$l_i]['objectsid'][0]) && !empty($l_attributes[$l_i]['objectsid'][0])) {
                                $l_attributes[$l_i]['objectsid'][0] = isys_module_ldap::convertSID($l_attributes[$l_i]['objectsid'][0]);
                            }

                            // @see  ID-6754  Convert `objectGUID` to a readable value.
                            if (isset($l_attributes[$l_i]['objectguid'][0]) && !empty($l_attributes[$l_i]['objectguid'][0])) {
                                $l_attributes[$l_i]['objectguid'][0] = $this->convertGUID($l_attributes[$l_i]['objectguid'][0]);
                            }

                            $uniqueAttribute = strtolower($l_row['isys_ldap__unique_attribute']);
                            if (!empty($l_row['isys_ldap__unique_attribute']) && isset($l_attributes[$l_i][$uniqueAttribute])) {
                                if (!isset($this->uniqueAttributes[$l_row["isys_ldap__id"]])) {
                                    $this->uniqueAttributes[$l_row["isys_ldap__id"]] = $l_ldap_module->getCustomPropertyDbField($l_row['isys_ldap__unique_attribute']);
                                }

                                $uniqueAttributeValue = $l_attributes[$l_i][$uniqueAttribute][0];
                                $userData = $l_person_dao->getPersonByCustomProperty(
                                    $this->uniqueAttributes[$l_row["isys_ldap__id"]],
                                    $uniqueAttributeValue
                                )->get_row();
                            } else {
                                $userData = $l_person_dao->get_person_by_username($l_username)->get_row();
                            }

                            // @See ID-4359 archive users only if the local object has no constant set
                            if ($userData && empty($userData['isys_obj__const'])) {
                                $this->ignoreUserStatus[$l_username] = true;
                                $userObjectId = $userData['isys_obj__id'];

                                $userStatus = C__RECORD_STATUS__ARCHIVED;
                                $disableLogin = 0;

                                if ($this->ldapConfig['disabledUsersBehaviour'] === 'delete') {
                                    $userStatus = C__RECORD_STATUS__DELETED;
                                } elseif ($this->ldapConfig['disabledUsersBehaviour'] === 'disable_login') {
                                    $userStatus = C__RECORD_STATUS__NORMAL;
                                    $disableLogin = 1;
                                }

                                // @See ID-6735 archive connections to this person object
                                $this->setObjectStatus($l_person_dao, $userObjectId, $userStatus);

                                if ($this->ldapConfig['disabledUsersBehaviour'] === 'disable_login' && $disableLogin === 1) {
                                    $l_sql = 'UPDATE isys_cats_person_list
                                              SET isys_cats_person_list__disabled_login = 1
                                              WHERE isys_cats_person_list__id = ' . $userData['isys_cats_person_list__id'];

                                    $l_person_dao->update($l_sql) && $l_person_dao->apply_update();
                                }
                            }
                        }
                    }

                    if (PHP_VERSION_ID < ldapi::PHP_VERSION_LDAP_PAGING_DEPRECATION) {
                        if ($pagedResultEnabled) {
                            $l_ldap_lib->ldapControlPagedResultResponse($l_search, $pagedResultCookie);
                        }
                    } else {
                        $l_ldap_lib->ldapParseResult($l_search, $errcode, $matcheddn, $errmsg, $referrals, $serverControls);
                        if (isset($serverControls[LDAP_CONTROL_PAGEDRESULTS]['value']['cookie'])) { // phpcs:ignore
                            // You need to pass the cookie from the last call to the next one
                            $pagedResultCookie = $serverControls[LDAP_CONTROL_PAGEDRESULTS]['value']['cookie']; // phpcs:ignore
                        } else {
                            $pagedResultCookie = '';
                        }
                    }
                } while ($pagedResultCookie !== null && $pagedResultCookie != '');
            }

            // Synchronize all users which are found via DN String
            if (!empty($l_mapping[C__LDAP_MAPPING__LASTNAME])) {
                /*
                 * Remove all ldap_dn entries for this ldap server
                 *  - This is used to identify deleted users later on
                 */
                $l_sql = "UPDATE isys_cats_person_list
                    SET isys_cats_person_list__ldap_dn = ''
                    WHERE isys_cats_person_list__isys_ldap__id = " . $l_person_dao->convert_sql_id($l_row["isys_ldap__id"]) . ';';

                $l_person_dao->update($l_sql);
                $pagedResultCookie = '';
                $l_existingGroups = [];
                $idoitSavedGroupsIDs = $l_person_group_dao->getAllGroupsNotPredefined();
                do {
                    if (PHP_VERSION_ID < ldapi::PHP_VERSION_LDAP_PAGING_DEPRECATION) {
                        if ($pagedResultEnabled) {
                            $l_ldap_lib->ldapControlPagedResult($pageLimit, true, $pagedResultCookie);
                        }
                        $l_search = $l_ldap_lib->search(
                            $l_row["isys_ldap__user_search"],
                            $l_filter,
                            [],
                            0,
                            null,
                            null,
                            null,
                            $l_recursive
                        );
                    } else {
                        $l_ldap_lib->set_option(LDAP_OPT_PROTOCOL_VERSION, 3);
                        $l_ldap_lib->set_option(LDAP_OPT_REFERRALS, false);

                        if ($pagedResultEnabled) {
                            $controls = [
                                LDAP_CONTROL_PAGEDRESULTS => [ // phpcs:ignore
                                    'oid'        => LDAP_CONTROL_PAGEDRESULTS, // phpcs:ignore
                                    'isCritical' => true,
                                    'value'      => [
                                        'size'   => $pageLimit,
                                        'cookie' => $pagedResultCookie,
                                    ],
                                ],
                            ];
                            $l_ldap_lib->set_option(LDAP_OPT_SERVER_CONTROLS, $controls);
                        }

                        $l_search = $l_ldap_lib->search(
                            $l_row["isys_ldap__user_search"],
                            $l_filter,
                            [],
                            0,
                            null,
                            null,
                            null,
                            $l_recursive,
                            $controls
                        );
                    }

                    if ($l_search) {
                        if (PHP_VERSION_ID < ldapi::PHP_VERSION_LDAP_PAGING_DEPRECATION) {
                            if ($pagedResultEnabled) {
                                $l_ldap_lib->ldapControlPagedResultResponse($l_search, $pagedResultCookie);
                            }
                        } else {
                            $l_ldap_lib->ldapParseResult($l_search, $errcode, $matcheddn, $errmsg, $referrals, $serverControls);
                            if (isset($serverControls[LDAP_CONTROL_PAGEDRESULTS]['value']['cookie'])) { // phpcs:ignore
                                $pagedResultCookie = $serverControls[LDAP_CONTROL_PAGEDRESULTS]['value']['cookie']; // phpcs:ignore
                            } else {
                                $pagedResultCookie = '';
                            }
                        }

                        $l_attributes = $l_ldap_lib->get_entries($l_search);
                        for ($l_i = 0; $l_i < $l_attributes["count"]; $l_i++) {
                            // Break if memory consumption is too high
                            $l_memory->outOfMemoryBreak(8192);

                            if (!isset($l_attributes[$l_i]["dn"])) {
                                continue;
                            }

                            $l_attributes[$l_i]["ldapi"] = &$l_ldap_lib;
                            $l_attributes[$l_i]["ldap_data"] = &$l_row;

                            // Force the "objectclass" to be an array, consisting of lowercase values.
                            $objectClass = array_map('strtolower', (array)$l_attributes[$l_i]['objectclass']);

                            if (!empty(array_intersect(self::GROUP_CLASSES, $objectClass))) {
                                $objectSID = isys_module_ldap::convertSID($l_attributes[$l_i]['objectsid'][0]);
                                $RID_arr = explode('-', $objectSID);
                                $RID = $RID_arr[count($RID_arr) - 1];
                                $l_ldapGroupName = $l_attributes[$l_i]['samaccountname']['0'];
                                $l_dn = $l_attributes[$l_i]["dn"];
                                if (!$l_person_group_dao->isGroupWithLdapSIDExist($objectSID, $l_ldapGroupName) && !empty($l_ldapGroupName)) {
                                    $syncedGroupObjectID = null;
                                    try {
                                        if ($syncedGroupObjectID = $this->sync_group(
                                            $objectSID,
                                            $l_ldapGroupName,
                                            $l_dao,
                                            $l_person_group_dao,
                                            $l_mapping,
                                            $l_row["isys_ldap__id"],
                                            $l_ldap_module,
                                            $l_row,
                                            $p_forceStatus
                                        )) {
                                            $this->output->writeln('Group <info>' . $l_attributes[$l_i]["dn"] . '</info> synchronized.');
                                        } else {
                                            $this->output->writeln('Failed synchronizing group: <error>' . $l_attributes[$l_i]["dn"] . '</error>');
                                        }
                                    } catch (isys_exception_validation $e) {
                                        $this->output->writeln('Validation for <error>' . $l_attributes[$l_i]["dn"] . '</error> failed: ' . $e->getMessage());
                                    }
                                    if ($syncedGroupObjectID) {
                                        $l_synced_groups[$l_dn] = [
                                            'RID' => $RID,
                                            'objectId' => $syncedGroupObjectID,
                                            'title' => $l_attributes[$l_i]['samaccountname']['0'],
                                        ];
                                    }
                                } else {
                                    $groupID = $l_person_group_dao->getGroupIDByLdapSyncID($objectSID, $l_ldapGroupName);
                                    $existedGroupTitle = $l_dao->get_obj_name_by_id_as_string($groupID);
                                    $newGroupTitle = $l_attributes[$l_i]['samaccountname']['0'];
                                    if ($existedGroupTitle !== $newGroupTitle) {
                                        $l_dao->update_object($groupID, null, $newGroupTitle);
                                        $this->output->writeln('Group with DN: <warning>' . $l_attributes[$l_i]["dn"] . '</warning> already exists and was renamed.');
                                    } else {
                                        $this->output->writeln('Group with DN: <warning>' . $l_attributes[$l_i]["dn"] . '</warning> already exists.');
                                    }
                                    $l_existingGroups[$l_dn] = [
                                        'RID' => $RID,
                                        'objectId' => $groupID,
                                        'title' => $newGroupTitle,
                                    ];
                                }
                            } elseif (!empty(array_intersect(self::PERSON_CLASSES, $objectClass))) {
                                $syncedPersonObjectID = null;
                                try {
                                    if ($syncedPersonObjectID = $this->sync_user(
                                        $l_attributes[$l_i],
                                        $l_person_dao,
                                        $l_mapping,
                                        $l_row["isys_ldap__id"],
                                        $l_ldap_module,
                                        $l_row,
                                        $p_forceStatus
                                    )) {
                                        $this->output->writeln('User <info>' . $l_attributes[$l_i]["dn"] . '</info> synchronized.');
                                    } else {
                                        $this->output->writeln('Failed synchronizing user: <error>' . $l_attributes[$l_i]["dn"] . '</error>');
                                    }
                                } catch (isys_exception_validation $e) {
                                    $this->output->writeln('Validation for <error>' . $l_attributes[$l_i]["dn"] . '</error> failed: ' . $e->getMessage());
                                }
                                if ($syncedPersonObjectID) {
                                    $syncedPersons[] = [
                                        'ObjectID' => $syncedPersonObjectID,
                                        'dn' => $l_attributes[$l_i]["dn"],
                                        'title' => $l_attributes[$l_i]['samaccountname']['0'],
                                        'memberof' => $l_attributes[$l_i]['memberof'],
                                        'primaryGroupsIds' => $l_attributes[$l_i]['primarygroupid'],
                                    ];
                                }
                            }
                        }
                    } else {
                        $this->output->writeln('<error>Ldap search err</error>');
                        $pagedResultCookie = '';
                    }
                } while ($pagedResultCookie !== null && $pagedResultCookie != '');

                $ldapPassedExistingInIdoitGroupsIDs = [];
                foreach ($l_existingGroups as $group) {
                    $ldapPassedExistingInIdoitGroupsIDs[] = $group['objectId'];
                }

                if ($this->ldapConfig['archiveDeletedGroups'] === self::GROUPS_ARCHIVE) {
                    $groupsIDsToRemove = array_diff($idoitSavedGroupsIDs, $ldapPassedExistingInIdoitGroupsIDs);
                    foreach ($groupsIDsToRemove as $groupID) {
                        $l_dao->set_object_status($groupID, C__RECORD_STATUS__ARCHIVED);
                        $this->output->writeln('Group with ID: <warning>' . $groupID . '</warning> was archived.');
                    }
                } elseif ($this->ldapConfig['archiveDeletedGroups'] === self::GROUPS_DELETE) {
                    $groupsIDsToRemove = array_diff($idoitSavedGroupsIDs, $ldapPassedExistingInIdoitGroupsIDs);
                    foreach ($groupsIDsToRemove as $groupID) {
                        $l_dao->delete_object_and_relations($groupID);
                        $this->output->writeln('Group with ID: <warning>' . $groupID . '</warning> was purged.');
                    }
                }

                $l_synced_groups = array_merge($l_synced_groups, $l_existingGroups);

                $systemParamsDefaultGroupsIdsString = isys_tenantsettings::get('ldap.default-group', '');
                $systemParamsDefaultGroupsIds = [];
                if (!empty($systemParamsDefaultGroupsIdsString)) {
                    $systemParamsDefaultGroupsIds = explode(',', $systemParamsDefaultGroupsIdsString);
                }

                $personGroupMembersDao = new \isys_cmdb_dao_category_s_person_group_members($l_ldap_dao->get_database_component());
                $personGroupMembershipDao = new \isys_cmdb_dao_category_s_person_assigned_groups($l_ldap_dao->get_database_component());
                $language = \isys_application::instance()->container->get('language');

                // dropping existing relations if command param --dropExistingRelations=1 is set
                if (($this->ldapConfig['dropExistingRelations'] === 1) && (!empty($l_existingGroups))) {
                    $this->output->writeln('Option <warning>"dropExistingRelations"</warning> is set.');
                    $builtInPersonsIDs = $personGroupMembersDao->getBuiltinPersonsIDs();
                    $groupAssignmentDrops = 0;
                    foreach ($l_existingGroups as $group) {
                        $groupObjectID = $group['objectId'];
                        $existingPersons = $l_person_group_dao->get_persons_by_id($groupObjectID)->__as_array();
                        if (!empty($existingPersons)) {
                            foreach ($existingPersons as $existingPerson) {
                                $existingPersonID = $existingPerson['isys_person_2_group__isys_obj__id__person'];
                                if (!in_array($existingPersonID, $builtInPersonsIDs)) {
                                    $this->output->writeln(
                                        '<info>Group ' . $group['title'] .
                                        ' detaching person ' . $existingPerson['isys_cats_person_list__title'] .
                                        '</info>'
                                    );
                                    $personGroupMembersDao->detach_person($groupObjectID, $existingPersonID);
                                    $groupAssignmentDrops++;
                                }
                            }
                        }
                    }
                    $this->output->writeln('Dropped (' . $groupAssignmentDrops . ') assignments from existing groups.');
                }

                // mapping imported persons to imported groups
                if (!empty($l_synced_groups) || !empty($systemParamsDefaultGroupsIds)) {
                    foreach ($syncedPersons as $person) {
                        $personID = $person['ObjectID'];
                        $memberof = $person['memberof'];
                        $memberofCount = (int)$memberof['count'];
                        $groupsIDs = [];

                        // search user's primary group object id by it's RID
                        if ($person['primaryGroupsIds']) {
                            $count = (int)$person['primaryGroupsIds']['count'];
                            for ($i = 0; $i < $count; $i++) {
                                $key = (string)$i;
                                $RIDToSearch = $person['primaryGroupsIds'][$key];
                                foreach ($l_synced_groups as $group) {
                                    if ($group['RID'] === $RIDToSearch) {
                                        $groupsIDs[] = (int)$group['objectId'];
                                        break;
                                    }
                                }
                            }
                        }

                        // get user's groups objects ids from memberof person's prop
                        for ($i = 0; $i < $memberofCount; $i++) {
                            $key = (string)$i;
                            $groupDn = $memberof[$key];
                            if (isset($l_synced_groups[$groupDn])) {
                                $groupID = $l_synced_groups[$groupDn]['objectId'];
                                $groupsIDs[] = (int)$groupID;
                            }
                        }

                        // SystemSettings default groups ids exist, then attach also
                        if (!empty($systemParamsDefaultGroupsIds)) {
                            $groupsIDs = array_merge($groupsIDs, $systemParamsDefaultGroupsIds);
                        }

                        if (!empty($groupsIDs)) {
                            if ($this->ldapConfig['dropExistingRelations'] === 0) {
                                $groupsIDs = array_unique(array_merge($groupsIDs, $personGroupMembershipDao->getAssignedGroupsIDs($personID)));
                            }
                            $this->output->writeln('<info>Groups "' . implode(', ', $groupsIDs) . '" attaching person ID ' . $personID . '</info>');
                            $personGroupMembershipDao->attachObjects($personID, $groupsIDs);
                        }
                    }

                    foreach ($syncedPersons as $person) {
                        $personID = $person['ObjectID'];
                        $preGroupsIDs = $this->preSyncUser2GroupMapping[$personID];
                        $postGroupsIDs = $personGroupMembershipDao->getAssignedGroupsIDs($personID);
                        $removedGroupsIDs = array_diff($preGroupsIDs, $postGroupsIDs);
                        foreach ($removedGroupsIDs as $groupID) {
                            // the group was deleted
                            isys_event_manager::getInstance()
                                ->triggerCMDBEvent(
                                    'C__LOGBOOK_EVENT__CATEGORY_CHANGED',
                                    'Dependency dropped by ldap-sync',
                                    $personID,
                                    $personGroupMembershipDao->get_objTypeID($personID),
                                    $language->get('LC__CONTACT__TREE__GROUP_MEMBERS'),
                                    serialize([
                                        'isys_cmdb_dao_category_s_person_assigned_groups::connected_object' => [
                                            'from' => $personGroupMembershipDao->get_obj_name_by_id_as_string($groupID),
                                            'to'   => '',
                                        ],
                                    ]),
                                    $language->get('LC__DEPENDENCY__DELETED')
                                );
                        }
                    }
                }

                // Archive or delete all deleted users where ldap_dn = '', this means this user was not synced and should therefore not exist anymore.
                if (!isset($this->ldapConfig['deletedUsersBehaviour'])) {
                    $this->ldapConfig['deletedUsersBehaviour'] = 'archive';
                }

                if ($this->ldapConfig['deletedUsersBehaviour'] === 'delete') {
                    $l_deletedUserStatus = C__RECORD_STATUS__DELETED;
                } elseif ($this->ldapConfig['deletedUsersBehaviour'] === 'disable_login') {
                    $l_deletedUserStatus = C__RECORD_STATUS__NORMAL;
                } else {
                    $l_deletedUserStatus = C__RECORD_STATUS__ARCHIVED;
                }

                $l_sql = 'SELECT isys_obj__title, isys_obj__id
                    FROM isys_obj
                    INNER JOIN isys_cats_person_list ON isys_obj__id = isys_cats_person_list__isys_obj__id
                    WHERE isys_cats_person_list__isys_ldap__id = ' . $l_person_dao->convert_sql_id($l_row["isys_ldap__id"]) . '
                    AND isys_cats_person_list__ldap_dn = \'\';';

                $l_deletedUsers = $l_person_dao->retrieve($l_sql);
                $l_deletedUsersArray = [];

                while ($l_delRow = $l_deletedUsers->get_row()) {
                    $l_deletedUsersArray[(int)$l_delRow['isys_obj__id']] = $l_delRow['isys_obj__title'];
                }

                if (count($l_deletedUsersArray) > 0 && $l_deletedUserStatus > 0) {
                    $l_sql = 'UPDATE isys_obj
                        SET isys_obj__status = ' . $l_person_dao->convert_sql_int($l_deletedUserStatus) . '
                        WHERE isys_obj__id IN (' . implode(',', array_keys($l_deletedUsersArray)) . ');';

                    $l_person_dao->update($l_sql);

                    if ($this->ldapConfig['deletedUsersBehaviour'] === 'disable_login') {
                        $l_sql = 'UPDATE isys_cats_person_list
                            SET isys_cats_person_list__disabled_login = 1
                            WHERE isys_cats_person_list__isys_obj__id IN (' . implode(',', array_keys($l_deletedUsersArray)) . ');';

                        $l_person_dao->update($l_sql);
                    }

                    $l_ldap_module::debug('NOTICE: The following users were ' . ($this->ldapConfig['deletedUsersBehaviour'] === 'disable_login' ? 'login disabled' : $this->ldapConfig['deletedUsersBehaviour'] . 'd: ') . implode(
                        ', ',
                        $l_deletedUsersArray
                    ));

                    if ($this->ldapConfig['connectionRankingActive']) {
                        // @See ID-6735 archive connections to all person objects which are orphaned
                        \isys_cmdb_dao_connection::instance($l_ldap_dao->get_database_component())
                            ->unidirectionalConnectionRanking($l_person_dao, null, array_keys($l_deletedUsersArray));
                    }

                    $this->output->writeln('Found ' . count($l_deletedUsersArray) . ' orphaned user(s) which is/are ' . $this->ldapConfig['deletedUsersBehaviour'] .
                        'd now (deleted users in your directory)');
                } else {
                    $this->output->writeln('No deleted users found.');
                }

                unset($l_deletedUsersArray);
            }

            // Output which users are disabled
            if ($l_disabled > 0 && count($this->ignoreUserStatus)) {
                $this->output->writeln('Found <info>' . $l_disabled . ' disabled object(s)</info> inside ' . $l_row["isys_ldap__user_search"] . '.');

                foreach ($this->ignoreUserStatus as $username => $unused) {
                    // @see  ID-5092  Old "controller" style was used here.
                    $this->output->writeln("User <info>'" . $username . "'</info> " . ($this->ldapConfig['disabledUsersBehaviour'] === 'disable_login' ? 'login disabled' : $this->ldapConfig['disabledUsersBehaviour'] . 'd') . '.');
                }
            }

            // Regenerate Search Index
            if (($l_disabled > 0 || count($l_synced_users)) && !$regenerateSearchIndex) {
                $regenerateSearchIndex = true;
            }
        }

        // Regenerate search index only if necessary
        if ($regenerateSearchIndex) {
            $this->regenerate_search_index();
        }

        // Attach users to rooms.
        foreach ($this->m_room as $roomTitle => $roomUsers) {
            if (!is_array($roomUsers) || count($roomUsers) === 0) {
                $this->output->writeln('There are <info>no users</info> to assign to room: <comment>' . $roomTitle . '</comment>');

                continue;
            }

            $users = [];

            foreach ($roomUsers as $user) {
                if (!is_numeric($user)) {
                    $user = $l_person_dao
                        ->get_person_by_username($user)
                        ->get_row_value('isys_cats_person_list__isys_obj__id');
                }

                // Get object IDs for each user.
                $users[] = $user;
            }

            $this->connect_room($users, $roomTitle);

            $this->output->writeln('Adding <info>' . count($users) . ' users</info> to room: <comment>' . $roomTitle . '</comment>');
        }

        // Clear all found "auth-*" cache-files.
        try {
            $l_cache_files = isys_caching::find('auth-*');
            array_map(function (isys_caching $l_cache) {
                $l_cache->clear();
            }, $l_cache_files);
        } catch (\Exception $e) {
            $this->output->writeln('<error>An error occurred while clearing the cache files: ' . $e->getMessage() . '</error>');
        }
    }

    /**
     * @param     $p_SID string
     * @param     $p_groupName string
     * @param     $p_dao isys_cmdb_dao
     * @param     $p_person_group_dao \isys_cmdb_dao_category_s_person_group
     * @param     $p_mapping
     * @param     $p_ldap_server_id
     * @param     $p_ldap_module
     * @param     $p_serverdata
     * @param int $p_forceStatus
     */
    private function sync_group(
        $p_SID,
        $p_groupName,
        $p_dao,
        $p_person_group_dao,
        $p_mapping,
        $p_ldap_server_id,
        $p_ldap_module,
        $p_serverdata,
        $p_forceStatus = C__RECORD_STATUS__NORMAL
    ) {
        $l_objID = $p_dao->insert_new_obj(
            C__OBJTYPE__PERSON_GROUP,
            null,
            $p_groupName,
            null,
            C__RECORD_STATUS__NORMAL,
            null,
            null,
            false,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null
        );

        isys_event_manager::getInstance()->triggerCMDBEvent(
            'C__LOGBOOK_EVENT__OBJECT_CREATED',
            'Persons group created from ldap-sync',
            $l_objID,
            defined_or_default('C__OBJTYPE__PERSON_GROUP'),
            null,
            null,
            null,
            null,
            $p_groupName
        );


        $l_catdata = $p_person_group_dao->get_data(null, $l_objID)->get_row();
        $l_data_id = $l_catdata['isys_cats_person_group_list__id'];
        $p_person_group_dao->save(
            $l_data_id,
            $p_groupName,
            '',
            '',
            $p_SID,
            '',
            C__RECORD_STATUS__NORMAL
        );

        return $l_objID;
    }
    /**
     * Person is syncronized here. $p_attributes are ldap_search data attributes.
     *
     * @param array $p_attributes
     * @param isys_cmdb_dao_category_s_person_master $p_person_dao
     * @param array $p_mapping
     * @param integer $p_ldap_server_id
     * @param isys_module_ldap $p_ldap_module
     *
     * @param array $p_serverdata
     * @param int $p_forceStatus
     *
     * @return bool
     *
     * @throws \Exception
     * @throws \isys_exception_cmdb
     * @throws isys_exception_validation
     */
    private function sync_user(
        $p_attributes,
        $p_person_dao,
        $p_mapping,
        $p_ldap_server_id,
        $p_ldap_module,
        $p_serverdata,
        $p_forceStatus = C__RECORD_STATUS__NORMAL
    ) {
        if (isset($this->ldapConfig['defaultCompany']) && $this->ldapConfig['defaultCompany']) {
            $this->m_default_company = $this->ldapConfig['defaultCompany'];
        }

        if (empty($p_mapping[C__LDAP_MAPPING__USERNAME]) && empty($p_mapping[C__LDAP_MAPPING__FIRSTNAME])) {
            throw new RuntimeException('LDAP Mappings empty! Configure your LDAP-Mappings in System -> LDAP -> Directories');
        }

        // @see  ID-6754  Convert `objectSID` to a readable value.
        if (isset($p_attributes['objectsid'][0]) && !empty($p_attributes['objectsid'][0])) {
            $p_attributes['objectsid'][0] = isys_module_ldap::convertSID($p_attributes['objectsid'][0]);
        }

        // @see  ID-6754  Convert `objectGUID` to a readable value.
        if (isset($p_attributes['objectguid'][0]) && !empty($p_attributes['objectguid'][0])) {
            $p_attributes['objectguid'][0] = $this->convertGUID($p_attributes['objectguid'][0]);
        }

        $l_username = $p_attributes[strtolower($p_mapping[C__LDAP_MAPPING__USERNAME])][0];
        $l_firstname = $p_attributes[strtolower($p_mapping[C__LDAP_MAPPING__FIRSTNAME])][0];
        $l_lastname = $p_attributes[strtolower($p_mapping[C__LDAP_MAPPING__LASTNAME])][0];
        $l_mail = $p_attributes[strtolower($p_mapping[C__LDAP_MAPPING__MAIL])][0];

        if (isset($this->ldapConfig['ignoreUsersWithAttributes']) && is_array($this->ldapConfig['ignoreUsersWithAttributes']) && count($this->ldapConfig['ignoreUsersWithAttributes'])) {
            /*
             * INFO:
             *
             * This routine will run the defined "ignoreFunction" against each property
             * from "ignoreUsersWithAttributes". Users will be only ignored if "ignoreFunction"
             * returns true for each property. Otherwise the user will be syncronized.
             */
            $ignoreUser = true;

            foreach ($this->ldapConfig['ignoreUsersWithAttributes'] as $l_checkAttr) {
                if ($this->callLanguageConstruct) {
                    $ignoreUser = $this->callLanguageConstructFunction(
                        $this->ldapConfig['ignoreFunction'],
                        $p_attributes[$l_checkAttr][0]
                    );
                }

                if ($this->callCallableFunction) {
                    $ignoreUser = !call_user_func(
                        $this->ldapConfig['ignoreFunction'],
                        $p_attributes[$l_checkAttr][0],
                        $p_attributes
                    );
                }

                // Check whether "ignoreFunction" does not match property
                if ($ignoreUser === false) {
                    break;
                }
            }

            if ($ignoreUser) {
                $p_ldap_module->debug('ignoreFunction prohibited syncing user "<info>' . ($p_attributes['distinguishedname'] ?: $p_attributes['cn']) . '</info>"');

                throw new isys_exception_validation(
                    'ignoreFunction prohibited syncing user.',
                    $this->ldapConfig['ignoreUsersWithAttributes']
                );
            }
        }

        if ($l_username) {
            $uniqueAttribute = strtolower($p_serverdata['isys_ldap__unique_attribute']);
            if (!empty($p_serverdata['isys_ldap__unique_attribute']) && isset($p_attributes[$uniqueAttribute])) {
                $p_ldap_module->debug('Check if user <info>' . $l_username . '</info> exists with configured unique Attribute: "<comment>' . $p_serverdata['isys_ldap__unique_attribute'] . '</comment>"');
                if (!isset($this->uniqueAttributes[$p_ldap_server_id])) {
                    $this->uniqueAttributes[$p_ldap_server_id] = $p_ldap_module->getCustomPropertyDbField($p_serverdata['isys_ldap__unique_attribute']);
                }

                $uniqueAttributeValue = $p_attributes[$uniqueAttribute][0];

                $l_userdata = $p_person_dao->getPersonByCustomProperty(
                    $this->uniqueAttributes[$p_ldap_server_id],
                    $uniqueAttributeValue
                );
            } else {
                $l_userdata = $p_person_dao->get_person_by_username($l_username);
            }

            $l_user_created = false;

            if ($l_userdata->num_rows() <= 0) {
                $p_ldap_module->debug('User with username "<info>' . $l_username . '</info>" was not found. Creating..');
                $l_object_id = $p_person_dao->create(
                    null,
                    $l_username,
                    $l_firstname,
                    $l_lastname,
                    $l_mail,
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    $p_ldap_server_id,
                    $p_attributes['dn'],
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    ''
                );

                isys_event_manager::getInstance()->triggerCMDBEvent(
                    'C__LOGBOOK_EVENT__OBJECT_CREATED',
                    'object created from ldap-sync',
                    $l_object_id,
                    $this->getPersonObjectType(),
                    null,
                    null,
                    null,
                    null,
                    $l_firstname . ' ' . $l_lastname
                );

                // Set Object status to archived because the user is deactivated in Active Directory
                if (isset($this->ignoreUserStatus[$l_username])) {
                    $p_person_dao->set_object_status($l_object_id, C__RECORD_STATUS__ARCHIVED);
                }

                $l_userdata = $p_person_dao->get_person_by_username($l_username)->get_row();
                $l_user_id = $l_userdata['isys_cats_person_list__id'];
                $l_user_created = true;
            } else {
                $output = 'User with username "<info>' . $l_username . '</info>" found. Syncing..';

                $l_userdata = $l_userdata->get_row();
                $l_user_id = $l_userdata["isys_cats_person_list__id"];
                $l_object_id = $l_userdata['isys_cats_person_list__isys_obj__id'];

                if (isset($l_userdata['isys_cats_person_list__title']) && $l_userdata['isys_cats_person_list__title'] !== $l_username) {
                    $output .= ' Username has changed from "<info>' . $l_userdata['isys_cats_person_list__title'] . '</info>" to "<info>' . $l_username . '</info>".';
                }

                // Fixing object status (in case an object was re-activated in ldap again, or accidentally archived in i-doit)
                // Only update object status if its different or if Active Directory and is not in ignoredUserIdStatus or if config autoReactivateUsers is true
                if ($l_userdata['isys_obj__status'] != $p_forceStatus &&
                    ((isset($p_serverdata["isys_ldap_directory__const"]) && $p_serverdata["isys_ldap_directory__const"] === "C__LDAP__AD" &&
                            !isset($this->ignoreUserStatus[$l_username])) || $this->ldapConfig['autoReactivateUsers'])) {
                    $output .= ' User <comment>' . $l_username . '</comment> has been <info>reactivated</info>.';

                    // @See ID-6735 recycle connections to this person object
                    $this->setObjectStatus($p_person_dao, $l_object_id, $p_forceStatus);

                    isys_event_manager::getInstance()->triggerCMDBEvent(
                        'C__LOGBOOK_EVENT__OBJECT_RECYCLED',
                        'object status changed from ldap-sync',
                        $l_object_id,
                        $this->getPersonObjectType(),
                        null,
                        null,
                        null,
                        null,
                        $l_userdata['isys_obj__title']
                    );
                }

                $p_ldap_module->debug($output);
                // Setting login
                $p_person_dao->save_login(
                    $l_user_id,
                    $l_username,
                    null,
                    null,
                    $p_forceStatus,
                    false,
                    (isset($this->ignoreUserStatus[$l_username]) ? 1 : 0)
                );
            }

            try {
                // Get assigned groups of synced persons.
                $personGroupMembershipDao = new \isys_cmdb_dao_category_s_person_assigned_groups(\isys_application::instance()->container->get('database'));
                $this->preSyncUser2GroupMapping[$l_object_id] = $personGroupMembershipDao->getAssignedGroupsIDs($l_object_id);
            } catch (\Exception $e) {
                $this->preSyncUser2GroupMapping[$l_object_id] = [];
            }

            if ($l_user_id > 0) {
                $p_ldap_module->debug('Available attributes for this user: ' . implode(
                    ',',
                    array_filter(array_keys($p_attributes), function ($p_val) {
                        return !is_numeric($p_val) && $p_val !== 'count' && $p_val !== 'ldapi' && $p_val !== 'ldap_data';
                    })
                ));

                // Initialize category data array
                $l_category_data = [
                    'data_id' => $l_user_id,
                    'properties' => []
                ];

                /**
                 * Prepare current values
                 */
                foreach ($p_person_dao->get_properties() as $l_key => $l_property) {
                    if (isset($l_userdata[$l_property[C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD]])) {
                        if ($l_key === 'organization') {
                            $l_category_data['properties'][$l_key][C__DATA__VALUE] = $l_userdata['isys_connection__isys_obj__id'];
                        } else {
                            $l_category_data['properties'][$l_key][C__DATA__VALUE] = $l_userdata[$l_property[C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD]];
                        }
                    }
                }

                // Custom properties
                $l_custom_properties = $p_person_dao->get_custom_properties(true);

                foreach ($l_custom_properties as $l_key => $l_property) {
                    // Custom properties always have a title
                    $customPropertyTitle = strtolower($l_property[C__PROPERTY__INFO][C__PROPERTY__INFO__TITLE]);

                    if (isset($p_attributes[$customPropertyTitle])) {
                        $l_category_data['properties'][$l_key][C__DATA__VALUE] =
                            $p_attributes[$customPropertyTitle][0];
                    }
                }

                /* Override default properties coming from ldap */
                $l_category_data['properties']['id'] = [C__DATA__VALUE => $l_user_id];
                $l_category_data['properties']['first_name'] = [C__DATA__VALUE => $l_firstname];
                $l_category_data['properties']['last_name'] = [C__DATA__VALUE => $l_lastname];
                $l_category_data['properties']['ldap_dn'] = [C__DATA__VALUE => $p_attributes["dn"]];
                $l_category_data['properties']['ldap_id'] = [C__DATA__VALUE => $p_ldap_server_id];
                $l_category_data['properties']['mail'] = [C__DATA__VALUE => $l_mail];

                // Prepare 'syncable' attributes.
                if (isset($this->ldapConfig['attributes']) && is_array($this->ldapConfig['attributes'])) {
                    foreach ($this->ldapConfig['attributes'] as $l_idoitAttribute => $l_ldapAttribute) {
                        $l_ldapAttribute = strtolower($l_ldapAttribute);

                        if (!isset($l_category_data[$l_idoitAttribute])) {
                            if (isset($p_attributes[$l_ldapAttribute][0])) {
                                if ($l_idoitAttribute === 'salutation') {
                                    // ID-6441: Use settings to map ldap salutation to i-doit salutation
                                    $ldapSalutation = $p_attributes[$l_ldapAttribute][0];

                                    $p_attributes[$l_ldapAttribute][0] = -1;

                                    $manMapping = array_map('trim', explode(
                                        ',',
                                        isys_tenantsettings::get(
                                            'ldap.person.salutation.man',
                                            isys_tenantsettings::LDAP_SALUTATION_MAN_DEFAULT
                                        )
                                    ));
                                    $womanMapping = array_map('trim', explode(
                                        ',',
                                        isys_tenantsettings::get(
                                            'ldap.person.salutation.woman',
                                            isys_tenantsettings::LDAP_SALUTATION_WOMAN_DEFAULT
                                        )
                                    ));

                                    if (\in_array($ldapSalutation, $manMapping, true)) {
                                        $p_attributes[$l_ldapAttribute][0] = 'm';
                                    }

                                    if (\in_array($ldapSalutation, $womanMapping, true)) {
                                        $p_attributes[$l_ldapAttribute][0] = 'f';
                                    }
                                }

                                $l_category_data['properties'][$l_idoitAttribute][C__DATA__VALUE] = $p_attributes[$l_ldapAttribute][0];
                            } else {
                                $p_ldap_module->debug('Warning: LDAP Attribute "' . $l_ldapAttribute . '" was not found for user ' . $p_attributes["dn"]);
                            }
                        }
                    }
                }

                // Prepare organization assignment.
                if (isset($this->ldapConfig['attributes']['organization']) && isset($p_attributes[$this->ldapConfig['attributes']['organization']][0])) {
                    $l_company = $p_attributes[$this->ldapConfig['attributes']['organization']][0];
                } elseif ($this->m_default_company) {
                    $l_company = $this->m_default_company;
                }

                // Check if company is defined
                if (isset($l_company) && $l_company) {
                    if (!is_numeric($l_company) && defined('C__OBJTYPE__ORGANIZATION')) {
                        $l_orga_obj_types = $p_person_dao->get_objtype_ids_by_cats_id_as_array(defined_or_default('C__CATS__ORGANIZATION')) ?: C__OBJTYPE__ORGANIZATION;
                        $l_category_data['properties']['organization'][C__DATA__VALUE] = $p_person_dao->get_obj_id_by_title(
                            $l_company,
                            $l_orga_obj_types
                        );

                        if (!$l_category_data['properties']['organization'][C__DATA__VALUE]) {
                            $l_category_data['properties']['organization'][C__DATA__VALUE] = $p_person_dao->insert_new_obj(
                                C__OBJTYPE__ORGANIZATION,
                                false,
                                $l_company,
                                null,
                                C__RECORD_STATUS__NORMAL
                            );
                        }
                    } elseif (is_numeric($l_company) && $p_person_dao->obj_exists($l_company)) {
                        $l_category_data['properties']['organization'][C__DATA__VALUE] = $l_company;
                    }
                }

                // log changes of the person
                $personChanges = isys_factory::get_instance('isys_module_logbook')
                    ->prepare_changes($p_person_dao, $l_userdata, $l_category_data);

                if (is_countable($personChanges) && count($personChanges)) {
                    isys_event_manager::getInstance()
                        ->triggerCMDBEvent(
                            'C__LOGBOOK_EVENT__CATEGORY_CHANGED',
                            'modified from LDAP-Sync',
                            $l_object_id,
                            $l_userdata['isys_obj__isys_obj_type__id'],
                            $p_person_dao->getCategoryTitle(),
                            serialize($personChanges),
                            null,
                            null,
                            $l_userdata['isys_obj__title']
                        );
                }

                // Synchronize.
                $l_success = $p_person_dao->sync($l_category_data, $l_object_id, isys_import_handler_cmdb::C__UPDATE);

                // Emit category signal (afterCategoryEntrySave).
                isys_component_signalcollection::get_instance()
                    ->emit(
                        'mod.cmdb.afterCategoryEntrySave',
                        $p_person_dao,
                        $l_user_id,
                        $l_success,
                        $l_object_id,
                        $l_category_data,
                        []
                    );

                // Also sync room.
                if (isset($this->ldapConfig['attributes']['office'], $p_attributes[$this->ldapConfig['attributes']['office']][0])) {
                    $l_room_title = $p_attributes[$this->ldapConfig['attributes']['office']][0];

                    if ($this->ldapConfig["import_rooms"] && $l_room_title) {
                        $this->add_to_room($l_room_title, $l_object_id);
                    }
                }

                $language = \isys_application::instance()->container->get('language');

                // And corresponding groups.
                if ($l_userdata && is_array($l_userdata)) {
                    if (isset($l_userdata["isys_obj__id"]) && $l_userdata["isys_obj__id"] > 0) {
                        $ldapGroups = $p_ldap_module->ldap_get_groups($p_attributes);

                        if (count($ldapGroups)) {
                            $groupNames = array_map(function ($row) use ($language) {
                                return '<info>"' . $language->get($row['isys_obj__title']) . '"</info>';
                            }, $ldapGroups);

                            $this->output->writeln(
                                sprintf('Attaching user "<info>%s</info>" to group(s) %s.', $language->get($l_userdata["isys_obj__title"]), implode(', ', $groupNames)),
                                OutputInterface::VERBOSITY_VERY_VERBOSE
                            );

                            $p_ldap_module->attach_groups_to_user(
                                $l_userdata["isys_obj__id"],
                                $ldapGroups,
                                $p_person_dao,
                                $l_user_created
                            );
                        }
                    } else {
                        $p_ldap_module->debug('Could not attach user to groups. User ID was not found.');
                    }
                }

                $p_ldap_module->debug('Done: User ID is "' . $l_userdata["isys_obj__id"] . '" (Category ID: ' . $l_user_id . ')');

                // Mark synchronized object as changed
                $p_person_dao->object_changed($l_object_id);

                return $l_object_id;
            }

            $p_ldap_module->debug('Could not add user.');
        } else {
            $this->output->writeln("Username for DN: " . '<error>' . $p_attributes["dn"] . '</error>' . " is not defined!");
        }

        return false;
    }

    /**
     *
     * @param $p_room_key
     * @param $p_user_id
     */
    private function add_to_room($p_room_key, $p_user_id)
    {
        $this->m_room[$p_room_key][] = $p_user_id;
    }

    /**
     * @param \isys_cmdb_dao_category $dao
     * @param                         $object
     * @param                         $status
     *
     * @return bool|void
     */
    private function setObjectStatus(\isys_cmdb_dao_category $dao, $object, $status)
    {
        $dao->set_object_status($object, $status);

        if ($this->ldapConfig['connectionRankingActive']) {
            // @See ID-6735 archive connections to this person object
            return \isys_cmdb_dao_connection::instance($dao->get_database_component())
                ->unidirectionalConnectionRanking($dao, null, [$object]);
        }

        return true;
    }

    /**
     * @param array  $p_user_obj_id
     * @param string $p_room_title
     *
     * @return int|null
     * @throws \isys_exception_cmdb
     * @throws \isys_exception_dao
     */
    private function connect_room(array $p_user_obj_id, string $p_room_title)
    {
        $l_dao = isys_cmdb_dao::instance($this->container->get('database'));
        $l_object_id = $l_dao->get_obj_id_by_title($p_room_title, defined_or_default('C__OBJTYPE__ROOM'));

        if (!defined('C__CATG__CONTACT')) {
            return null;
        }

        if (empty($l_object_id)) {
            $l_object_id = $l_dao->insert_new_obj(
                defined_or_default('C__OBJTYPE__ROOM'),
                false,
                $p_room_title,
                null,
                C__RECORD_STATUS__NORMAL
            );
        }

        $l_cat = isys_cmdb_dao_category_g_contact::instance($this->container->get('database'));

        $l_persons_to_attach = [];

        foreach ($p_user_obj_id as $l_person_obj_id) {
            if (!$l_cat->check_contacts($l_person_obj_id, $l_object_id)) {
                $l_persons_to_attach[] = $l_person_obj_id;
            }
        }

        // Attach persons only if they are not already assigned
        if (count($l_persons_to_attach)) {
            $l_cat->attachObjects(
                $l_object_id,
                $l_persons_to_attach
            );
        }
    }

    /**
     * Regenerate search index for synced contacts
     */
    private function regenerate_search_index()
    {
        $this->output->writeln('Regenerating search index..');

        Signals::instance()->setOutput($this->output);
        Signals::instance()->onPostImport($this->m_start_time, [
            'C__CATG__CONTACT',
            'C__CATG__IP'
        ], [
            'C__CATS__ORGANIZATION',
            'C__CATS__PERSON_MASTER',
            'C__CATS__PERSON_GROUP_MASTER',
            'C__CATS__CLIENT'
        ], false);

        $this->output->writeln('Regenerated search index!');
    }

    /**
     * Converts a binary value GUID into a valid string.
     *
     * @param string $objectGUID
     *
     * @return string
     */
    private function convertGUID($objectGUID)
    {
        $hexGUID = bin2hex($objectGUID);
        $convertedGUID = '';

        for ($i = 1; $i <= 4; ++$i) {
            $convertedGUID .= substr($hexGUID, 8 - 2 * $i, 2);
        }

        $convertedGUID .= '-';

        for ($i = 1; $i <= 2; ++$i) {
            $convertedGUID .= substr($hexGUID, 12 - 2 * $i, 2);
        }

        $convertedGUID .= '-';

        for ($i = 1; $i <= 2; ++$i) {
            $convertedGUID .= substr($hexGUID, 16 - 2 * $i, 2);
        }

        $convertedGUID .= '-' . substr($hexGUID, 16, 4);
        $convertedGUID .= '-' . substr($hexGUID, 20);

        return strtoupper($convertedGUID);
    }
}

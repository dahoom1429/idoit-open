<?php

namespace idoit\Console\Command\Ldap;

use idoit\Console\Command\AbstractCommand;
use isys_cmdb_dao;
use isys_cmdb_dao_category_g_ldap_dn;
use isys_cmdb_dao_category_s_person_master;
use isys_helper_crypt;
use isys_ldap_dao;
use isys_module_ldap;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class SyncDistinguishedNamesCommand extends AbstractCommand
{
    const NAME = 'ldap-syncdn';

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
        return 'Synchronizes LDAP user DN attributes with i-doit user objects (Only needs to be run when migrating between different LDAP sources)';
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
            null,
            InputOption::VALUE_REQUIRED,
            'Configuration Id of the server that should be synced with, else every configured server will be synced'
        ));

        $definition->addOption(new InputOption('dnString', 'dnS', InputOption::VALUE_REQUIRED, 'E.g. OU=Servers,DC=Test,DC=int'));

        $definition->addOption(new InputOption('dnType', 'dnT', InputOption::VALUE_REQUIRED, 'Either contacts or objects', 'contacts'));

        $definition->addOption(new InputOption('objectType', 'o', InputOption::VALUE_REQUIRED, 'E.g. C__OBJTYPE__SERVER'));

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

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $l_fixit_type = $input->getOption('dnType');
            $l_ldap_dn = $input->getOption('dnString');

            if (empty($l_ldap_dn) && $l_fixit_type != 'contacts') {
                throw new \Exception("Please provide --dnString for command.");
            }

            if ($l_fixit_type == 'objects') {
                $l_dao = new isys_cmdb_dao($this->container->database);
                $l_dao_cat_dn = new isys_cmdb_dao_category_g_ldap_dn($this->container->database);

                $objectType = $input->getOption('objectType');

                if ($objectType) {
                    $l_sql = 'SELECT * FROM isys_obj WHERE isys_obj__isys_obj_type__id = (SELECT isys_obj_type__id FROM isys_obj_type WHERE isys_obj_type__const = ' .
                        $l_dao->convert_sql_text($objectType) . ')';
                } else {
                    $l_sql = 'SELECT * FROM isys_obj WHERE isys_obj__isys_obj_type__id IN
					(
						SELECT isys_obj_type_2_isysgui_catg__isys_obj_type__id FROM isys_obj_type_2_isysgui_catg
						INNER JOIN isysgui_catg ON isysgui_catg__id = isys_obj_type_2_isysgui_catg__isysgui_catg__id
						WHERE isysgui_catg__const = \'C__CATG__LDAP_DN\'
					)';
                }

                $l_res = $l_dao->retrieve($l_sql);

                while ($l_row = $l_res->get_row()) {
                    $l_obj_id = $l_row['isys_obj__id'];
                    $l_obj_title = $l_row['isys_obj__title'];
                    $l_dn_string = 'CN=' . $l_obj_title . ',' . $l_ldap_dn;
                    $l_res_dn = $l_dao_cat_dn->get_data(null, $l_obj_id);

                    if ($l_res_dn->num_rows() > 0) {
                        $l_dn_data = $l_res_dn->get_row();
                        if ($l_dn_data['isys_catg_ldap_dn_list__title'] == '') {
                            // Update dn string in category ldap dn
                            $l_update_sql = 'UPDATE isys_catg_ldap_dn_list SET isys_catg_ldap_dn_list__title = ' . $l_dao->convert_sql_text($l_dn_string) . '
						WHERE isys_catg_ldap_dn_list__id = ' . $l_dao->convert_sql_id($l_dn_data['isys_catg_ldap_dn_list__id']);
                        }
                    } else {
                        // insert new entry for category ldap dn
                        $l_update_sql = 'INSERT INTO isys_catg_ldap_dn_list (
						isys_catg_ldap_dn_list__title,
						isys_catg_ldap_dn_list__isys_obj__id,
						isys_catg_ldap_dn_list__status
					)
					VALUES
					(
						' . $l_dao->convert_sql_text($l_dn_string) . ',
						' . $l_dao->convert_sql_id($l_obj_id) . ',
						' . C__RECORD_STATUS__NORMAL . '
					)';
                    }
                    $l_dao->update($l_update_sql);
                }
                $l_dao->apply_update();
            } elseif ($l_fixit_type == 'contacts') {
                $l_ldap_module = new isys_module_ldap();
                $l_ldap_dao = new isys_ldap_dao($this->container->database);
                $l_dao_person = new isys_cmdb_dao_category_s_person_master($this->container->database);

                $host = $input->getOption('ldapServerId');

                if (!empty($host)) {
                    // Checks users in specified ldap host
                    $l_sql = "SELECT * FROM isys_ldap " . "LEFT JOIN isys_ldap_directory " . "ON " . "isys_ldap__isys_ldap_directory__id = " . "isys_ldap_directory__id " .
                        "WHERE isys_ldap__hostname = " . $l_ldap_dao->convert_sql_text($host);
                    $l_servers = $l_ldap_dao->retrieve($l_sql);
                } else {
                    // Checks users in all active ldap servers
                    $l_servers = $l_ldap_dao->get_active_servers();
                }

                if ($l_servers->num_rows() > 0) {
                    while ($l_row = $l_servers->get_row()) {
                        $l_hostname = $l_row["isys_ldap__hostname"];
                        $l_port = $l_row["isys_ldap__port"];
                        $l_dn = $l_row["isys_ldap__dn"];
                        $l_ldap_id = $l_row["isys_ldap__id"];
                        $l_password = isys_helper_crypt::decrypt($l_row["isys_ldap__password"]);
                        $l_mapping = unserialize($l_row["isys_ldap_directory__mapping"]);

                        try {
                            $l_ldap_lib = $l_ldap_module->get_library($l_hostname, $l_dn, $l_password, $l_port);

                            $l_res = $l_dao_person->get_data();
                            while ($l_row_person = $l_res->get_row()) {
                                $l_search = $l_ldap_lib->search(
                                    $l_row["isys_ldap__user_search"],
                                    "(&(" . $l_mapping[C__LDAP_MAPPING__USERNAME] . "=" . $l_row_person['isys_cats_person_list__title'] . ")(objectclass=user))"
                                );

                                if ($l_search) {
                                    $l_attributes = $l_ldap_lib->get_entries($l_search);
                                    if ($l_attributes['count'] > 0) {
                                        $output->writeln("Found User with username " . $l_row_person['isys_cats_person_list__title'] .
                                            " in ldap server. Synchronizing LDAP DN String...");

                                        $l_ldap_data = $l_attributes[0];
                                        if (isset($l_ldap_data['distinguishedname']) && $l_ldap_data['distinguishedname']['count'] > 0) {
                                            $l_ldap_dn_string = $l_ldap_data['distinguishedname'][0];
                                            $l_update = 'UPDATE isys_cats_person_list SET ' . 'isys_cats_person_list__isys_ldap__id = \'' . $l_ldap_id . '\', ' .
                                                'isys_cats_person_list__ldap_dn = \'' . $l_ldap_dn_string . '\' ' . 'WHERE isys_cats_person_list__id = ' .
                                                $l_dao_person->convert_sql_id($l_row_person['isys_cats_person_list__id']);
                                            $l_dao_person->update($l_update);
                                        }
                                    } else {
                                        $error = "User with username " . $l_row_person['isys_cats_person_list__title'] . " in ldap server does not exist. Skipped User";
                                        $output->writeln("<comment>$error</comment>");
                                        $l_ldap_module->debug("ERROR: $error");
                                    }
                                }
                            }
                        } catch (\Exception $e) {
                            $output->writeln('<error>' . $e->getMessage() . '</error>');
                        }
                    }
                    $l_dao_person->apply_update();
                }
            }
        } catch (\Exception $exception) {
            $output->writeln('<error>' . $exception->getMessage() . '</error>');
            return Command::FAILURE;
        }
        return Command::SUCCESS;
    }
}

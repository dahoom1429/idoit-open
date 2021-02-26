<?php

use idoit\Context\Context;

/**
 * AJAX
 *
 * @package     i-doit
 * @subpackage  General
 * @author      Van Quyen Hoang <qhoang@i-doit.org>
 * @version     1.0
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 * @since       0.9.9-9
 */
class isys_ajax_handler_ldap_import extends isys_ajax_handler
{
    /**
     * Init method, which gets called from the framework.
     *
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function init()
    {
        switch ($_GET['func']) {
            case 'filter':
                echo $this->process_filter();
                break;
            case 'import':
                $this->process_import();
                break;
        }

        // End the request.
        $this->_die();
    }

    public function process_import()
    {
        global $g_comp_database;

        Context::instance()
            ->setContextTechnical(Context::CONTEXT_LDAP_IMPORT)
            ->setGroup(Context::CONTEXT_GROUP_IMPORT)
            ->setContextCustomer(Context::CONTEXT_LDAP_IMPORT)
            ->setImmutable(true);

        try {
            $l_dn_array = isys_format_json::decode($_POST['ids']);

            $l_ldap_server_id = $_POST['ldap_server'];
            $l_ldap_dn_string = trim($_POST['ldap_dn']);
            $l_connection_info = null;

            $l_ldap_mod = new isys_module_ldap;
            $l_ldap_lib = $l_ldap_mod->get_library_by_id($l_ldap_server_id, $l_connection_info);

            $l_ret = false;

            if ($l_ldap_lib) {
                $cookie = '';
                $l_entries = [];
                do {
                    if (PHP_VERSION_ID < ldapi::PHP_VERSION_LDAP_PAGING_DEPRECATION) {
                        if ($l_ldap_lib->isPagedResultSupported()) {
                            $l_ldap_lib->ldapControlPagedResult(1, true, $cookie);
                        }
                        $l_search_resource = $l_ldap_lib->search(
                            $l_ldap_dn_string,
                            '(objectclass=*)',
                            [],
                            0,
                            null,
                            null,
                            null,
                            C__LDAP_SCOPE__RECURSIVE
                        );
                    } else {
                        // phpcs:disable
                        $l_search_resource = $l_ldap_lib->search(
                            $l_ldap_dn_string,
                            '(objectclass=*)',
                            [],
                            0,
                            null,
                            null,
                            null,
                            C__LDAP_SCOPE__RECURSIVE,
                            [['oid' => LDAP_CONTROL_PAGEDRESULTS, 'value' => ['size' => 2, 'cookie' => $cookie]]]
                        );
                        // phpcs:enable
                    }

                    $l_entries = array_merge($l_entries, $l_ldap_lib->get_entries($l_search_resource));

                    if (PHP_VERSION_ID < ldapi::PHP_VERSION_LDAP_PAGING_DEPRECATION) {
                        if ($l_ldap_lib->isPagedResultSupported()) {
                            $l_ldap_lib->ldapControlPagedResultResponse($l_search_resource, $cookie);
                        }
                    } else {
                        // phpcs:disable
                        ldap_parse_result($l_ldap_lib->get_connection(), $l_search_resource, $errcode, $matcheddn, $errmsg, $referrals, $controls);
                        if (isset($controls[LDAP_CONTROL_PAGEDRESULTS]['value']['cookie'])) {
                            // You need to pass the cookie from the last call to the next one
                            $cookie = $controls[LDAP_CONTROL_PAGEDRESULTS]['value']['cookie'];
                        } else {
                            $cookie = '';
                        }
                        // phpcs:enable
                    }
                } while (!empty($cookie));

                if ($l_search_resource) {
                    switch ($l_connection_info['isys_ldap_directory__const']) {
                        case 'C__LDAP__AD':
                            isys_module_ldap::debug('Using "Active Directory"!');
                            $l_import_obj = new isys_ldap_dao_import_active_directory($g_comp_database, $l_ldap_lib);
                            $l_ret = $l_import_obj->set_resource($l_search_resource)
                                ->set_root_dn($l_ldap_dn_string)
                                ->set_dn_data($l_dn_array)
                                ->prepare($l_entries)
                                ->import();
                            break;
                        case 'C__LDAP__OPENLDAP':
                            isys_module_ldap::debug('Using "Open LDAP" ... Sorry, this is currently unsupported.');
                            echo isys_application::instance()->container->get('language')
                                ->get('LC__MODULE__LDAP__DIRECTORY_UNSUPPORTED');
                            break;
                        case 'C__LDAP__NDS':
                            isys_module_ldap::debug('Using "Novell Directory Services" ... Sorry, this is currently unsupported.');
                            echo isys_application::instance()->container->get('language')
                                ->get('LC__MODULE__LDAP__DIRECTORY_UNSUPPORTED');
                            break;
                    }
                }

                if (!$l_ret) {
                    echo isys_application::instance()->container->get('language')
                        ->get('LC__MODULE__LDAP__LDAP_OBJECTS_ERROR_MSG');
                }
            }
        } catch (isys_exception_ldap $e) {
            isys_notify::error($e->getMessage());
        }
    }

    /**
     * Method for processing the LDAP Filter.
     *
     * @return  json array string|boolean
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function process_filter()
    {
        global $g_comp_database;

        try {
            $l_ldap_server_id = $_POST['ldap_server'];
            $l_ldap_dn_string = trim($_POST['ldap_dn']);
            $l_connection_info = null;

            $l_ldap_mod = new isys_module_ldap;
            $l_ldap_lib = $l_ldap_mod->get_library_by_id($l_ldap_server_id, $l_connection_info);

            if ($l_ldap_lib) {
                $l_search_resource = $l_ldap_lib->search($l_ldap_dn_string, '(objectclass=*)', [], 0, null, null, null, C__LDAP_SCOPE__SINGLE);

                if ($l_search_resource) {
                    switch ($l_connection_info['isys_ldap_directory__const']) {
                        case 'C__LDAP__AD':
                            $l_import_obj = new isys_ldap_dao_import_active_directory($g_comp_database, $l_ldap_lib);
                            $l_arr = $l_import_obj->set_resource($l_search_resource)
                                ->set_root_dn($l_ldap_dn_string)
                                ->get_entries_from_resource();

                            return isys_format_json::encode($l_arr);
                            break;
                        case 'C__LDAP__OPENLDAP':
                            break;
                        case 'C__LDAP__NDS':
                            break;
                    }
                }
            }
        } catch (isys_exception_ldap $e) {
            isys_notify::error($e->getMessage());
        }

        return false;
    }
}

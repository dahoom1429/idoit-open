<?php

use idoit\AddOn\AuthableInterface;

/**
 * i-doit
 *
 * Logbook list.
 *
 * @package     i-doit
 * @subpackage  Modules
 * @author      Dennis Bluemer <dbluemer@i-doit.org>
 * @version     0.9.4
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_module_logbook extends isys_module implements AuthableInterface
{
    const DISPLAY_IN_MAIN_MENU = true;

    // Define, if this module shall be displayed in the named menus.
    const DISPLAY_IN_SYSTEM_MENU = false;

    /**
     * @var bool
     */
    protected static $m_licenced = true;

    /**
     * Objects with their names and identifiers
     *
     * @see fetch_object_name_by_id()
     * @var array
     */
    protected $m_objects = [];

    /**
     * This method builds the tree for the menu.
     *
     * @param   isys_component_tree $p_tree
     * @param   boolean             $p_system_module
     * @param   integer             $p_parent
     *
     * @author  Leonard Fischer <lfischer@i-doit.org>
     * @since   0.9.9-7
     * @see     isys_module::build_tree()
     */
    public function build_tree(isys_component_tree $p_tree, $p_system_module = true, $p_parent = null)
    {
        if (!defined('C__MODULE__LOGBOOK')) {
            return false;
        }
        global $g_dirs;
        $languageManager = isys_application::instance()->container->get('language');

        $l_parent = -1;
        $l_submodule = '';

        if ($p_system_module) {
            $l_parent = $p_tree->find_id_by_title('Modules');
            $l_submodule = '&' . C__GET__MODULE_SUB_ID . '=' . C__MODULE__LOGBOOK;
        }

        if (null !== $p_parent && is_int($p_parent)) {
            $l_root = $p_parent;
        } else {
            $l_root = $p_tree->add_node(C__MODULE__LOGBOOK . '0', $l_parent, $languageManager->get('LC__CMDB__CATG__LOGBOOK'));
        }

        $p_tree->add_node(
            C__MODULE__LOGBOOK . C__PAGE__LOGBOOK_VIEW,
            $l_root,
            $languageManager->get('LC__CMDB__LOGBOOK__LIST_CONTENT_TITLE'),
            '?' . C__GET__MODULE_ID . '=' . $_GET[C__GET__MODULE_ID] . $l_submodule . '&' . C__GET__TREE_NODE . '=' . C__MODULE__LOGBOOK . C__PAGE__LOGBOOK_VIEW . '&' .
            C__GET__SETTINGS_PAGE . '=' . C__PAGE__LOGBOOK_VIEW . '&' . C__GET__MAIN_MENU__NAVIGATION_ID . '=6',
            '',
            'images/icons/silk/book_open.png',
            0,
            '',
            '',
            isys_auth_logbook::instance()
                ->is_allowed_to(isys_auth::VIEW, 'LOGBOOK/' . C__MODULE__LOGBOOK . C__PAGE__LOGBOOK_VIEW)
        );

        $p_tree->add_node(
            C__MODULE__LOGBOOK . C__PAGE__LOGBOOK_CONFIGURATION,
            $l_root,
            $languageManager->get("LC__MODULE__CMDB__LOGBOOK_CONFIGURATION"),
            '?' . C__GET__MODULE_ID . '=' . $_GET[C__GET__MODULE_ID] . $l_submodule . '&' . C__GET__TREE_NODE . '=' . C__MODULE__LOGBOOK . C__PAGE__LOGBOOK_CONFIGURATION .
            '&' . C__GET__SETTINGS_PAGE . '=' . C__PAGE__LOGBOOK_CONFIGURATION . '&' . C__GET__MAIN_MENU__NAVIGATION_ID . '=6',
            null,
            $g_dirs["images"] . "/icons/silk/book_edit.png",
            0,
            '',
            '',
            isys_auth_logbook::instance()
                ->is_allowed_to(isys_auth::VIEW, 'LOGBOOK/' . C__MODULE__LOGBOOK . C__PAGE__LOGBOOK_CONFIGURATION)
        );

        $p_tree->add_node(
            C__MODULE__LOGBOOK . C__PAGE__LOGBOOK_ARCHIVE,
            $l_root,
            $languageManager->get('LC__NAVIGATION__NAVBAR__ARCHIVE'),
            '?' . C__GET__MODULE_ID . '=' . $_GET[C__GET__MODULE_ID] . $l_submodule . '&' . C__GET__TREE_NODE . '=' . C__MODULE__LOGBOOK . C__PAGE__LOGBOOK_ARCHIVE . '&' .
            C__GET__SETTINGS_PAGE . '=' . C__PAGE__LOGBOOK_ARCHIVE . '&' . C__GET__MAIN_MENU__NAVIGATION_ID . '=6',
            null,
            $g_dirs["images"] . "/icons/silk/door_in.png",
            0,
            '',
            '',
            isys_auth_logbook::instance()
                ->is_allowed_to(isys_auth::VIEW, 'LOGBOOK/' . C__MODULE__LOGBOOK . C__PAGE__LOGBOOK_ARCHIVE)
        );

        $p_tree->add_node(
            C__MODULE__LOGBOOK . C__PAGE__LOGBOOK_RESTORE,
            $l_root,
            $languageManager->get('LC__UNIVERSAL__RESTORE'),
            '?' . C__GET__MODULE_ID . '=' . $_GET[C__GET__MODULE_ID] . $l_submodule . '&' . C__GET__TREE_NODE . '=' . C__MODULE__LOGBOOK . C__PAGE__LOGBOOK_RESTORE . '&' .
            C__GET__SETTINGS_PAGE . '=' . C__PAGE__LOGBOOK_RESTORE . '&' . C__GET__MAIN_MENU__NAVIGATION_ID . '=6',
            null,
            $g_dirs["images"] . "/icons/silk/arrow_refresh.png",
            0,
            '',
            '',
            isys_auth_logbook::instance()
                ->is_allowed_to(isys_auth::EXECUTE, 'LOGBOOK/' . C__MODULE__LOGBOOK . C__PAGE__LOGBOOK_RESTORE)
        );
    }

    /**
     * Method for handling the module request.
     *
     */
    public function start()
    {
        if (!defined('C__MODULE__LOGBOOK') || !defined('C__MODULE__SYSTEM')) {
            return false;
        }
        if (isys_glob_get_param("ajax") && !isys_glob_get_param("call")) {
            $this->processAjaxRequest();
            die;
        }

        /* Set memory limit */
        if (($l_memlimit = isys_tenantsettings::get('system.memory-limit.search', '768M'))) {
            ini_set('memory_limit', $l_memlimit);
        }

        global $index_includes;

        // Build up the tree
        if ($_GET[C__GET__MODULE_ID] != C__MODULE__SYSTEM) {
            $l_tree = isys_module_request::get_instance()
                ->get_menutree();
            $this->build_tree($l_tree, false);
            isys_application::instance()->template->assign("menu_tree", $l_tree->process($_GET[C__GET__TREE_NODE]));
        }

        try {
            if (!isset($_GET[C__GET__SETTINGS_PAGE])) {
                if (isys_auth_logbook::instance()
                    ->is_allowed_to(isys_auth::VIEW, 'LOGBOOK/' . C__MODULE__LOGBOOK . C__PAGE__LOGBOOK_VIEW)) {
                    $_GET[C__GET__SETTINGS_PAGE] = C__PAGE__LOGBOOK_VIEW;
                } elseif (isys_auth_logbook::instance()
                    ->is_allowed_to(isys_auth::VIEW, 'LOGBOOK/' . C__MODULE__LOGBOOK . C__PAGE__LOGBOOK_CONFIGURATION)) {
                    $_GET[C__GET__SETTINGS_PAGE] = C__PAGE__LOGBOOK_CONFIGURATION;
                } elseif (isys_auth_logbook::instance()
                    ->is_allowed_to(isys_auth::VIEW, 'LOGBOOK/' . C__MODULE__LOGBOOK . C__PAGE__LOGBOOK_ARCHIVE)) {
                    $_GET[C__GET__SETTINGS_PAGE] = C__PAGE__LOGBOOK_ARCHIVE;
                } elseif (isys_auth_logbook::instance()
                    ->is_allowed_to(isys_auth::EDIT, 'LOGBOOK/' . C__MODULE__LOGBOOK . C__PAGE__LOGBOOK_RESTORE)) {
                    $_GET[C__GET__SETTINGS_PAGE] = C__PAGE__LOGBOOK_RESTORE;
                }
            }

            // Handle request.
            switch ($_GET[C__GET__SETTINGS_PAGE]) {
                case C__PAGE__LOGBOOK_ARCHIVE:
                    isys_auth_logbook::instance()
                        ->check(isys_auth::VIEW, 'LOGBOOK/' . C__MODULE__LOGBOOK . C__PAGE__LOGBOOK_ARCHIVE);
                    $this->processArchive();
                    break;
                case C__PAGE__LOGBOOK_RESTORE:
                    isys_auth_logbook::instance()
                        ->check(isys_auth::EXECUTE, 'LOGBOOK/' . C__MODULE__LOGBOOK . C__PAGE__LOGBOOK_RESTORE);
                    $this->processRestore();
                    break;
                case C__PAGE__LOGBOOK_CONFIGURATION:
                    isys_auth_logbook::instance()
                        ->check(isys_auth::VIEW, 'LOGBOOK/' . C__MODULE__LOGBOOK . C__PAGE__LOGBOOK_CONFIGURATION);
                    $this->processConfiguration();
                    break;
                case C__PAGE__LOGBOOK_VIEW:
                default:
                    isys_auth_logbook::instance()
                        ->check(isys_auth::VIEW, 'LOGBOOK/' . C__MODULE__LOGBOOK . C__PAGE__LOGBOOK_VIEW);
                    $this->processView();
            }
        } catch (isys_exception_general $e) {
            throw $e;
        } catch (isys_exception_auth $e) {
            isys_application::instance()->template->assign("exception", $e->write_log());
            $index_includes['contentbottomcontent'] = "exception-auth.tpl";
        }
    }

    /**
     * Prepares an array of the changes in a category for the loogbook.
     *
     * @param   object $p_dao
     * @param   array  $p_dataset_from
     * @param   array  $p_category_values
     *
     * @return  array
     * @author  Van Quyen Hoang <qhoang@synetics.de>
     */
    public function prepare_changes($p_dao, $p_dataset_from = null, $p_category_values = null)
    {
        // Properties of category.
        $l_properties = $p_dao->get_properties();

        $language = isys_application::instance()->container->get('language');

        $l_changes_array = [];

        $l_category_values = $p_category_values[isys_import_handler_cmdb::C__PROPERTIES];
        // For mass changes we have to go through each property so that it can also document empty fields.
        foreach ($l_properties as $l_key => $l_prop_info) {
            if ((!$l_prop_info[C__PROPERTY__PROVIDES][C__PROPERTY__PROVIDES__IMPORT] && isset($l_category_values[$l_key])) || !isset($l_category_values[$l_key])) {
                continue;
            }

            $l_arData = null;
            $l_from = null;
            $l_to = null;
            $l_unit_property = null;
            $l_has_callback = false;

            $l_field = ((isset($l_prop_info[C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD_ALIAS])) ? $l_prop_info[C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD_ALIAS] : $l_prop_info[C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD]);

            // Some properties needs a special handling.
            if (isset($l_prop_info[C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][1])) {
                switch ($l_prop_info[C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][1]) {
                    case 'dialog_multiselect':
                        /**
                         * @todo make dialog_multiselect work in changes array
                         */
                        if (isset($l_prop_info[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_arData'])) {
                        }
                        break;
                    case 'dialog':
                    case 'dialog_plus':
                    case 'get_yes_or_no':
                        if (isset($l_prop_info[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_arData'])) {
                            $l_arData = $l_prop_info[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_arData'];
                            if ($l_arData instanceof isys_callback) {
                                $l_arData = $l_arData->execute();
                            }

                            if (is_string($l_arData)) {
                                $l_arData = unserialize($l_arData);
                            }
                        } else {
                            if (isset($l_prop_info[C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES])) {
                                $l_field = ((isset($l_prop_info[C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD_ALIAS])) ? $l_prop_info[C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD_ALIAS] : $l_prop_info[C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0] .
                                    '__title');
                            }
                        }
                        break;
                    case 'location':
                    case 'object':
                        $l_from = $this->fetch_object_name_by_id($p_dataset_from[$l_prop_info[C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD]], $p_dao);
                        $l_to = $this->fetch_object_name_by_id($l_category_values[$l_key]['value'], $p_dao);
                        break;
                    case 'connection':
                        if (isset($l_prop_info[C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD_ALIAS])) {
                            $l_from = $this->fetch_object_name_by_id($p_dataset_from[$l_prop_info[C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD_ALIAS] . '__object'], $p_dao);
                            $l_to = $this->fetch_object_name_by_id($l_category_values[$l_key]['value'], $p_dao);
                        } else {
                            $l_from = $this->fetch_object_name_by_id($p_dataset_from['isys_connection__isys_obj__id'], $p_dao);
                            $l_to = $this->fetch_object_name_by_id($l_category_values[$l_key]['value'], $p_dao);
                        }

                        $l_has_callback = true;
                        break;
                    case 'contact':
                        $l_res_from = $p_dao->retrieve('SELECT isys_contact_2_isys_obj__isys_obj__id FROM isys_contact_2_isys_obj WHERE isys_contact_2_isys_obj__isys_contact__id = ' .
                            $p_dao->convert_sql_id($p_dataset_from[$l_field]) . ' ORDER BY isys_contact_2_isys_obj__isys_obj__id ASC');
                        while ($l_row_from = $l_res_from->get_row()) {
                            $l_from .= $this->fetch_object_name_by_id($l_row_from['isys_contact_2_isys_obj__isys_obj__id'], $p_dao) . ',';
                        }
                        $l_from = rtrim($l_from, ',');

                        $l_res_to = $p_dao->retrieve('SELECT isys_contact_2_isys_obj__isys_obj__id FROM isys_contact_2_isys_obj WHERE isys_contact_2_isys_obj__isys_contact__id = ' .
                            $p_dao->convert_sql_id($l_category_values[$l_key]['value']) . ' ORDER BY isys_contact_2_isys_obj__isys_obj__id ASC');
                        while ($l_row_to = $l_res_to->get_row()) {
                            $l_to .= $this->fetch_object_name_by_id($l_row_to['isys_contact_2_isys_obj__isys_obj__id'], $p_dao) . ',';
                        }

                        $l_to = rtrim($l_to, ',');

                        $l_has_callback = true;
                        break;
                    // @see API-13 This will be used for contacts in the contact category.
                    case 'exportContactAssignment':
                        $l_from = [];
                        $l_to = [];

                        $result = $p_dao->retrieve('SELECT isys_obj__title FROM isys_obj WHERE isys_obj__id = ' . $p_dao->convert_sql_id($p_dataset_from[$l_field]) . ';');

                        while ($row = $result->get_row()) {
                            $l_from[] = $language->get($row['isys_obj__title']);
                        }

                        $result = $p_dao->retrieve('SELECT isys_obj__title FROM isys_obj WHERE isys_obj__id = ' . $p_dao->convert_sql_id($l_category_values[$l_key]['value']) .
                            ';');

                        while ($row = $result->get_row()) {
                            $l_to[] = $language->get($row['isys_obj__title']);
                        }

                        $l_from = implode(', ', $l_from);
                        $l_to = implode(', ', $l_to);

                        $l_has_callback = true;
                        break;
                    case 'convert':
                        // Checks whether an unit property exists
                        if (isset($l_prop_info[C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__UNIT])) {
                            $l_unit_property = $l_properties[$l_prop_info[C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__UNIT]];
                        }
                        break;
                    default:
                        $formatCallbackClass = $l_prop_info[C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][0];

                        if (!is_a($formatCallbackClass, 'isys_export_helper', true)) {
                            break;
                        }

                        $l_helper = new $formatCallbackClass(
                            $p_dataset_from,
                            $p_dao->get_database_component(),
                            $l_prop_info[C__PROPERTY__DATA],
                            $l_prop_info[C__PROPERTY__FORMAT],
                            $l_prop_info[C__PROPERTY__UI]
                        );

                        $l_method = $l_prop_info[C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][1];

                        if (isset($p_dataset_from)) {
                            $l_helper_value = $l_helper->$l_method($p_dataset_from[$l_field]);

                            if (is_object($l_helper_value)) {
                                $l_helper_value_data = $l_helper_value->get_data();
                            } else {
                                $l_helper_value_data = $l_helper_value;
                            }

                            if (is_array($l_helper_value_data) > 0) {
                                if (is_array($l_helper_value_data[0])) {
                                    $l_helper_value_data = $l_helper_value_data[0];
                                }

                                if (isset($l_helper_value_data['ref_id'])) {
                                    if ($l_method === 'exportIpReference') {
                                        $l_from = $l_helper_value_data['ref_title'];
                                    } else {
                                        $l_from = $l_helper_value_data['ref_id'];
                                    }
                                } elseif (isset($l_helper_value_data['title'])) {
                                    $l_from = $l_helper_value_data['title'];
                                } else {
                                    $l_from = $l_helper_value_data['id'];
                                }
                            } else {
                                $l_from = $l_helper_value_data;
                            }
                        }

                        if (isset($l_category_values[$l_key]['ref_title'])) {
                            $l_to = $l_category_values[$l_key]['ref_title'];
                        } elseif (isset($l_category_values[$l_key]['value'])) {
                            if (is_numeric($l_category_values[$l_key]['value'])) {
                                $l_helper_value = $l_helper->$l_method($l_category_values[$l_key]['value']);

                                if (is_object($l_helper_value)) {
                                    $l_helper_value_data = $l_helper_value->get_data();
                                } else {
                                    $l_helper_value_data = $l_helper_value;
                                }

                                if (is_array($l_helper_value_data) > 0) {
                                    if (is_array($l_helper_value_data[0])) {
                                        $l_helper_value_data = $l_helper_value_data[0];
                                    }

                                    if (isset($l_helper_value_data['ref_id'])) {
                                        if ($l_method === 'exportIpReference') {
                                            $l_to = $l_helper_value_data['ref_title'];
                                        } else {
                                            $l_to = $l_helper_value_data['ref_id'];
                                        }
                                    } elseif (isset($l_helper_value_data['title'])) {
                                        $l_to = $l_helper_value_data['title'];
                                    } else {
                                        $l_to = $l_helper_value_data['id'];
                                    }
                                } else {
                                    $l_to = $l_helper_value_data;
                                }
                            } else {
                                $l_to_val = $l_category_values[$l_key]['value'];
                                if (is_array($l_to_val)) {
                                    if (isset($l_to_val['ref_title'])) {
                                        $l_to = $l_to_val['ref_title'];
                                    } else {
                                        foreach ($l_to_val as $l_to_part) {
                                            $l_to .= $l_to_part . ',';
                                        }
                                        $l_to = rtrim($l_to, ',');
                                    }
                                } else {
                                    $l_to = $l_to_val;
                                }
                            }
                        }

                        if (isset($l_category_values[$l_key]['reference']) && (!empty($l_from) || !empty($l_to))) {
                            if (isset($l_prop_info[C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][2]) &&
                                $l_category_values[$l_key]['reference'] == $l_prop_info[C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0]) {
                                $l_check_field = $l_prop_info[C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][2];
                            } else {
                                $l_check_field = $l_category_values[$l_key]['reference'] . '__title';
                            }

                            $l_query = 'SELECT ' . $l_check_field . ' FROM ' . $l_category_values[$l_key]['reference'] . ' WHERE ' . $l_category_values[$l_key]['reference'] .
                                '__id = ';
                            if (isset($l_from)) {
                                // Retrieve title only if value is numeric
                                if (is_numeric($l_from)) {
                                    $l_from = $p_dao->retrieve($l_query . $p_dao->convert_sql_id($l_from) . ';')
                                        ->get_row_value($l_category_values[$l_key]['reference'] . '__title');
                                }
                            }

                            if (isset($l_to)) {
                                // Retrieve title only if value is numeric
                                if (is_numeric($l_to)) {
                                    $l_to = $p_dao->retrieve($l_query . $p_dao->convert_sql_id($l_to) . ';')
                                        ->get_row_value($l_category_values[$l_key]['reference'] . '__title');
                                }
                            }
                        }

                        if (($l_prop_info[C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][1] == 'exportDnsServer' ||
                                $l_prop_info[C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][1] == 'exportIpReference') && (!empty($l_from) || !empty($l_to))) {
                            $l_query = 'SELECT isys_cats_net_ip_addresses_list__title FROM isys_cats_net_ip_addresses_list ' .
                                'INNER JOIN isys_catg_ip_list ON isys_catg_ip_list__isys_cats_net_ip_addresses_list__id = isys_cats_net_ip_addresses_list__id ' .
                                'WHERE isys_catg_ip_list__id = ';

                            if (isset($l_from) && is_numeric($l_from)) {
                                $l_data_from2 = $p_dao->retrieve($l_query . $p_dao->convert_sql_id($l_from))
                                    ->get_row();
                                $l_from = $l_data_from2['isys_cats_net_ip_addresses_list__title'];
                            }

                            if (isset($l_to) && is_numeric($l_to)) {
                                $l_data_to2 = $p_dao->retrieve($l_query . $p_dao->convert_sql_id($l_to))
                                    ->get_row();
                                $l_to = $l_data_to2['isys_cats_net_ip_addresses_list__title'];
                            }
                        }

                        $l_has_callback = true;

                        unset($l_helper);
                        break;
                }
            } else {
                if (isset($l_prop_info[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_arData'])) {
                    $l_arData = $l_prop_info[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_arData'];

                    if (is_object($l_arData)) {
                        $l_arData = $l_arData->execute();
                    }

                    if (is_string($l_arData)) {
                        $l_arData = unserialize($l_arData);
                    }
                }
            }

            // Checks whether data comes from the callback in the ui property
            if (isset($l_arData)) {
                if (isset($p_dataset_from[$l_field]) && isset($l_arData[$p_dataset_from[$l_field]])) {
                    $l_from = $l_arData[$p_dataset_from[$l_field]];
                }

                if (is_array($l_category_values[$l_key]['value'])) {
                    if (isset($l_category_values[$l_key]['value']['value'])) {
                        $l_category_values[$l_key] = $l_category_values[$l_key]['value'];
                    }
                }

                if (isset($l_key) && isset($l_category_values[$l_key]['value']) && isset($l_arData[$l_category_values[$l_key]['value']])) {
                    $l_to = $l_arData[$l_category_values[$l_key]['value']];
                }
            } else {
                if (!isset($l_from) && !isset($l_to)) {
                    if (isset($l_prop_info[C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][1]) &&
                        ($l_prop_info[C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][1] == 'dialog' ||
                            $l_prop_info[C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][1] == 'dialog_plus')) {
                        if (isset($p_dataset_from) && isset($p_dataset_from[$l_field])) {
                            if (is_numeric($p_dataset_from[$l_field])) {
                                $l_from_data = $p_dao
                                    ->get_dialog($l_prop_info[C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0], $p_dataset_from[$l_field])
                                    ->__to_array();
                                $l_from = $l_from_data[$l_prop_info[C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0] . '__title'];
                            } else {
                                $l_from = $p_dataset_from[$l_field];
                            }
                        } else {
                            $l_from = $p_dataset_from[$l_field];
                        }

                        if ($l_category_values[$l_key]['title_lang'] && !is_numeric($l_category_values[$l_key]['title_lang'])) {
                            $l_to = $l_category_values[$l_key]['title_lang'];
                        } elseif (isset($l_category_values[$l_key]['value']) && is_numeric($l_category_values[$l_key]['value'])) {
                            $l_to_data = $p_dao
                                ->get_dialog($l_prop_info[C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0], $l_category_values[$l_key]['value'])
                                ->__to_array();
                            $l_to = $l_to_data[$l_prop_info[C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0] . '__title'];
                        }
                    } else {
                        if (!$l_has_callback) {
                            $l_from = $p_dataset_from[$l_field];
                            if (isset($l_category_values[$l_key]['value'])) {
                                $l_to = $l_category_values[$l_key]['value'];
                            }
                        }
                    }
                }
            }

            // Replaces the raw data with the converted data
            if (isset($l_unit_property)) {
                $l_unit_field_from = $p_dataset_from[$l_unit_property[C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD]];

                $l_method = $l_prop_info[C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][2][0];

                /**
                 * Format a possible "," notated number to the english "." format, to not track 1,76 and 1.76 as a change
                 *
                 * @see ID-2460
                 */
                $l_from = str_replace(',', '.', isys_convert::$l_method($l_from, $l_unit_field_from, C__CONVERT_DIRECTION__BACKWARD));
                $l_to = str_replace(',', '.', $l_category_values[$l_key]['value']);
            }

            // Check
            if (!is_array($l_from) && !is_array($l_to) && (!empty($l_from) || !empty($l_to))) {
                $l_formatted_from = trim($language->get($l_from));
                $l_formatted_to = trim($language->get($l_to));

                if (strtolower($l_formatted_from) != strtolower($l_formatted_to)) {
                    $l_changes_array[get_class($p_dao) . '::' . $l_key] = [
                        'from' => $l_formatted_from,
                        'to'   => $l_formatted_to
                    ];
                }
            }
        }

        return $l_changes_array;
    }

    /**
     * Initialize method.
     *
     * @param   isys_module_request $p_req
     *
     * @return  boolean
     */
    public function init(isys_module_request $p_req)
    {
        if (is_object($p_req)) {
            return true;
        }

        return false;
    }

    /**
     * Assign the logbook detail view to the templates
     */
    protected function processDetailView()
    {
        global $index_includes;
        global $g_comp_database;
        global $g_db_system;

        $l_ui_logbook = new isys_cmdb_ui_category_g_logb(isys_application::instance()->template);

        // if id is connected to an object redirect browser to logbook
        // category of the object
        $l_objDAOLogbook = new isys_component_dao_logbook($g_comp_database);

        $l_lbID = isys_glob_get_param("id");

        // Is desired entry in archive?
        if (isys_glob_get_param('archived')) {
            try {
                /**
                 * Build database component for logbook archive
                 */

                // Get settings for archive database connection
                $l_settings = $l_objDAOLogbook->getArchivingSettings();

                // Is archive on same database?
                if ($l_settings["dest"] == 0) {
                    $l_db = $g_comp_database;
                } else {
                    // Create database connection to external database
                    $l_db = isys_component_database::get_database(
                        $g_db_system["type"],
                        $l_settings["host"],
                        $l_settings["port"],
                        $l_settings["user"],
                        $l_settings["pass"],
                        $l_settings["db"]
                    );
                }

                $l_objDAOLogbook = new isys_component_dao_archive($l_db);
            } catch (Exception $e) {
            }
        }

        $l_lbRes = $l_objDAOLogbook->get_result_by_logbook_id($l_lbID);
        $l_lbRow = $l_lbRes->get_row();

        $l_nObjID = $l_objDAOLogbook->get_object_id_by_logbook_id($l_lbID);

        /**
         * @todo set $l_nObjID to null if object is deleted!
         */
        if ($l_nObjID) {
            $l_objCMDBDAO = new isys_cmdb_dao($g_comp_database);
            $l_nObjTypeID = $l_objCMDBDAO->get_objTypeID($l_nObjID);
            $l_nTreeMode = C__CMDB__VIEW__TREE_OBJECT;
            $l_nViewMode = C__CMDB__VIEW__CATEGORY_GLOBAL;
            $l_CatgID = defined_or_default('C__CATG__LOGBOOK');
            $l_nCatID = $l_lbRow["isys_catg_logb_list__id"];

            $l_strURL = "index.php" . "?moduleID=" . defined_or_default('C__MODULE__CMDB') . "&objTypeID=$l_nObjTypeID" . "&viewMode=$l_nViewMode" . "&tvMode=$l_nTreeMode" . "&objID=$l_nObjID" .
                "&catgID=$l_CatgID" . "&cateID=$l_nCatID";

            header("Location: $l_strURL"); //redirect browser
            die;
        }

        // title
        $l_mod_event_manager = isys_event_manager::getInstance();

        $l_strTitle = $l_mod_event_manager->translateEvent(
            $l_lbRow["isys_logbook__event_static"],
            $l_lbRow["isys_logbook__obj_name_static"],
            $l_lbRow["isys_logbook__category_static"],
            $l_lbRow["isys_logbook__obj_type_static"] . $l_lbRow["isys_logbook__entry_identifier_static"],
            $l_lbRow["isys_logbook__changecount"]
        );

        /* Assign and retrieve changes */
        $l_changes_ar = $l_ui_logbook->get_changes_as_array($l_lbRow["isys_logbook__changes"]);

        $l_rules["C__CMDB__LOGBOOK__CHANGED_FIELDS"]["p_strValue"] = is_countable($l_changes_ar) ? count($l_changes_ar) : 0;

        if (($l_changes = $l_ui_logbook->get_changes_as_html_table($l_changes_ar))) {
            isys_application::instance()->template->assign("changes", $l_changes);
        }

        // Make rules
        $l_rules["C__CMDB__LOGBOOK__TITLE"]["p_strValue"] = $l_strTitle;
        $l_rules["C__CMDB__LOGBOOK__DESCRIPTION"]["p_strValue"] = stripslashes($l_lbRow["isys_logbook__description"]);
        $l_rules["C__CMDB__LOGBOOK__DATE"]["p_strValue"] = $l_lbRow["isys_logbook__date"];
        $l_rules["C__CMDB__LOGBOOK__LEVEL"]["p_strValue"] = isys_application::instance()->container->get('language')
            ->get($l_lbRow["isys_logbook_level__title"]);
        $l_rules["C__CMDB__LOGBOOK__COMMENT"]["p_strValue"] = $l_lbRow["isys_logbook__comment"];
        $l_rules["C__CMDB__LOGBOOK__REASON"]["p_strSelectedID"] = $l_lbRow["isys_logbook__isys_logbook_reason__id"];

        //is there a name?
        if ($l_lbRow["isys_logbook__isys_obj__id"] > 0) {
            $l_strUsertitle = isys_component_dao_user::instance($g_comp_database)
                ->get_user_title($l_lbRow["isys_logbook__isys_obj__id"]);
        } else {
            $l_strUsertitle = $l_lbRow["isys_logbook__user_name_static"];
        }

        if (empty($l_strUsertitle)) {
            $l_strUsertitle = isys_tenantsettings::get('gui.empty_value', '-');
        }

        $l_rules["C__CMDB__LOGBOOK__USER"]["p_strValue"] = $l_strUsertitle;

        // Apply rules
        isys_application::instance()->template->smarty_tom_add_rules("tom.content.bottom.content", $l_rules);

        $index_includes["contenttop"] = '';
        $index_includes["contentbottomcontent"] = "content/bottom/content/catg__logbook.tpl";
    }

    /**
     * Assign the logbook list view to the templates
     */
    protected function processListView()
    {
        if (!defined('C__MODULE__LOGBOOK')) {
            return false;
        }
        global $g_comp_database;
        global $g_db_system;

        try {
            $l_listdao = new isys_component_dao_logbook($g_comp_database);

            // Removed "new" and "save" state, according to #4780
            isys_application::instance()->container->get('template')
                ->smarty_tom_add_rule("tom.content.top.filter.p_strValue=" . isys_glob_get_param("filter"));

            /**
             * Get all logbook entries
             */
            $l_navPageCount = $l_listdao->count();

            /* Hotfix for too many entries in page select dropdown */
            global $g_page_limit;
            if ($g_page_limit < 250 && $l_navPageCount > 250000) {
                $g_page_limit = 250;
            }

            if (isys_glob_get_param('filter_archive') == "1") {
                try {
                    $l_settings = $l_listdao->getArchivingSettings();

                    if ($l_settings['dest'] == 0) {
                        $l_db = $g_comp_database;
                    } else {
                        $l_db = isys_component_database::get_database(
                            $g_db_system['type'],
                            $l_settings['host'],
                            $l_settings['port'],
                            $l_settings['user'],
                            $l_settings['pass'],
                            $l_settings['db']
                        );
                    }

                    $l_daoArchive = new isys_component_dao_archive($l_db);
                    $l_listres = $l_daoArchive->get_result();

                    $l_objList = new isys_component_list_logbook_archive(null, $l_listres, $l_daoArchive);
                    $l_strRowLink = "document.location.href='?moduleID=" . C__MODULE__LOGBOOK . "&id=[{isys_logbook__id}]&archived=1';";
                } catch (Exception $e) {
                    isys_application::instance()->template->assign("content_title", isys_application::instance()->container->get('language')
                        ->get('LC__CMDB__LOGBOOK__LIST_CONTENT_TITLE'))
                        ->assign('archiveBrowser', 1)
                        ->assign('objectTableList', '<h3>' . $e->getMessage() . '</h3>')
                        ->smarty_tom_add_rule('tom.content.bottom.buttons.*.p_bInvisible=1')
                        ->include_template('navbar', 'content/navbar/logbook.tpl')
                        ->include_template('contentbottomcontent', 'content/bottom/content/module__logbook__list.tpl');

                    $this->setupFilter($l_listdao);

                    throw new Exception($e->getMessage());
                }
            } else {
                $l_objList = new isys_component_list_logbook(null, null, $l_listdao);
                $l_strRowLink = "document.location.href='?moduleID=" . C__MODULE__LOGBOOK . "&id=[{isys_logbook__id}]';";
            }

            // array with table header titles
            $l_arTableHeader = [
                '+'                              => '',
                'isys_logbook__title'            => $this->language->get('LC__CMDB__LOGBOOK__TITLE'),
                'isys_logbook__user_name_static' => $this->language->get('LC__CMDB__LOGBOOK__SOURCE__USER'),
                'isys_logbook__obj_name_static'  => $this->language->get('LC__CMDB__LOGBOOK__SOURCE__OBJECT'),
                'isys_logbook__date'             => $this->language->get('LC__CMDB__LOGBOOK__DATE'),
                'isys_logbook_level__title'      => $this->language->get('LC__CMDB__LOGBOOK__LEVEL'),
                'isys_logbook_source__title'     => $this->language->get('LC__CMDB__LOGBOOK__SOURCE')
            ];

            $l_objList->config($l_arTableHeader, $l_strRowLink);
            $l_objList->setTableClass('mainTable w100');

            isys_component_template_navbar::getInstance()->set_nav_page_count($l_navPageCount);

            isys_application::instance()->container->get('template')
                ->assign('content_title', $this->language->get('LC__CMDB__LOGBOOK__LIST_CONTENT_TITLE'))
                ->assign('LogbookList', $l_objList->getTempTableHtml($_POST))
                ->assign('archiveBrowser', 1)
                ->smarty_tom_add_rule('tom.content.bottom.buttons.*.p_bInvisible=1')
                ->include_template('contentbottomcontent', 'content/bottom/content/module__logbook__list.tpl')
                ->include_template('navbar', 'content/navbar/logbook.tpl');

            $this->setupFilter($l_listdao);
        } catch (Exception $e) {
            isys_application::instance()->container['notify']->error($e->getMessage());
        }
    }

    /**
     * Fetches object name by its identifier.
     *
     * @param      integer       $objectId
     * @param      isys_cmdb_dao $dao
     *
     * @deprecated We sould use `isys_cmdb_dao->get_obj_name_by_id_as_string()` directly!!
     * @return     string
     */
    protected function fetch_object_name_by_id($objectId, $dao)
    {
        // Force an integer.
        $objectId = (int)$objectId;

        if ($objectId > 0) {
            if (!isset($this->m_objects[$objectId])) {
                $this->m_objects[$objectId] = $dao->get_obj_name_by_id_as_string($objectId);
            }

            return $this->m_objects[$objectId];
        }

        return '';
    }

    /**
     * Process an AJAX request on the logbook
     *
     */
    private function processAjaxRequest()
    {
        switch (isys_glob_get_param("request")) {
            case "expandLogbookEntry":
                $this->expandLogbookEntry();
                break;

            case "executeRestore":
                $this->restoreLogbook();
                break;

            default:
                global $index_includes;
                $l_navbar = isys_component_template_navbar::getInstance();

                $l_settings_page = (int)$_GET[C__GET__SETTINGS_PAGE];
                switch ($l_settings_page) {
                    case C__PAGE__LOGBOOK_ARCHIVE:
                        $this->processArchive();

                        isys_application::instance()->template->assign("index_includes", $index_includes)
                            ->assign("navbar", $l_navbar->show_navbar())
                            ->display("content/main_groups.tpl");
                        break;
                    case C__PAGE__LOGBOOK_CONFIGURATION:
                        $this->processConfiguration();

                        isys_application::instance()->template->assign("index_includes", $index_includes)
                            ->assign("navbar", $l_navbar->show_navbar())
                            ->display("content/main_groups.tpl");
                        break;
                    case C__PAGE__LOGBOOK_VIEW:
                    default:
                        if ($_POST[C__GET__NAVMODE] == C__NAVMODE__FORWARD || $_POST[C__GET__NAVMODE] == C__NAVMODE__BACK || isys_glob_get_param("navPageStart") != "") {
                            $this->processListView();

                            isys_application::instance()->template->assign("index_includes", $index_includes)
                                ->assign("navbar", $l_navbar->show_navbar())
                                ->display("content/main_groups.tpl");
                        } else {
                            echo "Error processing AJAX request";
                        }
                        break;
                }

        }
    }

    /**
     * Echo the description of a logbook entry given by its id.
     * This is an AJAX request
     *
     */
    private function expandLogbookEntry()
    {
        if (!$_GET["log_id"]) {
            echo "Error";
        } else {
            global $g_comp_database, $g_db_system;

            $l_cmdb_ui = new isys_cmdb_ui_category_g_logb(isys_application::instance()->template);

            if (isset($_GET["inArchive"])) {
                $l_daoLogbook = new isys_component_dao_logbook($g_comp_database);
                if (is_object($l_daoLogbook)) {
                    $l_settings = $l_daoLogbook->getArchivingSettings();
                    try {
                        if ($l_settings["dest"] == 0) {
                            $l_db = $g_comp_database;
                        } else {
                            $l_db = isys_component_database::get_database(
                                $g_db_system["type"],
                                $l_settings["host"],
                                $l_settings["port"],
                                $l_settings["user"],
                                $l_settings["pass"],
                                $l_settings["db"]
                            );
                        }

                        $l_daoLogbook = new isys_component_dao_archive($l_db);
                    } catch (Exception $e) {
                        die($e->getMessage());
                    }
                }
            } else {
                $l_daoLogbook = new isys_component_dao_logbook($g_comp_database);
            }

            try {
                $l_desc = $l_daoLogbook->get_changes_utf8($_GET["log_id"]);

                $l_changes_ar = $l_cmdb_ui->get_changes_as_array($l_desc);

                if (is_countable($l_changes_ar) && count($l_changes_ar) > 0) {
                    echo $l_cmdb_ui->get_changes_as_html_table($l_changes_ar, "");
                } else {
                    echo "No changes assigned to this logbook entry.";
                }
            } catch (Exception $e) {
                echo $e->getMessage();
            }
        }
    }

    /**
     * Restore logbook data from the archive. Connection information are passed by POST
     *
     */
    private function restoreLogbook()
    {
        global $g_comp_database, $g_db_system;

        try {
            if ($_POST["archiveSource"] == "0") {
                // Use the local database
                $l_db = $g_comp_database;
            } else {
                // Use a remote database
                $l_db = isys_component_database::get_database(
                    $g_db_system["type"],
                    $_POST["archiveHost"],
                    $_POST["archivePort"],
                    $_POST["archiveUser"],
                    $_POST["archivePass"],
                    $_POST["archiveDB"]
                );
            }
        } catch (Exception $e) {
            echo "<p style=\"color:#ff0000;\">Could not connect to " . $_POST["archiveHost"] . "</p>";
            die;
        }

        $l_daoArchive = new isys_component_dao_archive($l_db);

        // Calculate the date to restore from. Number of days in the past passed by POST
        $l_arDate = getdate(time() - $_POST["restoreFrom"] * isys_convert::DAY);
        $l_date = $l_arDate["year"] . "-" . $l_arDate["mon"] . "-" . $l_arDate["mday"] . " " . $l_arDate["hours"] . ":" . $l_arDate["minutes"] . ":" . $l_arDate["seconds"];

        try {
            if (!$l_db->is_table_existent("isys_archive_logbook")) {
                throw new Exception("No archive data found");
            }

            // start restoring
            $l_daoArchive->restore(new isys_component_dao_logbook($g_comp_database), $l_date, $_POST["restoreFrom"], $_POST["archiveSource"] == "0");
            echo "Restore successful";
        } catch (Exception $e) {
            echo "<p>" . $e->getMessage() . "</p>";
            die;
        }
    }

    private function processView()
    {
        if (isys_glob_get_param("id")) {
            $this->processDetailView();
        } else {
            try {
                $this->processListView();
            } catch (Exception $e) {
                ;
            }
        }
    }

    private function processConfiguration()
    {
        if (!defined('C__MODULE__LOGBOOK')) {
            return false;
        }
        global $index_includes, $g_comp_session, $g_comp_database;

        $l_comp_logb = isys_component_dao_logbook::instance($g_comp_database);
        $l_user_id = $g_comp_session->get_user_id();
        $l_config_res = $l_comp_logb->get_logbook_config($l_user_id);
        $l_config = null;
        if ($l_config_res->num_rows() > 0) {
            $l_config = $l_config_res->__to_array();
        } else {
            $l_comp_logb->create_logbook_config();
            $l_config['isys_logbook_configuration__type'] = 0;
        }

        switch ($_POST[C__GET__NAVMODE]) {
            case C__NAVMODE__EDIT:
                isys_component_template_navbar::getInstance()
                    ->set_active(true, C__NAVBAR_BUTTON__SAVE)
                    ->set_active(true, C__NAVBAR_BUTTON__CANCEL);
                $index_includes['contentbottomcontent'] = "modules/cmdb/logbook.tpl";
                break;
            case C__NAVMODE__SAVE:
                if (!empty($l_config)) {
                    $l_comp_logb->save_logbook_config(
                        $l_config['isys_logbook_configuration__id'],
                        $_POST['C__MODULE__CMDB__LOGBOOK_CONFIG__TYPE'],
                        $_POST['C__MODULE__CMDB__LOGBOOK_CONFIG__PLACEHOLDER']
                    );
                } else {
                    $l_comp_logb->create_logbook_config($_POST['C__MODULE__CMDB__LOGBOOK_CONFIG__TYPE'], $_POST['C__MODULE__CMDB__LOGBOOK_CONFIG__PLACEHOLDER']);
                }
                isys_tenantsettings::set('logbook.changes.multivalue-threshold', (int)$_POST['C__MODULE__CMDB__LOGBOOK_CONFIGURATION__MULTIVALUE_THRESHOLD']);

                isys_tenantsettings::set('logbook.relations.entries', $_POST['C__MODULE__CMDB__LOGBOOK_CONFIGURATION__RELATIONS_ENTRIES']);

                $l_config = $l_comp_logb->get_logbook_config($l_user_id)
                    ->get_row();
                    // no break
            default:
                isys_component_template_navbar::getInstance()
                    ->set_active(isys_auth_logbook::instance()
                        ->is_allowed_to(isys_auth::EDIT, 'LOGBOOK/' . C__MODULE__LOGBOOK . C__PAGE__LOGBOOK_CONFIGURATION), C__NAVBAR_BUTTON__EDIT)
                    ->set_visible(true, C__NAVBAR_BUTTON__EDIT);
                $index_includes['contentbottomcontent'] = "modules/cmdb/logbook.tpl";
                break;
        }

        isys_application::instance()->template->assign('multivalue_threshold', isys_tenantsettings::get('logbook.changes.multivalue-threshold', 25))
            ->assign('relations_entries', isys_tenantsettings::get('logbook.relations.entries', 'initiated'))
            ->assign('content_title', isys_application::instance()->container->get('language')
                ->get('LC__MODULE__CMDB__LOGBOOK_CONFIGURATION'))
            ->assign('default_checked', false)
            ->assign('advanced_checked', false)
            ->assign('disabled_on', (($_POST[C__GET__NAVMODE] == C__NAVMODE__EDIT) ? false : true))
            ->assign('logbook_type', $l_config['isys_logbook_configuration__type']);

        if (!empty($l_config)) {
            isys_application::instance()->template->assign('default_checked', (($l_config['isys_logbook_configuration__type'] == 0) ? true : false))
                ->assign('advanced_checked', (($l_config['isys_logbook_configuration__type'] == 1) ? true : false))
                ->assign(
                    'placeholder_string',
                    (!empty($l_config['isys_logbook_configuration__placeholder_string']) ? $l_config['isys_logbook_configuration__placeholder_string'] : isys_application::instance()->container->get('language')
                        ->get('LC__NAVIGATION__BREADCRUMB__NO_TITLE'))
                );
        }

        isys_component_template_navbar::getInstance()
            ->set_active(false, C__NAVBAR_BUTTON__NEW)
            ->set_active(false, C__NAVBAR_BUTTON__PURGE);
    }

    /**
     * Process the archiving settings page.
     *
     */
    private function processArchive()
    {
        if (!defined('C__MODULE__LOGBOOK')) {
            return false;
        }
        global $g_comp_database;

        $l_error = false;
        $l_navbar = isys_component_template_navbar::getInstance();
        $l_daoLogbook = new isys_component_dao_logbook($g_comp_database);
        $l_has_edit_right = isys_auth_logbook::instance()
            ->is_allowed_to(isys_auth::EDIT, 'LOGBOOK/' . C__MODULE__LOGBOOK . C__PAGE__LOGBOOK_ARCHIVE);

        if ($_POST["navMode"] == C__NAVMODE__SAVE) {
            try {
                $l_daoLogbook->archiveAccessible();
                $l_daoLogbook->saveArchivingSettings();
            } catch (Exception $e) {
                $l_error = $e->getMessage();
                $_POST["navMode"] = C__NAVMODE__EDIT;
            }

            isys_application::instance()->template->assign("archiveDest", $_POST["archiveDest"]);
        }

        $l_ad = $l_daoLogbook->getArchivingSettings();

        $l_rules = [
            'archiveInterval' => [
                'p_strValue' => $l_ad["interval"]
            ],
            'archiveDest'     => [
                'p_strClass'      => 'input-small',
                'p_arData'        => [
                    "0" => isys_application::instance()->container->get('language')
                        ->get("LC__UNIVERSAL__DATABASE_LOCAL"),
                    "1" => isys_application::instance()->container->get('language')
                        ->get("LC__UNIVERSAL__DATABASE_REMOTE")
                ],
                'p_strSelectedID' => $l_ad["dest"]
            ],
            'archiveHost'     => [
                'p_strClass' => 'input-small',
                'p_strValue' => $l_ad["host"]
            ],
            'archivePort'     => [
                'p_strClass' => 'input-small',
                'p_strValue' => $l_ad["port"]
            ],
            'archiveDB'       => [
                'p_strClass' => 'input-small',
                'p_strValue' => $l_ad["db"]
            ],
            'archiveUser'     => [
                'p_strClass' => 'input-small',
                'p_strValue' => $l_ad["user"]
            ],
            'archivePass'     => [
                'p_strClass' => 'input-small',
                'p_strValue' => '' // See ID-3436. Original value: $l_ad["pass"]
            ]
        ];

        if (isset($_POST['navMode']) && ($_POST["navMode"] == C__NAVMODE__EDIT || $_POST["navMode"] == C__NAVMODE__NEW)) {
            $l_navbar->set_visible(false, C__NAVBAR_BUTTON__EDIT)
                ->set_active($l_has_edit_right, C__NAVBAR_BUTTON__SAVE)
                ->set_active(true, C__NAVBAR_BUTTON__CANCEL);
        } else {
            $l_navbar->set_active($l_has_edit_right, C__NAVBAR_BUTTON__EDIT)
                ->set_visible(false, C__NAVBAR_BUTTON__SAVE)
                ->set_visible(false, C__NAVBAR_BUTTON__CANCEL);
        }

        isys_application::instance()->template->assign('archiveDest', $l_ad['dest'])
            ->assign('archiveError', $l_error)
            ->assign('content_title', isys_application::instance()->container->get('language')
                ->get('LC__NAVIGATION__NAVBAR__ARCHIVE'))
            ->smarty_tom_add_rules('tom.content.bottom.content', $l_rules)
            ->include_template('contentbottomcontent', 'content/bottom/content/module__logbook__archive.tpl');
    }

    /**
     * Process the restore page
     */
    private function processRestore()
    {
        $l_daoLogbook = new isys_component_dao_logbook(isys_application::instance()->database);
        $l_ad = $l_daoLogbook->getArchivingSettings();

        $l_rules = [
            'archiveSource' => [
                'p_arData'        => [
                    '0' => isys_application::instance()->container->get('language')
                        ->get('LC__UNIVERSAL__DATABASE_LOCAL'),
                    '1' => isys_application::instance()->container->get('language')
                        ->get('LC__UNIVERSAL__DATABASE_REMOTE')
                ],
                'p_strSelectedID' => $l_ad['dest'],
                'p_strClass'      => 'input-small'
            ],
            'restoreFrom'   => [
                'p_strValue' => $l_ad['interval']
            ],
            'archiveHost'   => [
                'p_strValue' => $l_ad['host'],
                'p_strClass' => 'input-small'
            ],
            'archivePort'   => [
                'p_strValue' => $l_ad['port'],
                'p_strClass' => 'input-small'
            ],
            'archiveDB'     => [
                'p_strValue' => $l_ad['db'],
                'p_strClass' => 'input-small'
            ],
            'archiveUser'   => [
                'p_strValue' => $l_ad['user'],
                'p_strClass' => 'input-small'
            ],
            'archivePass'   => [
                'p_strValue' => '', // No use in diaplaying the PW here (ID-3436).
                'p_strClass' => 'input-small'
            ],
            'buttonRestore' => [
                'p_bDisabled' => 0
            ]
        ];

        isys_application::instance()->template->assign("btnLabelExecute", isys_application::instance()->container->get('language')
            ->get("LC__UNIVERSAL__EXECUTE"))
            ->assign("archiveDest", $l_ad["dest"])
            ->assign("content_title", isys_application::instance()->container->get('language')
                ->get("LC__UNIVERSAL__RESTORE"))
            ->smarty_tom_add_rule("tom.content.bottom.buttons.*.p_bInvisible=1")
            ->smarty_tom_add_rules("tom.content.bottom.content", $l_rules)
            ->include_template('contentbottomcontent', 'content/bottom/content/module__logbook__restore.tpl');
    }

    /**
     * Set up the filter for the logbook
     */
    private function setupFilter($p_daoLogbook)
    {
        $l_rules = [
            'filter_source'  => [
                'p_arData'   => $p_daoLogbook->getSources(),
                'p_strClass' => 'input-mini'
            ],
            'filter_alert'   => [
                'p_arData'   => $p_daoLogbook->getAlertlevels(),
                'p_strClass' => 'input-mini'
            ],
            'filter_type'    => [
                'p_arData'   => [
                    "0" => isys_application::instance()->container->get('language')
                        ->get("LC__CMDB__CATG__SYSTEM"),
                    "1" => isys_application::instance()->container->get('language')
                        ->get("LC__NAVIGATION__MENUTREE__BUTTON_OBJECT_VIEW")
                ],
                'p_strClass' => 'input-mini'
            ],
            'filter_archive' => [
                'p_arData'   => [
                    "0" => isys_application::instance()->container->get('language')
                        ->get("LC__RECORD_STATUS__NORMAL"),
                    "1" => isys_application::instance()->container->get('language')
                        ->get("LC__UNIVERSAL__ARCHIVE")
                ],
                'p_strClass' => 'input-mini'
            ]
        ];

        if (isset($_POST["filter_source"])) {
            $l_rules["filter_source"]["p_strSelectedID"] = $_POST["filter_source"];
        } else {
            $l_rules["filter_source"]["p_strSelectedID"] = "-1";
        }

        if (isset($_POST["filter_alert"])) {
            $l_rules["filter_alert"]["p_strSelectedID"] = $_POST["filter_alert"];
        } else {
            $l_rules["filter_alert"]["p_strSelectedID"] = "-1";
        }

        if (isset($_POST["filter_type"])) {
            $l_rules["filter_type"]["p_strSelectedID"] = $_POST["filter_type"];
        } else {
            $l_rules["filter_type"]["p_strSelectedID"] = "-1";
        }

        if (isset($_POST["filter_from__HIDDEN"])) {
            $l_rules["filter_from"]["p_strValue"] = $_POST["filter_from__HIDDEN"];
        }

        if (isset($_POST["filter_to__HIDDEN"])) {
            $l_rules["filter_to"]["p_strValue"] = $_POST["filter_to__HIDDEN"];
        }

        if (isset($_POST["filter_archive"])) {
            $l_rules["filter_archive"]["p_strSelectedID"] = $_POST["filter_archive"];
        } else {
            $l_rules["filter_archive"]["p_strSelectedID"] = "0";
        }

        if (isset($_POST["filter_user__HIDDEN"])) {
            $l_rules["filter_user"]["p_strSelectedID"] = $_POST["filter_user__HIDDEN"];
        }

        isys_application::instance()->template->activate_editmode()
            ->smarty_tom_add_rules("tom.content.bottom.content", $l_rules);
    }

    /**
     * @return isys_auth_logbook
     */
    public static function getAuth()
    {
        return isys_auth_logbook::instance();
    }
}

<?php

use idoit\Context\Context;

/**
 * i-doit
 *
 * CMDB Action: Category creation.
 *
 * @package     i-doit
 * @subpackage  CMDB_Actions
 * @author      Andre Woesten <awoesten@i-doit.de>
 * @version     1.0
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_action_category_create extends isys_cmdb_action_category implements isys_cmdb_action
{
    /**
     * Category title.
     *
     * @var  string
     */
    protected $m_category_title;

    /**
     * Changes for the logbook.
     *
     * @var  array
     */
    protected $m_changes = [];

    /**
     * Handle method.
     *
     * @param   isys_cmdb_dao $p_dao
     * @param   array         $p_data
     *
     * @throws  isys_exception_cmdb
     */
    public function handle(isys_cmdb_dao $p_dao, &$p_data)
    {
        Context::instance()
            ->setContextTechnical(Context::CONTEXT_DAO_CREATE)
            ->setGroup(Context::CONTEXT_GROUP_DAO)
            ->setContextCustomer(Context::CONTEXT_DAO_CREATE);

        /** @var  $l_actionproc  isys_cmdb_action_processor */
        $l_actionproc = $p_data["__ACTIONPROC"];

        /** @var  $l_dao  isys_cmdb_dao_category */
        $l_dao = $p_data[0];

        /** @var  $l_ui  isys_cmdb_ui_category */
        $l_ui = $p_data[1];

        // Check, if the user is allowed to create a new category entry.
        $this->check_right($_GET[C__CMDB__GET__OBJECT], $l_dao->get_category_const());

        if (!isset($l_dao) || !$l_dao) {
            throw new isys_exception_cmdb("Could not handle category update (DAO class not set)", C__CMDB__ERROR__ACTION_PROCESSOR);
        }

        if (!isset($l_ui) || !$l_ui) {
            throw new isys_exception_cmdb("Could not handle category update (UI class not set)", C__CMDB__ERROR__ACTION_PROCESSOR);
        }

        $l_newid = null;
        $l_default_template = null;
        $l_dao->set_strLogbookSQL('');
        $l_object_id = $_GET[C__CMDB__GET__OBJECT];

        // Get default template, if configured.
        if ($_POST['useTemplate'] == 1) {
            // Get template module.
            $l_template_module = new isys_module_templates();
            $l_default_template = $l_dao->get_default_template_by_obj_type($_GET[C__CMDB__GET__OBJECTTYPE]);
        }

        // Sanitize Data.
        $_POST = $l_dao->sanitize_post_data();

        // If it is the global category or the object is locked, just cancel.
        if (($l_dao->get_category_id() === defined_or_default('C__CATG__GLOBAL') && $l_dao->get_category_type() === C__CMDB__CATEGORY__TYPE_GLOBAL) || $this->object_is_locked()) {
            if (!$this->object_is_locked() && $_POST['useTemplate'] == 1) {
                if (isset($l_template_module) && is_object($l_template_module)) {
                    $l_template_module->create_from_template(
                        [$l_default_template],
                        $_GET[C__CMDB__GET__OBJECTTYPE],
                        $_POST['C__CATG__GLOBAL_TITLE'],
                        $l_object_id,
                        false,
                        1,
                        ''
                    );
                }
            }

            $l_actionproc->result_push(null);

            return;
        } elseif ($l_dao->get_category_id() === defined_or_default('C__CATG__OVERVIEW') && $_POST['useTemplate'] == 1) {
            if (isset($l_template_module) && is_object($l_template_module)) {
                $l_template_module->create_from_template([$l_default_template], $_GET[C__CMDB__GET__OBJECTTYPE], $_POST['C__CATG__GLOBAL_TITLE'], $l_object_id, false, 1, '');
            }
        }

        if ($l_dao->get_object_browser_category() !== false) {
            // It is a category with only an object browser create changes
            $this->set_changes($_POST, $l_dao);
        }

        // Emit category signal (beforeCreateCategoryEntry).
        isys_component_signalcollection::get_instance()
            ->emit("mod.cmdb.beforeCreateCategoryEntry", $l_dao->get_category_id(), $l_object_id, $l_dao, $this->m_changes);

        // Check if this is an ObjectBrowserReceiver
        if (is_a($l_dao, '\\idoit\\Module\\Cmdb\\Interfaces\\ObjectBrowserReceiver')) {
            if (isset($_POST[C__POST__POPUP_RECEIVER]) && !empty($_POST[C__POST__POPUP_RECEIVER])) {
                $l_post_key = C__POST__POPUP_RECEIVER;
            } else {
                $l_objBrowser_key = 'assigned_object';
                if ($l_dao->get_object_browser_category() === true && $l_dao->get_object_browser_property() !== '') {
                    // Retrieve property which is the object browser
                    $l_objBrowser_key = $l_dao->get_object_browser_property();
                }

                $l_post_key = ($l_dao->get_property_by_key($l_objBrowser_key)[C__PROPERTY__UI][C__PROPERTY__UI__ID] ?: '') . '__HIDDEN';

                if (!isset($_POST[$l_post_key])) {
                    foreach ($_POST as $k => $v) {
                        if ($k != 'savedCheckboxes' && !empty($v) && isys_format_json::is_json_array($v)) {
                            $l_post_key = $k;
                            break;
                        }
                    }
                }
            }

            if (isset($_POST[$l_post_key])) {
                if (isys_format_json::is_json_array($_POST[$l_post_key])) {
                    $l_data = isys_format_json::decode($_POST[$l_post_key]);
                } else {
                    $l_data = explode(',', $_POST[$l_post_key]);
                }

                /** @var $l_dao \idoit\Module\Cmdb\Interfaces\ObjectBrowserReceiver */
                $l_dao->attachObjects((int)$_GET[C__CMDB__GET__OBJECT], $l_data);

                $l_newid = true;
            }
        }

        if (empty($l_post_key) || $l_post_key === '__HIDDEN') {
            if (!$l_dao->is_multivalued()) {
                // Create the record!
                $l_newid = $l_dao->create_connector($l_dao->get_table(), (int)$l_object_id);

                if (is_null($l_newid)) {
                    $l_newid = -1;
                }
            } else {
                $l_newid = -1;
            }
        }

        $l_dao->object_changed($_GET[C__CMDB__GET__OBJECT]);

        if ($l_dao->get_object_browser_category() !== false) {
            // Set changes into the db
            $this->set_logbook_entries($l_dao);
        }

        // Emit category signal (afterCreateCategoryEntry).
        isys_component_signalcollection::get_instance()
            ->emit("mod.cmdb.afterCreateCategoryEntry", $l_dao->get_category_id(), $l_newid, ($l_newid > 0), $l_object_id, $l_dao, $this->m_changes);

        // This was a standard save :-)
        $l_actionproc->result_push($l_newid);
    }

    /**
     * Sets logbook changes
     *
     * @param                        $p_post
     * @param isys_cmdb_dao_category $p_dao
     *
     * @author Van Quyen Hoang <qhoang@synetics.de>
     */
    private function set_changes($p_post, isys_cmdb_dao_category $p_dao)
    {
        if (isset($_GET[C__CMDB__GET__OBJECT])) {
            $l_new_objects = $l_new_objects_arr = $l_current_objects = [];

            if (isset($p_post[C__POST__POPUP_RECEIVER])) {
                $l_new_objects = (is_string($p_post[C__POST__POPUP_RECEIVER])) ? isys_format_json::decode($p_post[C__POST__POPUP_RECEIVER]) : $p_post[C__POST__POPUP_RECEIVER];
            }

            $l_property_arr = $p_dao->get_properties();
            foreach ($l_property_arr as $l_property_key => $l_property) {
                if ($l_property[C__PROPERTY__INFO][C__PROPERTY__INFO__TYPE] == C__PROPERTY__INFO__TYPE__OBJECT_BROWSER ||
                    $l_property[C__PROPERTY__INFO][C__PROPERTY__INFO__TYPE] == C__PROPERTY__INFO__TYPE__N2M) {
                    $l_referenced_property_key = $l_property_key;

                    if (isset($l_property[C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][1])) {
                        switch ($l_property[C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][1]) {
                            case 'relation_connection':
                            case 'connection':
                                $l_object_field = 'isys_connection__isys_obj__id';
                                break;
                            default:
                                if (isset($l_property[C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD_ALIAS]) &&
                                    $l_property[C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD_ALIAS] != '') {
                                    $l_object_field = $l_property[C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD_ALIAS];
                                } else {
                                    $l_object_field = $l_property[C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD];
                                }
                                break;
                        }
                        break;
                    } else {
                        return;
                    }
                }
            }

            if (isset($l_referenced_property_key)) {
                $this->m_category_title = ($p_dao->get_category_type() ==
                    C__CMDB__CATEGORY__TYPE_GLOBAL) ? $p_dao->get_catg_name_by_id_as_string($p_dao->get_category_id()) : $p_dao->get_cats_name_by_id_as_string($p_dao->get_category_id());
                // @See ID-6423 fallback if popupReceiver is empty
                if (empty($l_new_objects)) {
                    if (isset($p_post[$l_property[C__PROPERTY__UI][C__PROPERTY__UI__ID] . '__HIDDEN'])) {
                        $l_key = $l_property[C__PROPERTY__UI][C__PROPERTY__UI__ID] . '__HIDDEN';
                    } else {
                        $l_key = $l_property[C__PROPERTY__UI][C__PROPERTY__UI__ID];
                    }

                    if (isys_format_json::is_json_array($p_post[$l_key])) {
                        $l_new_objects = isys_format_json::decode($p_post[$l_key]);
                    } elseif (strpos($p_post[$l_key], ',') !== false) {
                        $l_new_objects = explode(',', $p_post[$l_key]);
                    }
                }

                $l_res = $p_dao->get_data(null, $_GET[C__CMDB__GET__OBJECT]);

                if (strpos($l_object_field, 'isys_obj__id') === false) {
                    // IDs are no object ids
                    $l_helper = new $l_property[C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][0]([], $p_dao->get_database_component());
                    $l_method = $l_property[C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][1];
                }

                while ($l_row = $l_res->get_row()) {
                    if (isset($l_method)) {
                        $l_data = $l_helper->$l_method($l_row[$l_object_field]);
                        $l_current_objects[] = (isset($l_data['ref_title'])) ? $l_data['ref_title'] : $l_data['title'];
                    } else {
                        $l_current_objects[] = $p_dao->get_obj_name_by_id_as_string($l_row[$l_object_field]);
                    }
                }

                if (is_array($l_new_objects) && count($l_new_objects) > 0) {
                    foreach ($l_new_objects as $l_obj_id) {
                        if (isset($l_method)) {
                            $l_data = $l_helper->$l_method($l_obj_id);
                            $l_new_objects_arr[] = (isset($l_data['ref_title'])) ? $l_data['ref_title'] : $l_data['title'];
                        } else {
                            $l_new_objects_arr[] = $p_dao->get_obj_name_by_id_as_string($l_obj_id);
                        }
                    }
                }

                $l_from = implode(', ', $l_current_objects);
                $l_to = implode(', ', $l_new_objects_arr);

                if ($l_from != $l_to) {
                    $l_changes = [
                        get_class($p_dao) . '::' . $l_referenced_property_key => [
                            'from' => $l_from,
                            'to'   => $l_to
                        ]
                    ];
                    $this->m_changes = $l_changes;
                }
            }
        }
    }

    /**
     * Insert logbook changes into the db
     *
     * @param $p_dao
     */
    private function set_logbook_entries($p_dao)
    {
        if (is_countable($this->m_changes) && count($this->m_changes) > 0) {
            $l_event_manager = isys_event_manager::getInstance();
            $l_changes_compressed = serialize($this->m_changes);

            $l_event_manager->triggerCMDBEvent(
                'C__LOGBOOK_EVENT__CATEGORY_CHANGED',
                $p_dao->get_strLogbookSQL(),
                $_GET[C__CMDB__GET__OBJECT],
                isys_glob_get_param(C__CMDB__GET__OBJECTTYPE),
                isys_application::instance()->container->get('language')
                    ->get($this->m_category_title),
                $l_changes_compressed
            );
        }
    }
}

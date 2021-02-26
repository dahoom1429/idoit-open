<?php

use idoit\Context\Context;

/**
 * Action: Object creation
 *
 * @package     i-doit
 * @subpackage  CMDB_Actions
 * @author      Andre Woesten <awoesten@i-doit.de>
 * @version     1.0
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_action_object_create implements isys_cmdb_action
{
    /**
     * Create object handler.
     *
     * @param  isys_cmdb_dao $p_dao
     * @param  array         &$p_data
     */
    public function handle(isys_cmdb_dao $p_dao, &$p_data)
    {
        $l_mod_event_manager = isys_event_manager::getInstance();
        $l_gets = isys_module_request::get_instance()
            ->get_gets();

        list($p_objtype_id) = $p_data;

        /**
         * @var isys_cmdb_action_processor
         */
        $l_actionproc = $p_data['__ACTIONPROC'];

        $l_obj_type = $p_dao->get_object_type($p_objtype_id);

        if (!isys_auth_cmdb::instance()
            ->is_allowed_to(isys_auth::CREATE, 'OBJ_IN_TYPE/' . $l_obj_type['isys_obj_type__const'])) {
            if (!isys_auth_cmdb_objects::instance()
                ->is_object_type_allowed($l_obj_type['isys_obj_type__id'], isys_auth::CREATE)) {
                new isys_exception_auth(isys_application::instance()->container->get('language')
                    ->get('LC__AUTH__EXCEPTION__MISSING_ACTION_RIGHT_FROM_MODULE', [
                        'Create',
                        isys_application::instance()->container->get('language')
                            ->get(isys_auth_cmdb::instance()
                                ->get_module_title())
                    ]));
            }
        }

        try {
            Context::instance()
                ->setContextTechnical(Context::CONTEXT_OBJECT_CREATE)
                ->setGroup(Context::CONTEXT_GROUP_OBJECT)
                ->setContextCustomer(Context::CONTEXT_OBJECT_CREATE);

            $l_default_template = $p_dao->get_default_template_by_obj_type($p_objtype_id);

            isys_component_signalcollection::get_instance()
                ->emit("mod.cmdb.beforeInsertObject", $p_dao, $p_objtype_id, $l_default_template);

            // Create the object.
            $l_new_objid = $p_dao->insert_new_obj($p_objtype_id, false);

            if ($l_default_template) {
                $l_obj_title = '';

                $l_obj_data = $p_dao->get_type_by_object_id($l_new_objid)
                    ->get_row();
                $l_obj_status = $l_obj_data['isys_obj__status'];
                if (!in_array($l_obj_status, [C__RECORD_STATUS__TEMPLATE, C__RECORD_STATUS__MASS_CHANGES_TEMPLATE]) && (!isset($_POST['template']) || !$_POST['template'])) {
                    $l_obj_type = $l_obj_data['isys_obj__isys_obj_type__id'];
                    if (isset($l_obj_data['isys_obj_type__use_template_title']) && $l_obj_data['isys_obj_type__use_template_title']) {
                        $l_obj_title = $p_dao->obj_get_title_by_id_as_string($l_default_template);
                    }
                    $l_template_module = new isys_module_templates();
                    $l_template_module->create_from_template([$l_default_template], $l_obj_type, $l_obj_title, $l_new_objid, false, 1, '');
                    $p_dao->set_object_status($l_new_objid, C__RECORD_STATUS__BIRTH);
                }
            }

            isys_component_signalcollection::get_instance()
                ->emit("mod.cmdb.afterInsertObject", $p_dao, $p_objtype_id, $l_new_objid);
        } catch (Exception $e) {
            isys_glob_display_error($e->getMessage());
            die;
        }

        if ($l_new_objid != -1) {
            $l_mod_event_manager->triggerCMDBEvent('C__LOGBOOK_EVENT__OBJECT_CREATED', '-object initialized-', $l_new_objid, $p_objtype_id);
        } else {
            $l_mod_event_manager->triggerCMDBEvent('C__LOGBOOK_EVENT__OBJECT_CREATED__NOT', '', null, $p_objtype_id);
        }

        $l_gets[C__CMDB__GET__OBJECT] = $l_new_objid;
        isys_module_request::get_instance()
            ->_internal_set_private('m_get', $l_gets);

        if (method_exists($l_actionproc, 'result_push')) {
            $l_actionproc->result_push($l_new_objid);
        }
    }
}

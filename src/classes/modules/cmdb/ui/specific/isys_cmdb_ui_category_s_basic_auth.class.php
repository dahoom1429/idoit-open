<?php

/**
 * i-doit
 *
 * UI: specific category for the basic auth-system implementation.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 * @since       1.1
 * @author      Leonard Fischer <lfischer@i-doit.com>
 */
class isys_cmdb_ui_category_s_basic_auth extends isys_cmdb_ui_category_specific
{
    /**
     * Process method.
     *
     * @param  isys_cmdb_dao_category_s_basic_auth $p_cat
     *
     * @return array|void
     */
    public function process(isys_cmdb_dao_category $p_cat)
    {
        $l_obj_id = $_GET[C__CMDB__GET__OBJECT];
        $l_obj_type = $p_cat->get_objTypeID($l_obj_id);

        isys_auth_auth::instance()
            ->check(isys_auth::SUPERVISOR, 'MODULE/C__MODULE__AUTH');

        // Collect the available modules.
        $l_modules = [];
        $l_module_res = isys_module_manager::instance()
            ->get_modules(null, null, true);

        if (is_countable($l_module_res) && count($l_module_res) > 0) {
            while ($l_row = $l_module_res->get_row()) {
                $l_auth_instance = isys_module_manager::instance()
                    ->get_module_auth($l_row['isys_module__id']);

                if (get_class($l_auth_instance) == 'isys_auth_system' && defined($l_row['isys_module__const']) &&
                    constant($l_row['isys_module__const']) != defined_or_default('C__MODULE__SYSTEM')) {
                    continue;
                }

                // We only want to select modules, which have their own auth-classes.
                if ($l_auth_instance && class_exists($l_row['isys_module__class'])) {
                    $l_modules[$l_row['isys_module__const']] = isys_application::instance()->container->get('language')
                        ->get($l_row['isys_module__title']);
                }
            }
        }

        // Collect the available rights.
        $l_rights = isys_auth::get_rights();

        foreach ($l_rights as &$l_right) {
            $l_right['title'] = isys_application::instance()->container->get('language')->get($l_right['title']);
        }

        // Collect the paths.
        $l_paths = [];
        $l_res = $p_cat->get_data(null, $l_obj_id);

        while ($l_row = $l_res->get_row()) {
            if (!in_array($l_row['isys_auth__type'], (array)$l_paths[$l_row['isys_module__const']])) {
                if (is_array($l_paths[$l_row['isys_module__const']])) {
                    $l_paths[$l_row['isys_module__const']] = array_merge($l_paths[$l_row['isys_module__const']], isys_helper::split_bitwise($l_row['isys_auth__type']));
                } else {
                    $l_paths[$l_row['isys_module__const']] = isys_helper::split_bitwise($l_row['isys_auth__type']);
                }

                // @see ID-4792  The "array_unique" is not necessary, but it shrinks the array immensely!
                $l_paths[$l_row['isys_module__const']] = array_values(array_unique($l_paths[$l_row['isys_module__const']]));
            }
        }

        // Now collect the inherited paths of persongroups (if the current object is a person).
        $l_inherited_paths = [];

        // @todo Maybe this "check" should look for the specific category instead of the object-type.
        if ($l_obj_type == defined_or_default('C__OBJTYPE__PERSON')) {
            $l_pg_dao = new isys_cmdb_dao_category_s_person_assigned_groups($this->get_database_component());

            $l_pg_res = $l_pg_dao->get_data(null, $l_obj_id);

            while ($l_row = $l_pg_res->get_row()) {
                $l_res = $p_cat->get_data(null, $l_row['isys_person_2_group__isys_obj__id__group']);

                while ($l_row2 = $l_res->get_row()) {
                    if (is_array($l_inherited_paths[$l_row2['isys_module__const']])) {
                        $l_inherited_paths[$l_row2['isys_module__const']] = array_merge(
                            $l_inherited_paths[$l_row2['isys_module__const']],
                            isys_helper::split_bitwise($l_row2['isys_auth__type'])
                        );
                    } else {
                        $l_inherited_paths[$l_row2['isys_module__const']] = isys_helper::split_bitwise($l_row2['isys_auth__type']);
                    }

                    // @see ID-4792  The "array_unique" is not necessary, but it shrinks the array immensely!
                    $l_inherited_paths[$l_row2['isys_module__const']] = array_values(array_unique($l_inherited_paths[$l_row2['isys_module__const']]));
                }
            }
        }

        $this->deactivate_commentary()
            ->get_template_component()
            ->assign('rights', $l_rights)
            ->assign('modules', $l_modules)
            ->assign('paths', $l_paths)
            ->assign('inherited_paths', $l_inherited_paths)
            ->assign('edit_mode', (int)isys_glob_is_edit_mode());
    }
}

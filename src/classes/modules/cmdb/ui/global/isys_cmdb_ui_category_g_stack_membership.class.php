<?php

/**
 * i-doit
 *
 * CMDB Global category stack membership.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @since       1.7
 * @author      Leonard Fischer <lfischer@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_ui_category_g_stack_membership extends isys_cmdb_ui_category_g_virtual
{
    /**
     * Processes view/edit mode.
     *
     * @param   isys_cmdb_dao_category $p_cat
     *
     * @return  void
     */
    public function process(isys_cmdb_dao_category $p_cat)
    {
        global $g_dirs;

        $l_obj_id = $this->m_object_id ?: $_GET[C__CMDB__GET__OBJECT];
        $l_dao = isys_cmdb_dao_category_g_stack_member::instance($this->m_database_component);

        $l_stacks = [];
        $l_is_stacked = false;
        $l_res = $l_dao->get_stacking_meta($l_obj_id);

        isys_component_template_navbar::getInstance()
            ->set_active(false, C__NAVBAR_BUTTON__EDIT)
            ->set_visible(false, C__NAVBAR_BUTTON__EDIT);

        if (is_countable($l_res) && count($l_res)) {
            $l_is_stacked = true;
            $l_quickinfo = new isys_ajax_handler_quick_info;

            while ($l_row = $l_res->get_row()) {
                $l_members = [];

                $l_member_res = $l_dao->get_data(null, $l_row['isys_obj__id'], '', null, C__RECORD_STATUS__NORMAL);

                while ($l_member_row = $l_member_res->get_row()) {
                    $l_members[] = $l_quickinfo->get_quick_info($l_member_row['isys_obj__id'], '<img src="' . $g_dirs['images'] . 'icons/silk/link.png" class="vam mr5" />' .
                        isys_application::instance()->container->get('language')
                            ->get($l_member_row['isys_obj_type__title']) . ' &raquo; ' . $l_member_row['isys_obj__title'], C__LINK__OBJECT);
                }

                if (count($l_members)) {
                    $l_stacks[] = [
                        'quickinfo' => $l_quickinfo->get_quick_info($l_row['isys_obj__id'], '<img src="' . $g_dirs['images'] . 'icons/silk/link.png" class="vam mr5" />' .
                            isys_application::instance()->container->get('language')
                                ->get($l_row['isys_obj_type__title']) . ' &raquo; ' . $l_row['isys_obj__title'], C__LINK__CATG, false,
                            [C__CMDB__GET__CATG => defined_or_default('C__CATG__STACK_MEMBER')]),
                        'members'   => $l_members
                    ];
                }
            }
        }

        $this->get_template_component()
            ->assign('is_stacked', $l_is_stacked)
            ->assign('stacks', $l_stacks);

        parent::process($p_cat);
    }
}

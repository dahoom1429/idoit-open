<?php

/**
 * i-doit
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Van Quyen Hoang <qhoang@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_ui_category_g_database_folder extends isys_cmdb_ui_category_global
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
        $navbar = isys_component_template_navbar::getInstance();
        $navbar
            ->deactivate_all_buttons()
            ->hide_all_buttons();

        $dao = isys_cmdb_dao_category_g_database_folder::instance(isys_application::instance()->container->get('database'));

        if ($this->get_template()) {
            $this->deactivate_commentary()
                ->get_template_component()
                ->assign('rootNode', (int)$_GET[C__CMDB__GET__OBJECT])
                ->include_template('contentbottomcontent', $this->get_template());
        }
    }
}

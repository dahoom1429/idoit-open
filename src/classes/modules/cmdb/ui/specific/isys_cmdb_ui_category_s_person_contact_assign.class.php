<?php

/**
 * i-doit
 *
 * CMDB Person: Specific category
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Van Quyen Hoang <qhoang@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_ui_category_s_person_contact_assign extends isys_cmdb_ui_category_specific
{
    /**
     * Define if this category is multivalued or not.
     *
     * @author  Dennis Stücken <dstuecken@synetics.de>
     * @return  boolean
     */
    public function is_multivalued()
    {
        return true;
    }

    /**
     * Process method.
     *
     * @param   isys_cmdb_dao_category $p_cat
     *
     * @return  void
     * @throws  isys_exception_cmdb
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function process(isys_cmdb_dao_category $p_cat)
    {
        $l_rules = [];

        $this->fill_formfields($p_cat, $l_rules, $p_cat->get_general_data());

        // Apply rules.
        $this->get_template_component()
            ->smarty_tom_add_rules("tom.content.bottom.content", $l_rules);
    }

    /**
     * Process-list method.
     *
     * @param   isys_cmdb_dao_category $p_cat
     *
     * @param null                     $p_get_param_override
     * @param null                     $p_strVarName
     * @param null                     $p_strTemplateName
     * @param bool                     $p_bCheckbox
     * @param bool                     $p_bOrderLink
     * @param null                     $p_db_field_name
     *
     * @return bool
     * @throws Exception
     * @throws isys_exception_general
     * @author  Dennis Stücken <dstuecken@synetics.de>
     */
    public function process_list(
        isys_cmdb_dao_category &$p_cat,
        $p_get_param_override = null,
        $p_strVarName = null,
        $p_strTemplateName = null,
        $p_bCheckbox = true,
        $p_bOrderLink = true,
        $p_db_field_name = null
    ) {
        /**
         * Method refactored
         *
         * @see ID-6042
         */
        // Call parent process_list()
        parent::process_list($p_cat, $p_get_param_override, $p_strVarName, $p_strTemplateName, $p_bCheckbox, $p_bOrderLink, $p_db_field_name);

        // Let us disable quick purge button
        isys_component_template_navbar::getInstance()
            ->set_active(false, C__NAVBAR_BUTTON__QUICK_PURGE)
            ->set_visible(false, C__NAVBAR_BUTTON__QUICK_PURGE);

        return true;
    }
}

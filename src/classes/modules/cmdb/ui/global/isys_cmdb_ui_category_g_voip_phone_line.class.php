<?php

/**
 * i-doit
 *
 * CMDB Global category voice over IP phones.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Leonard Fischer <lfischer@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 * @since       1.0
 */
class isys_cmdb_ui_category_g_voip_phone_line extends isys_cmdb_ui_category_global
{
    /**
     * Method for defining this category as multivalued.
     *
     * @author  Leonard Fischer <lfischer@i-doit.org>
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
     * @global  array                  $index_includes
     * @author  Leonard Fischer <lfischer@i-doit.org>
     * @return array|void
     */
    public function process(isys_cmdb_dao_category $p_cat)
    {
        global $index_includes;

        $l_rules = [];

        $this// This is a new method - It tries to fills the category forms automatically.
        ->fill_formfields($p_cat, $l_rules, $p_cat->get_general_data())// Display the commentary.
        ->activate_commentary($p_cat);

        // Assign all the data to the template.
        $this->get_template_component()
            ->smarty_tom_add_rules("tom.content.bottom.content", $l_rules);

        $index_includes["contentbottomcontent"] = $this->get_template();
    }

    /**
     * Process list method.
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
     * @author  Leonard Fischer <lfischer@i-doit.org>
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
        $this->list_view("isys_catg_voip_phone_line", $_GET[C__CMDB__GET__OBJECT], isys_cmdb_dao_list_catg_voip_phone_line::build($this->get_database_component(), $p_cat));

        return true;
    }

    /**
     * UI constructor.
     *
     * @param   isys_component_template $p_template
     *
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function __construct(isys_component_template &$p_template)
    {
        parent::__construct($p_template);
        $this->set_template("catg__voip_phone_line.tpl");
    }
}
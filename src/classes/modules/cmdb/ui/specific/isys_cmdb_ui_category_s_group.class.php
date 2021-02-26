<?php

/**
 * i-doit
 *
 * UI: specific category group
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Dennis Blümer <dbluemer@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_ui_category_s_group extends isys_cmdb_ui_category_specific
{
    /**
     * Process the list.
     *
     * @param   isys_cmdb_dao_category &$p_cat
     * @param   array                  $p_get_param_override
     * @param   string                 $p_strVarName
     * @param   string                 $p_strTemplateName
     * @param   boolean                $p_bCheckbox
     * @param   boolean                $p_bOrderLink
     * @param   string                 $p_db_field_name
     *
     * @return  null
     * @throws  isys_exception_general
     * @author  Dennis Blümer <dbluemer@i-doit.org>
     * @see     isys_cmdb_ui_category::process_list()
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
        if (!($p_cat instanceof isys_cmdb_dao_category_s_group)) {
            return null;
        }

        $l_type = $p_cat->get_group_type($_GET[C__CMDB__GET__OBJECT]);

        if ($l_type == 0) {
            $this->object_browser_as_new([
                'name'                                          => 'C__CMDB__CATS__GROUP__OBJECT',
                isys_popup_browser_object_ng::C__MULTISELECTION => true,
                isys_popup_browser_object_ng::C__FORM_SUBMIT    => true,
                isys_popup_browser_object_ng::C__RETURN_ELEMENT => C__POST__POPUP_RECEIVER,
                isys_popup_browser_object_ng::C__DATARETRIEVAL  => [
                    [get_class($p_cat), "get_connected_objects"],
                    $_GET[C__CMDB__GET__OBJECT]
                ]
            ], "LC__UNIVERSAL__OBJECT_ADD_REMOVE", "LC__UNIVERSAL__OBJECT_ADD_REMOVE_DESCRIPTION");

            return parent::process_list($p_cat, $p_get_param_override, $p_strVarName, $p_strTemplateName, $p_bCheckbox, $p_bOrderLink, $p_db_field_name);
        } else {
            isys_component_template_navbar::getInstance()
                ->hide_all_buttons()
                ->deactivate_all_buttons();

            parent::process_list($p_cat, $p_get_param_override, $p_strVarName, $p_strTemplateName, false, $p_bOrderLink, $p_db_field_name);

            $this->m_template->smarty_tom_add_rule("tom.content.navbar.cRecStatus.p_bDisabled=1")
                ->smarty_tom_add_rule("tom.content.navbar.cRecStatus.p_bInvisible=1")
                ->smarty_tom_add_rule("tom.content.top.filter.p_bDisabled=1");
        }

        return null;
    }

    /**
     * Constructor.
     *
     * @param   isys_component_template $p_template
     *
     * @author  Dennis Blümer <dbluemer@i-doit.org>
     */
    public function __construct(isys_component_template &$p_template)
    {
        $this->set_template("cats__group.tpl");
        parent::__construct($p_template);
    }
}

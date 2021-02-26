<?php

/**
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Van Quyen Hoang <qhoang@i-doit.org>
 * @version     0.9.9.7
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_ui_category_g_assigned_cards extends isys_cmdb_ui_category_global
{
    /**
     * @param isys_cmdb_dao_category $p_cat
     *
     * @return null
     * @throws isys_exception_general
     */
    public function process(isys_cmdb_dao_category $p_cat)
    {
        return $this->process_list($p_cat);
    }

    /**
     * Process list method.
     *
     * @param isys_cmdb_dao_category $p_cat
     * @param null                   $p_get_param_override
     * @param null                   $p_strVarName
     * @param null                   $p_strTemplateName
     * @param bool                   $p_bCheckbox
     * @param bool                   $p_bOrderLink
     * @param null                   $p_db_field_name
     *
     * @return  null
     * @throws isys_exception_general
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
        $this->object_browser_as_new([
            'name'                                          => 'C__CATG__ASSIGNED_CARDS__OBJ',
            isys_popup_browser_object_ng::C__MULTISELECTION => true,
            // multiselection: false is default
            isys_popup_browser_object_ng::C__FORM_SUBMIT    => true,
            // should isys_form gets submitted after accepting? default is no.
            isys_popup_browser_object_ng::C__CAT_FILTER     => "C__CATG__SIM_CARD",
            isys_popup_browser_object_ng::C__RETURN_ELEMENT => C__POST__POPUP_RECEIVER,
            // this is the html element where the selected objects are transfered into (as JSON)
            isys_popup_browser_object_ng::C__DATARETRIEVAL  => [[get_class($p_cat), "get_assigned_object"], $_GET[C__CMDB__GET__OBJECT]]
            // this is where the browser tries to get a preselection from
        ], "LC__UNIVERSAL__OBJECT_ADD_REMOVE", "LC__UNIVERSAL__OBJECT_ADD_REMOVE_DESCRIPTION");

        return parent::process_list($p_cat, $p_get_param_override, $p_strVarName, $p_strTemplateName, $p_bCheckbox, $p_bOrderLink, $p_db_field_name);
    }

    /**
     * Constructor.
     *
     * @todo   Is this a reversed-category or can the constructor be removed?
     *
     * @param  isys_component_template $p_template
     *
     * @throws isys_exception_ui
     */
    public function __construct(isys_component_template &$p_template)
    {
        $this->set_template("catg__assigned_cards.tpl");
        parent::__construct($p_template);
    }
}
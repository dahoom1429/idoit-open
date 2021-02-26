<?php

/**
 * i-doit
 *
 * DAO: Group memberships list for persons.
 *
 * @package     i-doit
 * @subpackage  CMDB_Category_lists
 * @author      Dennis Blümer <dbluemer@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_list_cats_person_assigned_groups extends isys_component_dao_category_table_list
{
    /**
     * Counter for the dialog smarty-plugin.
     *
     * @var  integer
     */
    protected $dialogRoleCounter = 0;

    /**
     *
     * @return  integer
     */
    public function get_category()
    {
        return defined_or_default('C__CATS__PERSON_ASSIGNED_GROUPS');
    }

    /**
     *
     * @return  integer
     */
    public function get_category_type()
    {
        return C__CMDB__CATEGORY__TYPE_SPECIFIC;
    }

    /**
     *
     * @param   null    $p_str
     * @param   integer $p_objID
     *
     * @return  isys_component_dao_result
     * @author  Dennis Stücken <dstuecken@i-doit.org>
     */
    public function get_result($p_str = null, $p_objID, $p_unused = null)
    {
        $l_cRecStatus = empty($this->get_rec_status()) ? C__RECORD_STATUS__NORMAL : $this->get_rec_status();

        $l_query = "SELECT *, isys_catg_mail_addresses_list__title AS isys_cats_person_group_list__email_address
			FROM isys_person_2_group
			INNER JOIN isys_obj ON isys_obj__id = isys_person_2_group__isys_obj__id__group 
			AND isys_obj.isys_obj__status = " . $this->convert_sql_int($l_cRecStatus) . " 
			INNER JOIN isys_cats_person_group_list ON isys_obj__id = isys_cats_person_group_list__isys_obj__id
			LEFT JOIN isys_catg_mail_addresses_list ON isys_obj__id = isys_catg_mail_addresses_list__isys_obj__id AND isys_catg_mail_addresses_list__primary = 1
			WHERE isys_person_2_group__isys_obj__id__person = " . $this->convert_sql_id($p_objID) . "
			GROUP BY isys_cats_person_group_list__isys_obj__id;";

        return $this->retrieve($l_query);
    }

    /**
     * Flag for the rec status dialog.
     *
     * @return  boolean
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function rec_status_list_active()
    {
        return false;
    }

    /**
     * Build header for the list.
     *
     * @return  array
     */
    public function get_fields()
    {
        return [
            "isys_cats_person_group_list__title"         => "LC__CMDB__CATG__ODEP_OBJ",
            "isys_cats_person_group_list__email_address" => "LC__CONTACT__GROUP_EMAIL_ADDRESS",
            "isys_cats_person_group_list__phone"         => "LC__CONTACT__GROUP_PHONE",
            "isys_cats_person_group_list__ldap_group"    => "LC__CONTACT__GROUP_LDAP_GROUP",
            "person_group_member_tag"                    => "LC__CONTACT__PERSON_ROLE"
        ];
    }

    /**
     * @return  string
     */
    public function make_row_link()
    {
        return isys_helper_link::create_url([
            C__GET__MODULE_ID    => defined_or_default('C__MODULE__CMDB'),
            C__CMDB__GET__OBJECT => "[{isys_person_2_group__isys_obj__id__group}]",
            C__CMDB__GET__CATS   => defined_or_default('C__CATS__PERSON_GROUP')
        ]);
    }

    /**
     * @param array $entry
     */
    public function modify_row(&$entry)
    {
        $params = [
            'p_strPopupType'    => 'dialog_plus',
            'p_strSelectedID'   => $entry['isys_person_2_group__isys_contact_tag__id'],
            'p_strTable'        => 'isys_contact_tag',
            'p_strClass'        => 'input-block',
            'p_bInfoIconSpacer' => 0,
            'name'              => 'C__CATS__PERSON_GROUP_MEMBER__CONTACT_TAG_' . $this->dialogRoleCounter++,
            'p_onChange'        => "new Ajax.Updater('infoBox', '?ajax=1&call=update_person_group_member_tag&" . C__CMDB__GET__OBJECT . "=" . $_GET[C__CMDB__GET__OBJECT] .
                "', { parameters: " . "{ conId:'" . $entry['isys_person_2_group__id'] .
                "', valId:this.value}, method:'post', onComplete:function(){ $('infoBox').highlight();}});",
            'p_bEditMode'       => true
        ];
        $entry['person_group_member_tag'] = (new isys_smarty_plugin_f_popup)->set_parameter($params);
    }
}

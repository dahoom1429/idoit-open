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
class isys_cmdb_dao_list_cats_person_group_members extends isys_component_dao_category_table_list
{
    /**
     * Counter for the dialog smarty-plugin.
     *
     * @var  integer
     */
    protected $dialogRoleCounter = 0;

    /**
     * Gets category identifier.
     *
     * @return  integer
     */
    public function get_category()
    {
        return defined_or_default('C__CATS__PERSON_GROUP_MEMBERS');
    }

    /**
     * Gets category type.
     *
     * @return  integer
     */
    public function get_category_type()
    {
        return C__CMDB__CATEGORY__TYPE_SPECIFIC;
    }

    /**
     * General get_result.
     *
     * @param   null    $p_str
     * @param   integer $p_objID
     *
     * @return  isys_component_dao_result
     */
    public function get_result($p_str = null, $p_objID, $p_unused = null)
    {
        $l_query = "SELECT *, isys_catg_mail_addresses_list__title AS isys_cats_person_list__mail_address FROM isys_person_2_group
			INNER JOIN isys_cats_person_list ON isys_cats_person_list__isys_obj__id = isys_person_2_group__isys_obj__id__person
			LEFT JOIN isys_catg_mail_addresses_list ON isys_catg_mail_addresses_list__isys_obj__id = isys_cats_person_list__isys_obj__id AND isys_catg_mail_addresses_list__primary = 1
			LEFT JOIN isys_connection ON isys_connection__id = isys_cats_person_list__isys_connection__id
			LEFT JOIN isys_obj ON isys_obj__id = isys_connection__isys_obj__id
            LEFT JOIN isys_contact_tag ON isys_contact_tag__id = isys_person_2_group__isys_contact_tag__id
			WHERE isys_person_2_group__isys_obj__id__group = " . $this->convert_sql_id($p_objID) . "
			GROUP BY isys_cats_person_list__isys_obj__id;";

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
     * Retrieve the table fields.
     *
     * @return  array
     */
    public function get_fields()
    {
        return [
            'isys_cats_person_list__first_name'    => 'LC__CATG__CONTACT_FIRSTNAME',
            'isys_cats_person_list__last_name'     => 'LC__CONTACT__PERSON_LAST_NAME',
            'isys_cats_person_list__department'    => 'LC__CONTACT__PERSON_DEPARTMENT',
            'isys_cats_person_list__phone_company' => 'LC__CONTACT__PERSON_TELEPHONE_COMPANY',
            'isys_cats_person_list__mail_address'  => 'LC__CONTACT__PERSON_MAIL_ADDRESS',
            'isys_obj__title'                      => 'LC__CONTACT__PERSON_ASSIGNED_ORGANISATION',
            'isys_cats_person_list__title'         => 'LC__CONTACT__PERSON_USER_NAME',
            'person_group_member_tag'              => 'LC__CONTACT__PERSON_ROLE'
        ];
    }

    /**
     * Make row link method.
     *
     * @return  string
     */
    public function make_row_link()
    {
        return isys_helper_link::create_url([
            C__GET__MODULE_ID    => defined_or_default('C__MODULE__CMDB'),
            C__CMDB__GET__OBJECT => '[{isys_cats_person_list__isys_obj__id}]',
            C__CMDB__GET__CATS   => defined_or_default('C__CATS__PERSON')
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
            'name'              => 'C__CATG__CONTACT_TAG_' . $this->dialogRoleCounter++,
            'p_onChange'        => "new Ajax.Updater('infoBox', '?ajax=1&call=update_person_group_member_tag&" . C__CMDB__GET__OBJECT . "=" . $_GET[C__CMDB__GET__OBJECT] .
                "', { parameters: " . "{ conId:'" . $entry['isys_person_2_group__id'] .
                "', valId:this.value}, method:'post', onComplete:function(){ $('infoBox').highlight();}});",
            'p_bEditMode'       => true
        ];
        $entry['person_group_member_tag'] = (new isys_smarty_plugin_f_popup)->set_parameter($params);
    }
}

<?php

/**
 * i-doit
 *
 * @package     i-doit
 * @subpackage  CMDB_Category_lists
 * @author      Van Quyen Hoang <qhoang@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_list_cats_organization_person extends isys_component_dao_category_table_list
{
    /**
     * Return constant of category.
     *
     * @return  integer
     */
    public function get_category()
    {
        return defined_or_default('C__CATS__ORGANIZATION_PERSONS');
    }

    /**
     * Return constant of category type.
     *
     * @return  integer
     */
    public function get_category_type()
    {
        return C__CMDB__CATEGORY__TYPE_SPECIFIC;
    }

    /**
     * Return Category Data
     *
     *
     * @param   string  $p_strTable
     * @param   integer $p_obj_id
     * @param   integer $p_cRecStatus
     *
     * @return  isys_component_dao_result
     */
    public function get_result($p_strTable = null, $p_obj_id, $p_cRecStatus = null)
    {
        $l_sql = 'SELECT t1.*, p1.*, t2.isys_obj__title AS organame, isys_connection__id, mail.isys_catg_mail_addresses_list__title AS isys_cats_person_list__mail_address
			FROM isys_obj AS t1
			LEFT JOIN isys_cats_person_list AS p1 ON p1.isys_cats_person_list__isys_obj__id = t1.isys_obj__id
			LEFT JOIN isys_catg_mail_addresses_list AS mail ON mail.isys_catg_mail_addresses_list__isys_obj__id = t1.isys_obj__id AND mail.isys_catg_mail_addresses_list__primary = 1
			LEFT JOIN isys_connection ON isys_connection__id = p1.isys_cats_person_list__isys_connection__id
			LEFT JOIN isys_obj AS t2 ON isys_connection__isys_obj__id = t2.isys_obj__id
			WHERE TRUE';

        if ($p_obj_id !== null) {
            $l_sql .= ' AND isys_connection__isys_obj__id = ' . $this->convert_sql_id($p_obj_id);
        } else {
            $l_sql .= ' AND isys_connection__isys_obj__id = ' . $this->convert_sql_id($_GET[C__CMDB__GET__OBJECT]);
        }

        $l_cRecStatus = ($p_cRecStatus === null) ? $this->get_rec_status() : $p_cRecStatus;

        $l_sql .= ' AND t1.isys_obj__status = ' . $this->convert_sql_int($l_cRecStatus);

        return $this->retrieve($l_sql . ';');
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
     * Returns array with table headers.
     *
     * @return  array
     */
    public function get_fields()
    {
        return [
            "isys_cats_person_list__isys_obj__id"  => "ID",
            "isys_cats_person_list__first_name"    => "LC__CONTACT__PERSON_FIRST_NAME",
            "isys_cats_person_list__last_name"     => "LC__CONTACT__PERSON_LAST_NAME",
            "isys_cats_person_list__department"    => "LC__CONTACT__PERSON_DEPARTMENT",
            "isys_cats_person_list__phone_company" => "LC__CONTACT__PERSON_TELEPHONE_COMPANY",
            "isys_cats_person_list__mail_address"  => "LC__CONTACT__PERSON_MAIL_ADDRESS",
            "organame"                             => "LC__CONTACT__PERSON_ASSIGNED_ORGANISATION"
        ];
    }

    /**
     *
     *
     * @param   integer $p_obj_id
     *
     * @return  string
     */
    public function get_assigned_person_ids_as_string($p_obj_id = null)
    {
        $l_sql = 'SELECT t1.*, p1.*, t2.isys_obj__title AS organame
			FROM isys_obj AS t1
			LEFT JOIN isys_cats_person_list AS p1 ON p1.isys_cats_person_list__isys_obj__id = t1.isys_obj__id
			LEFT JOIN isys_connection ON isys_connection__id = p1.isys_cats_person_list__isys_connection__id
			LEFT JOIN isys_obj AS t2 ON isys_connection__isys_obj__id = t2.isys_obj__id
			WHERE TRUE';

        if ($p_obj_id !== null) {
            $l_sql .= ' AND isys_connection__isys_obj__id = ' . $this->convert_sql_id($p_obj_id);
        } else {
            $l_sql .= ' AND isys_connection__isys_obj__id = ' . $this->convert_sql_id($_GET[C__CMDB__GET__OBJECT]);
        }

        $l_sql .= ' AND t1.isys_obj__status = ' . $this->convert_sql_int(C__RECORD_STATUS__NORMAL);

        $l_obj_ids_as_string = [];

        $l_res = $this->retrieve($l_sql);

        if (is_countable($l_res) && count($l_res) > 0) {
            while ($l_row = $l_res->get_row()) {
                $l_obj_ids_as_string[] = $l_row["isys_obj__id"];
            }
        }

        return implode(',', $l_obj_ids_as_string);
    }

    /**
     * Make Row Link.
     *
     * @return  string
     */
    public function make_row_link()
    {
        $l_arrGetUrl = [
            C__CMDB__GET__VIEWMODE => C__CMDB__VIEW__CATEGORY,
            C__CMDB__GET__TREEMODE => C__CMDB__VIEW__TREE_OBJECT,
            C__CMDB__GET__OBJECT   => "[{isys_cats_person_list__isys_obj__id}]",
            C__CMDB__GET__CATS     => defined_or_default('C__CATS__PERSON')
        ];

        return isys_helper_link::create_url($l_arrGetUrl);
    }
}
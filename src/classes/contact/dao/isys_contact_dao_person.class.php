<?php

/**
 * i-doit
 *
 * DAO: goup list
 *
 * @package    i-doit
 * @subpackage Contact
 * @copyright  synetics GmbH
 * @license    http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_contact_dao_person extends isys_contact_dao
{
    /**
     * @param   string  $p_strSqlFilter
     * @param   integer $p_nRecStatus
     *
     * @return  isys_component_dao_result
     * @author  Niclas Potthast <npotthast@i-doit.org>
     */
    public function get_data($p_strSqlFilter = null, $p_nRecStatus = C__RECORD_STATUS__NORMAL)
    {
        $l_strSQL = "SELECT *, mail_person.isys_catg_mail_addresses_list__title AS isys_cats_person_list__mail_address, mail_pgroup.isys_catg_mail_addresses_list__title AS isys_cats_person_list__mail_address
			FROM isys_cats_person_list
			INNER JOIN isys_obj ON isys_obj__id = isys_cats_person_list__isys_obj__id
			LEFT JOIN isys_person_2_group ON isys_person_2_group__isys_obj__id__person = isys_cats_person_list__isys_obj__id
			LEFT JOIN isys_cats_person_group_list ON isys_person_2_group__isys_obj__id__group = isys_cats_person_group_list__isys_obj__id
			LEFT JOIN isys_catg_mail_addresses_list AS mail_person ON mail_person.isys_catg_mail_addresses_list__isys_obj__id = isys_cats_person_list__isys_obj__id AND mail_person.isys_catg_mail_addresses_list__primary = 1
			LEFT JOIN isys_catg_mail_addresses_list AS mail_pgroup ON mail_pgroup.isys_catg_mail_addresses_list__isys_obj__id = isys_cats_person_group_list__isys_obj__id AND mail_pgroup.isys_catg_mail_addresses_list__primary = 1
			WHERE TRUE ";

        if ($p_nRecStatus > 0) {
            $l_strSQL .= " AND isys_obj__status = " . $this->convert_sql_int($p_nRecStatus);
        }

        if ($p_strSqlFilter !== null) {
            $l_strSQL .= " AND (" . $p_strSqlFilter . ") ";
        }

        return $this->retrieve($l_strSQL . " GROUP BY isys_cats_person_list__id;");
    }

    /**
     * Returns a person dao by its username.
     *
     * @param   string $p_username
     *
     * @return  isys_component_dao_result
     * @author  Dennis Stücken <dstuecken@synetics.de>
     */
    public function get_person_by_username($p_username)
    {
        $l_sql = "SELECT *, isys_catg_mail_addresses_list__title AS isys_cats_person_list__mail_address
			FROM isys_cats_person_list
			LEFT JOIN isys_catg_mail_addresses_list ON isys_catg_mail_addresses_list__isys_obj__id = isys_cats_person_list__isys_obj__id AND isys_catg_mail_addresses_list__primary = 1
			WHERE BINARY LOWER(isys_cats_person_list__title) = LOWER(" . $this->convert_sql_text($p_username) . ");";

        return $this->retrieve($l_sql);
    }

    /**
     * Checks if a username already exists.
     *
     * @param   string $p_title
     *
     * @return  boolean
     * @author  Dennis Stücken <dstuecken@synetics.de>
     */
    public function exists($p_title)
    {
        $result = $this->get_person_by_username($p_title);
        $count = is_countable($result) ? count($result) : 0;
        return $count > 0;
    }

    /**
     * Get GroupData by groupID.
     *
     * @param   integer $p_intPersonID
     *
     * @return  isys_component_dao_result
     */
    public function get_data_by_id($p_intPersonID)
    {
        return $this->get_data("isys_cats_person_list__isys_obj__id = " . $this->convert_sql_id($p_intPersonID));
    }

    /**
     * Get userid by ldap_dn.
     *
     * @param   string $p_ldap_dn
     *
     * @return  integer
     */
    public function get_user_id_by_ldap_dn($p_ldap_dn)
    {
        if (!is_null($p_ldap_dn)) {
            $l_data = $this->get_data("isys_cats_person_list__ldap_dn = '$p_ldap_dn'")
                ->__to_array();

            return $l_data["isys_cats_person_list__id"];
        } else {
            return false;
        }
    }

    /**
     * Get all GroupRecords for one (internal) Person by PersonID.
     *
     * @param   integer $p_intPersonID
     *
     * @return  isys_component_dao_result
     */
    public function get_groups_by_id($p_intPersonID)
    {
        $l_strSQL = "SELECT *
			FROM isys_cats_person_group_list
			LEFT JOIN isys_person_2_group ON isys_person_2_group__isys_obj__id__group = isys_cats_person_group_list__isys_obj__id
			WHERE TRUE
			AND isys_person_2_group__isys_obj__id__person = " . $this->convert_sql_id($p_intPersonID) . ";";

        return $this->retrieve($l_strSQL);
    }

    /**
     * Return the organisationID (or null if not assigned) by PersonID.
     *
     * @param   integer $p_intPersonID
     *
     * @return  integer  OrganisationID or null if not assigned or not found
     */
    public function get_organisation_by_id_as_int($p_intPersonID)
    {
        $l_intOrganisationId = null;

        $l_result = $this->get_data_by_id($p_intPersonID);

        if (is_countable($l_result) && count($l_result) > 0) {
            $l_data = $l_result->get_row();

            $l_dao = new isys_cmdb_dao_connection($this->get_database_component());
            $l_res = $l_dao->get_connection($l_data["isys_cats_person_list__isys_connection__id"]);
            $l_row = $l_res->get_row();

            $l_intOrganisationId = $l_row["isys_connection__isys_obj__id"];
        }

        return $l_intOrganisationId;
    }

    /**
     * Assign an organisation to a person.
     *
     * @param   integer $p_intPersonID
     * @param   mixed   $p_intOrganisationID
     *
     * @return  boolean
     * @author  Niclas Potthast <npotthast@i-doit.org>
     */
    public function set_organisation_by_id($p_intPersonID, $p_intOrganisationID)
    {
        $l_dao = new isys_cmdb_dao_connection($this->get_database_component());
        $l_id = $l_dao->add_connection($p_intOrganisationID);

        $l_strSql = "UPDATE isys_cats_person_list
			SET isys_cats_person_list__isys_connection__id = " . $this->convert_sql_id($l_id) . "
			WHERE isys_cats_person_list__isys_obj__id = " . $this->convert_sql_id($p_intPersonID) . ";";

        return ($this->update($l_strSql) && $this->apply_update());
    }
}
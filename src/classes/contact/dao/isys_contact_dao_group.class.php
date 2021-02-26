<?php

/**
 * i-doit
 *
 * DAO: goup list
 *
 * @package     i-doit
 * @subpackage  Contact
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_contact_dao_group extends isys_contact_dao
{
    /**
     * Get PersonData by groupID.
     *
     * @param   integer $p_intGroupID
     *
     * @return  isys_component_dao_result
     */
    public function get_persons_by_id($p_intGroupID)
    {
        $l_strSQL = 'SELECT * FROM isys_obj
			INNER JOIN isys_person_2_group ON isys_person_2_group__isys_obj__id__person = isys_obj__id
			WHERE isys_person_2_group__isys_obj__id__group = ' . $this->convert_sql_id($p_intGroupID) . ';';

        return $this->retrieve($l_strSQL);
    }
}
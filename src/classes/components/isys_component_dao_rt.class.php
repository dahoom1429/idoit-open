<?php

/**
 * i-doit
 *
 * DAO for RT data exchange
 *
 * @package    i-doit
 * @subpackage Components
 * @author     Dennis Blümer <dbluemer@i-doit.org>
 * @copyright  Copyright synetics GmbH
 * @license    http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_component_dao_rt extends isys_component_dao
{

    /**
     * Insert a new custom field value into RTs custom fields
     *
     * @param int $p_objTypeID ID of the referenced object type
     *
     * @return int - id of the inserted custom field value or false
     */
    public function insert_customfield_value($p_objTypeID)
    {
        $l_query = "SELECT id FROM CustomFields WHERE Description = " . $this->convert_sql_id($p_objTypeID);
        $l_res = $this->retrieve($l_query);
        $l_row = $l_res->get_row();
        $l_cfID = $l_row["id"];

        $l_query = "SELECT id FROM Users WHERE Name = 'RT_System'";
        $l_res = $this->retrieve($l_query);
        $l_row = $l_res->get_row();
        $l_userID = $l_row["id"];
        $l_return = false;

        $l_update = "INSERT INTO CustomFieldValues SET " . "CustomField   = " . $this->convert_sql_id($l_cfID) . ", " . "Creator       = " . $this->convert_sql_id($l_userID) .
            ", " . "Created       = NOW(), " . "LastUpdatedBy = " . $this->convert_sql_id($l_userID) . ", " . "LastUpdated   = NOW()";

        if ($this->update($l_update)) {
            $l_return = $this->get_last_insert_id();
            $this->apply_update();
        }

        return $l_return;
    }

    /**
     * Sets the name of the custom field value.
     *
     * @param int $p_cfID
     * @param string $p_name
     * @param string $p_oldName
     *
     * @return bool
     * @throws isys_exception_dao
     */
    public function set_customfield_name($p_cfID, $p_name, $p_oldName)
    {
        $l_update = 'UPDATE CustomFieldValues 
            SET Name = ' . $this->convert_sql_text($p_name) . '
            WHERE id = ' . $this->convert_sql_id($p_cfID) . ';';

        if (!$this->update($l_update)) {
            return false;
        }

        /**
         * Update all ObjectCustomFieldValues, due this damn RT does not
         * use references, but puts in the name as plain text
         */
        $l_update = 'UPDATE ObjectCustomFieldValues 
            SET Content = ' . $this->convert_sql_text($p_name) . '
            WHERE Content = ' . $this->convert_sql_text($p_oldName) . ';';

        return ($this->update($l_update) && $this->apply_update());
    }

    /**
     * Delete a custom field value for the IT Service field in RT
     *
     * @param int $p_id
     *
     * @return boolean true on success, else false
     */
    public function delete_customfield_value($p_id)
    {
        $l_update = "DELETE FROM CustomFieldValues WHERE id = " . $this->convert_sql_id($p_id);

        return ($this->update($l_update) && $this->apply_update());
    }

    /**
     * Get all tickets associated with the given custom field ID
     *
     * @param string $p_cfValue
     *
     * @return isys_component_dao_result
     * @throws isys_exception_database
     */
    public function get_tickets($p_cfValue)
    {
        $l_query = 'SELECT Tickets.Status, Tickets.Subject, Tickets.Created, Tickets.LastUpdated, Users.Name
            FROM Tickets
            INNER JOIN ObjectCustomFieldValues ON ObjectCustomFieldValues.ObjectId = Tickets.id AND ObjectCustomFieldValues.ObjectType = \'RT::Ticket\'
            INNER JOIN Users ON Users.id = Tickets.Creator AND ObjectCustomFieldValues.Content = ' . $this->convert_sql_text($p_cfValue) . ';';

        return $this->retrieve($l_query);
    }

    public function __construct(isys_component_database $p_db)
    {
        parent::__construct($p_db);
    }
}

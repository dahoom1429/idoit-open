<?php

/**
 * i-doit
 *
 * Connection DAO
 *
 * @package     i-doit
 * @subpackage  CMDB_Low-Level_API
 * @author      Dennis Stuecken <dstuecken@synetics.de
 * @version     1.0
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_connection extends isys_cmdb_dao
{
    /**
     * Retrieves a connection by its id
     *
     * @param   integer $p_connection_id
     *
     * @return  isys_component_dao_result
     */
    public function get_connection($p_connection_id)
    {
        return $this->retrieve('SELECT * FROM isys_connection WHERE isys_connection__id = ' . $this->convert_sql_id($p_connection_id) . ';');
    }

    /**
     * Retrieves the object id by connection id.
     *
     * @param   integer $p_connection_id
     *
     * @return  integer
     */
    public function get_object_id_by_connection($p_connection_id)
    {
        $l_row = $this->get_connection($p_connection_id)
            ->get_row();

        return $l_row["isys_connection__isys_obj__id"];
    }

    /**
     * Adds a new connection to isys_obj__id
     *
     * @param   integer $p_object_id
     *
     * @return  mixed  Integer with last inserted ID on success, null on failure.
     */
    public function add_connection($p_object_id)
    {
        $l_sql = "INSERT INTO isys_connection SET isys_connection__isys_obj__id = " . $this->convert_sql_id($p_object_id) . ";";

        if ($this->update($l_sql) && $this->apply_update()) {
            return $this->get_last_insert_id();
        }

        return null;
    }

    /**
     * Updates an existing connection.
     *
     * @param   integer $p_connection_id
     * @param   integer $p_object_id
     *
     * @return  bool
     */
    public function update_connection($p_connection_id, $p_object_id)
    {
        if (empty($p_connection_id)) {
            return $this->add_connection($p_object_id);
        }

        $l_sql = "UPDATE isys_connection
			SET isys_connection__isys_obj__id = " . $this->convert_sql_id($p_object_id) . "
			WHERE isys_connection__id = " . $this->convert_sql_id($p_connection_id);

        if ($this->update($l_sql) && $this->apply_update()) {
            return $p_connection_id;
        }

        return false;
    }

    /**
     * Attaches a connection.
     *
     * @param   string  $p_list_table
     * @param   integer $p_list_id
     * @param   integer $p_object_id
     *
     * @throws  isys_exception_cmdb
     * @return  mixed
     */
    public function attach_connection($p_list_table, $p_list_id, $p_object_id, $p_field = null)
    {
        if ($p_list_table) {
            $l_id = $this->add_connection($p_object_id);

            if ($p_field === null) {
                $l_set = "SET " . $p_list_table . "__isys_connection__id = " . $this->convert_sql_id($l_id);
            } else {
                $l_set = "SET " . $p_field . " = " . $this->convert_sql_id($l_id);
            }

            $l_sql = "UPDATE " . $p_list_table . " " . $l_set . "
				WHERE " . $p_list_table . "__id = " . $this->convert_sql_id($p_list_id) . ";";

            if ($this->update($l_sql) && $this->apply_update()) {
                return $l_id;
            }

            return false;
        } else {
            throw new isys_exception_cmdb("Coult not attach connection. List table is empty.");
        }
    }

    /**
     * Method for retrieving the connection id from the specified table. Creates a new connection if not existing.
     *
     * @param      $p_list_table
     * @param      $p_list_id
     * @param null $p_connection_field
     *
     * @return bool|mixed
     * @throws isys_exception_cmdb
     */
    public function retrieve_connection($p_list_table, $p_list_id, $p_connection_field = null)
    {
        if ($p_list_table) {
            if ($p_connection_field) {
                $l_connection_field = $p_connection_field;
            } else {
                $l_connection_field = $p_list_table . '__isys_connection__id';
            }

            $l_sql = "SELECT " . $l_connection_field . " FROM " . $p_list_table . " WHERE " . $p_list_table . "__id = " . $this->convert_sql_id($p_list_id);
            $l_return = $this->retrieve($l_sql)
                ->get_row_value($l_connection_field);

            return $l_return;
        } else {
            throw new isys_exception_cmdb("Coult not retrieve connection id. List table is empty.");
        }
    }

    /**
     * Deletes a connection.
     *
     * @param   integer $p_connectionID
     *
     * @return  boolean
     */
    public function delete($p_connectionID)
    {
        return ($this->update("DELETE FROM isys_connection WHERE isys_connection__id = " . $this->convert_sql_id($p_connectionID) . ";") && $this->apply_update());
    }

    /**
     * After ranking an object rank, also rank connected category entries
     *
     * @param isys_cmdb_dao $p_cmdb_dao
     * @param               $p_direction
     * @param array         $p_objects
     */
    public function unidirectionalConnectionRanking($p_cmdb_dao, $p_direction, array $p_objects)
    {
        $database = isys_application::instance()->container->database;

        // @See ID-5491 only rank entries in tables which are multi valued
        $allMultivalueTables = $p_cmdb_dao->getAllCategoryTablesAsArray(true);

        $query = "
            SELECT TABLE_NAME, COLUMN_NAME
            FROM information_schema.KEY_COLUMN_USAGE
            WHERE REFERENCED_TABLE_NAME = 'isys_connection' AND
                REFERENCED_COLUMN_NAME = 'isys_connection__id' AND
                TABLE_SCHEMA = " . $p_cmdb_dao->convert_sql_text($database->get_db_name()) . " AND
                TABLE_NAME IN (" . implode(', ', array_map([$p_cmdb_dao, 'convert_sql_text'], $allMultivalueTables)) . ")";

        $referencedColumns = $database->retrieveArrayFromResource($database->query($query));

        foreach ($p_objects as $object) {
            $changedObject = $p_cmdb_dao->get_object((int)$object, true)
                ->__as_array();

            $connectionIds = $database->retrieveArrayFromResource($database->query("SELECT isys_connection__id
                 FROM isys_connection
                 WHERE isys_connection__isys_obj__id = $object"));

            foreach ($connectionIds as $connectionId) {
                foreach ($referencedColumns as $columnData) {
                    $p_cmdb_dao->update(sprintf(
                        'UPDATE %s SET %s__status = %d WHERE %s = %s;',
                        $columnData['TABLE_NAME'],
                        $columnData['TABLE_NAME'],
                        $changedObject[0]['isys_obj__status'],
                        $columnData['COLUMN_NAME'],
                        $connectionId['isys_connection__id']
                    ));
                }
            }
        }

        $p_cmdb_dao->apply_update();
    }
}

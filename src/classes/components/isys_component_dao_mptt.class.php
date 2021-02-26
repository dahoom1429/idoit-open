<?php
/**
 * i-doit
 *
 * MPTT
 *
 * @package    i-doit
 * @subpackage Components
 * @author     Andre Woesten <awoesten@i-doit.de>
 * @version    1.0
 * @copyright  synetics GmbH
 * @license    http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */

/**
 * Notes:
 * ------
 * * Action stack ist eine Hash table, wobei als Index die Node-ID verwendet
 *   wird, um einen direkten Zusammenhang zwischen Stack und Node zu bilden.
 *   Ein Eintrag beinhaltet wiederrum ein Array mit den Aktionsdaten.
 * * Die Konstanten befinden sich im Konstantenmanager deklariert
 */
class isys_component_dao_mptt extends isys_component_dao
{
    private $m_actionstack;

    /**
     * Cache for method has_children
     *
     * @var array
     */
    private $m_child_cache = [];

    private $m_initialized;

    private $m_src_field_const;

    private $m_src_field_id;

    private $m_src_field_left;

    private $m_src_field_node;

    private $m_src_field_parent_id;

    private $m_src_field_right;

    private $m_src_tbl;

    public function get_next_node_as_integer()
    {
        $l_q = "SELECT " . "MAX({$this->m_src_field_node}) " . "FROM " . "{$this->m_src_tbl};";

        $l_nextdao = $this->retrieve($l_q);
        if ($l_nextdao->num_rows()) {
            list($l_nodeid) = $l_nextdao->get_row(IDOIT_C__DAO_RESULT_TYPE_ROW);

            return $l_nodeid + 1;
        }

        return null;
    }

    public function get_by_const($p_const)
    {
        if (is_numeric($p_const)) {
            $l_q = "SELECT " . "* " . "FROM " . "{$this->m_src_tbl} " . "WHERE " . "{$this->m_src_field_const}=" . "'" . $p_const . "'";

            return $this->retrieve($l_q);
        }

        return null;
    }

    /**
     * @param $p_nodeid
     *
     * @return isys_component_dao_result
     * @throws Exception
     * @throws isys_exception_database
     */
    public function get_by_node_id($p_nodeid)
    {
        $l_q = "SELECT * FROM " . "{$this->m_src_tbl} " . "INNER JOIN isys_obj " . "ON isys_obj__id = " . "{$this->m_src_field_node} " . "WHERE " .
            "{$this->m_src_field_node}=" . (int)$p_nodeid . " " . "ORDER BY isys_obj__title";

        return $this->retrieve($l_q);
    }

    /**
     * @param $p_parentid
     *
     * @return isys_component_dao_result
     * @throws Exception
     * @throws isys_exception_database
     */
    public function get_by_parent_id($p_parentid)
    {
        $l_q = "SELECT " . "* " . "FROM " . "{$this->m_src_tbl} " . "WHERE " . "{$this->m_src_field_parent_id}=" . (int)$p_parentid;

        return $this->retrieve($l_q);
    }

    /**
     * Get all children of $p_parent_id, this includes an entry of the node itself ($p_parent_id) as well
     *
     * @param $p_parent_id
     *
     * @author Dennis Stücken <dstuecken@i-doit.de>
     *
     * @return isys_component_dao_result|null
     */
    public function get_tree($p_parent_id)
    {
        $l_node = $this->get_by_node_id($p_parent_id)
            ->get_row();

        if ($l_node && isset($l_node[$this->m_src_field_left]) && isset($l_node[$this->m_src_field_right])) {
            return $this->get_left_by_left_right($l_node[$this->m_src_field_left], $l_node[$this->m_src_field_right], C__RECORD_STATUS__NORMAL);
        }

        return null;
    }

    /**
     * Get all children of $p_parent_id, this does NOT includes an entry of the node itself ($p_parent_id) as well
     *
     * @param $p_parent_id
     * @param null $p_nRecStatus
     *
     * @author Dennis Stücken <dstuecken@i-doit.de>
     *
     * @return isys_component_dao_result|null
     */
    public function get_children($p_parent_id, $p_nRecStatus = C__RECORD_STATUS__NORMAL)
    {
        $l_node = $this->get_by_node_id($p_parent_id)
            ->get_row();

        if ($l_node && isset($l_node[$this->m_src_field_left]) && isset($l_node[$this->m_src_field_right])) {
            return $this->get_left_by_left_right($l_node[$this->m_src_field_left], $l_node[$this->m_src_field_right], $p_nRecStatus, false);
        }

        return null;
    }

    /**
     * Recursively searches for $p_children in tree
     *
     * @param $p_root_object_id
     * @param $p_child_object_id
     *
     * @author Dennis Stücken <dstuecken@i-doit.de>
     *
     * @return isys_component_dao_result|null
     */
    public function has_children($p_root_object_id, $p_child_object_id)
    {
        if (isset($this->m_child_cache[$p_root_object_id][$p_child_object_id])) {
            return $this->m_child_cache[$p_root_object_id][$p_child_object_id];
        }

        if ($p_root_object_id > 0) {
            $l_node = $this->get_by_node_id($p_root_object_id)
                ->get_row();

            if ($l_node && isset($l_node[$this->m_src_field_left]) && isset($l_node[$this->m_src_field_right])) {
                $l_q = "SELECT " . $this->m_src_field_id . ", " . $this->m_src_field_node . ", " . $this->m_src_field_parent_id . ", " . $this->m_src_field_const . ", " .
                    $this->m_src_field_left . ", " . $this->m_src_field_right . ", " . "isys_obj__title, " . "isys_obj_type__id, " . "isys_obj_type__title " .

                    "FROM " . "{$this->m_src_tbl} ";

                $l_q .= "INNER JOIN isys_obj ON isys_obj__id = {$this->m_src_tbl}__isys_obj__id " .
                    "INNER JOIN isys_obj_type ON isys_obj__isys_obj_type__id = isys_obj_type__id ";

                $l_q .= "WHERE {$this->m_src_field_left} BETWEEN " . $l_node[$this->m_src_field_left] . " AND " . $l_node[$this->m_src_field_right] . ' AND ' .
                    $this->m_src_field_node . ' = ' . $this->convert_sql_int($p_child_object_id);

                if ($this->retrieve($l_q)
                        ->num_rows() > 0) {
                    $this->m_child_cache[$p_root_object_id][$p_child_object_id] = true;

                    return true;
                } else {
                    $this->m_child_cache[$p_root_object_id][$p_child_object_id] = false;
                }
            }
        }

        return false;
    }

    /**
     * Get items between left and right
     *
     * @param      $p_left
     * @param      $p_right
     * @param null $p_nRecStatus
     *
     * @return isys_component_dao_result|null
     * @throws Exception
     * @throws isys_exception_database
     */
    public function get_left_by_left_right($p_left, $p_right, $p_nRecStatus = null, $p_include_self = true)
    {
        if (is_numeric($p_left) && is_numeric($p_right)) {
            $l_q = "SELECT " . $this->m_src_field_id . ", " . $this->m_src_field_node . ", " . $this->m_src_field_parent_id . ", " . $this->m_src_field_const . ", " .
                $this->m_src_field_left . ", " . $this->m_src_field_right . ", " . "isys_obj__title, " . "isys_obj_type__id, " . "isys_obj_type__title " .

                "FROM " . "{$this->m_src_tbl} ";

            $l_q .= "INNER JOIN isys_obj ON isys_obj__id = {$this->m_src_tbl}__isys_obj__id " . "INNER JOIN isys_obj_type ON isys_obj__isys_obj_type__id = isys_obj_type__id ";

            $l_q .= "WHERE {$this->m_src_field_left} BETWEEN " . $p_left . " AND " . $p_right;

            if (!is_null($p_nRecStatus)) {
                $l_q .= " AND isys_obj__status = " . $this->convert_sql_int($p_nRecStatus) . " ";
            }

            if (!$p_include_self) {
                $l_q .= ' AND isys_obj__id != ' . $this->convert_sql_int($p_left);
            }

            $l_q .= " ORDER BY {$this->m_src_field_left} ASC";

            return $this->retrieve($l_q);
        }

        return null;
    }

    public function get_outer_by_left_right($p_left, $p_right)
    {
        if (is_numeric($p_left) && is_numeric($p_right)) {
            $l_q = "SELECT " . "*" . "FROM " . "{$this->m_src_tbl} " . "WHERE " . "{$this->m_src_field_left} < $p_left " . "AND " . "{$this->m_src_field_right} > $p_right " .
                "GROUP BY " . "{$this->m_src_tbl}__isys_obj__id " . "ORDER BY " . "{$this->m_src_field_left} ASC" . ";";

            return $this->retrieve($l_q);
        }

        return null;
    }

    public function action_stack_add($p_action, $p_data)
    {
        if ($p_action > C__MPTT__ACTION_BEGIN && $p_action < C__MPTT__ACTION_END) {
            array_push($this->m_actionstack, [
                $p_action,
                $p_data
            ]);

            return true;
        }

        return false;
    }

    /**
     * Public wrapper function for recursive writing algorithm
     *
     * @param object $p_callback
     *
     * @return mixed
     */
    public function write($p_callback = null)
    {
        return $this->_write($p_callback);
    }

    public function read($p_id, $p_callback = null, $p_userdata = null)
    {
        $l_resultset = [];
        $l_treeres = $this->get_by_node_id($p_id);
        $l_use_callback = (!is_null($p_callback));

        if ($l_treeres) {
            if ($l_treeres->num_rows()) {
                $l_rstack = [];

                /* Fetch data of origin object */
                $l_startrow = $l_treeres->get_row();

                if ($l_startrow[$this->m_src_field_left] > $l_startrow[$this->m_src_field_right]) {
                    $l_left = $l_startrow[$this->m_src_field_left];
                    $l_startrow[$this->m_src_field_left] = $l_startrow[$this->m_src_field_right];
                    $l_startrow[$this->m_src_field_right] = $l_left;
                }

                /* Fetch results of sub-tree */
                $l_subres = $this->get_left_by_left_right($l_startrow[$this->m_src_field_left], $l_startrow[$this->m_src_field_right], C__RECORD_STATUS__NORMAL);

                if ($l_subres) {
                    /* Iterate through result set */
                    while ($l_subrow = $l_subres->get_row()) {

                        /* If there are entries in the stack ... */
                        if (count($l_rstack)) {
                            /* Remove all entries smaller than the current one */
                            while ($l_rstack[count($l_rstack) - 1] < $l_subrow[$this->m_src_field_right]) {
                                if (count($l_rstack) - 1 < 0) {
                                    break;
                                }
                                array_pop($l_rstack);
                            }
                        }

                        /* Decide whether to use callback or append it to the result set */
                        if ($l_use_callback) {
                            $p_callback->mptt_read(
                                $this->m_src_tbl,
                                count($l_rstack), /* Level */
                                $l_subrow[$this->m_src_field_id],
                                $l_subrow[$this->m_src_field_node],
                                $l_subrow[$this->m_src_field_parent_id],
                                $l_subrow[$this->m_src_field_const],
                                $l_subrow[$this->m_src_field_left],
                                $l_subrow[$this->m_src_field_right],
                                $p_userdata,
                                $l_subrow["isys_obj__title"]
                            );
                        } else {
                            $l_resultset[] = $l_subrow;
                        }

                        /* Add current right entry to stack */
                        $l_rstack[] = $l_subrow[$this->m_src_field_right];
                    }
                }

                if ($l_use_callback) {
                    return true;
                }

                return $l_resultset;
            }
        }

        if (isset($p_callback)) {
            return false;
        }

        return null;
    }

    public function is_initialized()
    {
        return $this->m_initialized;
    }

    public function configure_datasource($p_tbl, $p_field_id, $p_field_node, $p_field_parentid, $p_field_const, $p_field_left, $p_field_right)
    {
        $this->m_src_tbl = $p_tbl;

        $this->m_src_field_id = $p_field_id;
        $this->m_src_field_node = $p_field_node;
        $this->m_src_field_parent_id = $p_field_parentid;
        $this->m_src_field_const = $p_field_const;
        $this->m_src_field_left = $p_field_left;
        $this->m_src_field_right = $p_field_right;

        return true;
    }

    private function action_handler_add($p_data)
    {
        /* Check some required parameters */
        if (!array_key_exists("node_id", $p_data)) {
            return "Please set node_id!";
        }
        if (!array_key_exists("parent_id", $p_data)) {
            return "Please set parent_id!";
        }

        /* Check if node is existent */
        $l_existsdao = $this->retrieve("SELECT " . "* " . "FROM " . "{$this->m_src_tbl} " . "WHERE " . "{$this->m_src_field_node}={$p_data["node_id"]};");

        /* If yes, break proc */
        if ($l_existsdao && $l_existsdao->num_rows() > 0) {
            return "Node already existent!";
        }

        /* Add node */
        $this->update("INSERT INTO " . "{$this->m_src_tbl} " . "(" . "{$this->m_src_field_node}, " . "{$this->m_src_field_parent_id}, " . "{$this->m_src_field_const}, " .
            "{$this->m_src_field_left}, " . "{$this->m_src_field_right} " . ") VALUES (" . "'{$p_data["node_id"]}', " . "'{$p_data["parent_id"]}', " .
            "'{$p_data["const"]}', " . "'0', " . "'0' " . ");");

        /* Everything was okay */

        return null;
    }

    private function action_handler_update($p_data)
    {
        if (!is_array($p_data) || !array_key_exists("node_id", $p_data)) {
            return "Please set node_id";
        }

        $l_nodeid = $p_data["node_id"];
        unset($p_data["node_id"]);

        $l_strextra = "";
        $l_ci = 0;
        $count = count($p_data);

        foreach ($p_data as $l_field => $l_data) {
            $l_strextra .= "$l_field='" . (is_numeric($l_data) ? $l_data : $this->m_db->escape_string($l_data)) . "' ";

            if ($l_ci < $count - 1) {
                $l_strextra .= ", ";
            }

            $l_ci++;
        }

        $l_q = "UPDATE " . "{$this->m_src_tbl} " . "SET " . "{$this->m_src_field_node}='" . $l_nodeid . "', " . $l_strextra . "WHERE " . "{$this->m_src_field_node}='" .
            $l_nodeid . "';";

        if (!$this->update($l_q)) {
            return "Update was not successful ($l_q)!";
        }

        return null;
    }

    private function action_handler_delete($p_data)
    {
        if (!array_key_exists("node_id", $p_data)) {
            return "Please set node_id";
        }

        /* Delete node */
        $this->update("DELETE FROM " . "{$this->m_src_tbl} " . "WHERE " . "{$this->m_src_field_node} = {$p_data["node_id"]};");

        /* Everything was okay */

        return null;
    }

    private function action_handler_move($p_data)
    {
        if (!array_key_exists("node_id", $p_data)) {
            return "Please set node_id";
        }
        if (!array_key_exists("parent_id", $p_data)) {
            return "Please set node_id";
        }

        /* Delete node */
        $this->update("UPDATE " . "{$this->m_src_tbl} " . "SET " . "{$this->m_src_field_parent_id} = {$p_data["parent_id"]} " . "WHERE " .
            "{$this->m_src_field_node} = {$p_data["node_id"]};");

        /* Everything was okay */

        return null;
    }

    private function action_stack_process()
    {
        if (is_countable($this->m_actionstack) && count($this->m_actionstack) > 0) {
            $this->begin_update();

            foreach ($this->m_actionstack as $l_action_data) {
                list($l_action, $l_data) = $l_action_data;
                $l_ret = null;

                switch ($l_action) {
                    case C__MPTT__ACTION_ADD:
                        $l_ret = $this->action_handler_add($l_data);
                        break;
                    case C__MPTT__ACTION_DELETE:
                        $l_ret = $this->action_handler_delete($l_data);
                        break;
                    case C__MPTT__ACTION_MOVE:
                        $l_ret = $this->action_handler_move($l_data);
                        break;
                    case C__MPTT__ACTION_UPDATE:
                        $l_ret = $this->action_handler_update($l_data);
                        break;
                    default:
                        throw new isys_exception_dao("MPTT: Invalid action $l_action!");
                }

                if ($l_ret != null) {
                    throw new isys_exception_dao("MPTT: Could not handle action $l_action: $l_ret");
                }
            }

            $this->apply_update();

            unset($this->m_actionstack);
            $this->m_actionstack = [];
        }
    }

    private function _write($p_callback = null, $p_node_id = null, &$p_left = null)
    {
        if ($p_node_id === null) {
            $p_node_id = defined_or_default('C__OBJ__ROOT_LOCATION');
        }
        /* Get node data */

        $l_noderes = $this->get_by_node_id($p_node_id);

        if ($l_noderes && $l_noderes->num_rows() > 0) {
            $l_noderow = $l_noderes->get_row();

            /* Before writing anything, we process the action stack,
               but from the root node, since changes can modify
               the whole tree! */
            if ($l_noderow[$this->m_src_field_node] == defined_or_default('C__OBJ__ROOT_LOCATION')) {
                $p_left = 1; /* Start from 1, please */

                /* Process respective action stack */
                $this->action_stack_process();
            }

            $l_right = $p_left + 1;

            /* Enumerate child nodes */
            $l_childres = $this->get_by_parent_id($l_noderow[$this->m_src_field_node]);

            if ($l_childres->num_rows() > 0) {
                while ($l_childrow = $l_childres->get_row()) {
                    $l_right = $this->_write($p_callback, $l_childrow[$this->m_src_field_node], $l_right);
                }
            }

            $l_dowrite = true;

            /* Call callback, if developer wants to change the vars again */
            if ($p_callback) {
                /* A callback can influence the behaviour. If the callback
                   does not return a true value, the update transaction
                   won't be commited */
                $l_dowrite = $p_callback->mptt_write($p_node_id, $l_noderow[$this->m_src_field_parent_id], $l_noderow[$this->m_src_field_const], $p_left, $l_right);
            }

            if ($l_dowrite) {
                /* Update transaction */
                {
                    $l_q = "UPDATE " . "{$this->m_src_tbl} " . "SET " . "{$this->m_src_field_left} = '$p_left', " . "{$this->m_src_field_right} = '$l_right' " . "WHERE " .
                        "{$this->m_src_field_node} = '$p_node_id';";

                    /* Update node entry */
                    $this->update($l_q);
                }
            }

            return $l_right + 1;
        }

        return $p_left;
    }

    public function __construct(isys_component_database &$p_db, $p_tbl, $p_field_id, $p_field_node, $p_field_parentid, $p_field_const, $p_field_left, $p_field_right)
    {
        parent::__construct($p_db);

        $this->m_initialized = false;

        if ($this->configure_datasource($p_tbl, $p_field_id, $p_field_node, $p_field_parentid, $p_field_const, $p_field_left, $p_field_right)) {
            $this->m_initialized = true;
            $this->m_actionstack = [];
        }
    }
}

/**
 * isys_mptt_callback is the interface you have to implement in a class,
 * which is reponsible to handle a reading / writing iteration from the
 * read / write methods of isys_component_dao_mptt.
 */
interface isys_mptt_callback
{
    /**
     * Implement this method in order to define a handler where you accept
     * incoming reading iterations. I.e. you can directly build up a JavaScript
     * tree by passing a isys_component_tree-object to $p_userdata
     *
     * @param string  $p_table
     * @param integer $p_level
     * @param integer $p_id
     * @param integer $p_node_id
     * @param integer $p_parent_id
     * @param string  $p_const
     * @param integer $p_left
     * @param integer $p_right
     * @param mixed   $p_userdata
     */
    public function mptt_read($p_table, $p_level, $p_id, $p_node_id, $p_parent_id, $p_const, $p_left, $p_right, $p_userdata);

    /**
     * Implement this method in order to define a handler where you can change
     * data to write before writing. This method has to return true in
     * its implementation in order to commit the write. Otherwise, with
     * false, nothing will be written.
     *
     * @param ref -integer $p_node_id
     * @param ref -integer $p_parent_id
     * @param ref -string $p_const
     * @param ref -integer $p_left
     * @param ref -integer $p_right
     */
    public function mptt_write(&$p_node_id, &$p_parent_id, &$p_const, &$p_left, &$p_right);
}

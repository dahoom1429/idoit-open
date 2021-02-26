<?php
/**
 * i-doit
 *
 * DAO: Contact References
 *
 * @package    i-doit
 * @subpackage Contact
 * @author     Dennis Blümer <dbluemer@i-doit.org>
 * @copyright  synetics GmbH
 * @license    http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */

define("C__CONTACT__DATA_ITEM__PERSON_INTERN", 1);
define("C__CONTACT__DATA_ITEM__PERSON_EXTERN", 2);
define("C__CONTACT__DATA_ITEM__GROUP", 3);
define("C__CONTACT__DATA_ITEM__ORGANISATION", 4);
define("C__CONTACT__DATA_ITEM__GENERIC_OBJECT", 5);

class isys_contact_dao_reference extends isys_component_dao
{
    private $m_arDataItems;

    private $m_data_item_id;

    private $m_data_item_ids;

    private $m_nID;

    public function get_last_dataitem_id()
    {
        return $this->m_data_item_id;
    }

    public function get_data_item_ids()
    {
        return $this->m_data_item_ids;
    }

    /**
     * Returns the ID of the contact reference record
     *
     * @return integer
     */
    public function get_id()
    {
        return $this->m_nID;
    }

    /**
     * Returns the array with the data items
     *
     * @return array
     */
    public function get_data_item_array()
    {
        return $this->m_arDataItems;
    }

    /**
     * Replace all existing contacts with $p_array
     *
     * @param integer $p_array Array of contact IDs
     *
     * @return boolean
     */
    public function set_data_items($p_array)
    {
        $this->flush();
        if (is_array($p_array)) {
            foreach ($p_array as $l_index => $l_objid) {
                $this->m_arDataItems[$l_objid] = true;
            }
        }

        return true;
    }

    /**
     * Inserts a contact data item, $p_type = C__CONTACT__DATA_ITEM__*
     *
     * @param integer $p_id
     *
     * @return boolean
     */
    public function insert_data_item($p_id)
    {
        if ($p_id > 0) {
            if (!array_key_exists($p_id, $this->m_arDataItems)) {
                $this->m_arDataItems[$p_id] = true;
            }
        }

        return true;
    }

    /**
     * Remove a contact data item
     *
     * @param integer $p_id
     *
     * @return boolean
     */
    public function remove_data_item($p_id)
    {
        unset($this->m_arDataItems[$p_id]);

        return true;
    }

    /**
     * Return data item dao
     *
     * @param int $p_data_item_id
     */
    public function get_data_item($p_data_item_id)
    {
        $l_query = "SELECT * FROM isys_obj " . "LEFT JOIN isys_contact_2_isys_obj ON isys_obj__id = isys_contact_2_isys_obj__isys_obj__id " . "WHERE isys_obj__id = " .
            $this->convert_sql_id($p_data_item_id);

        return $this->retrieve($l_query);
    }

    /**
     * Returns the count of data items
     *
     * @return integer
     */
    public function count()
    {
        return is_countable($this->m_arDataItems) ? count($this->m_arDataItems) : 0;
    }

    /**
     * Saves a list of contacts.
     *
     * @param   mixed   $p_contact_string JSON string, comma-separated list or array.
     * @param   integer $p_contact_id
     *
     * @return  integer  On success, the last inserted ID
     * @return  boolean  False on failure.
     * @author  Dennis Stuecken <dstuecken@i-doit.de>
     * @author  Dennis Bluemer <dbluemer@i-doit.org>
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function ref_contact($p_contacts, $p_contact_id = null)
    {
        $this->clear();

        if (empty($p_contacts)) {
            return false;
        }

        if (empty($p_contact_id)) {
            $p_contact_id = null;
        }

        // Update current contact set if valid contact id is given.
        if (is_string($p_contacts) && isys_format_json::is_json($p_contacts)) {
            // Assume we got a JSON string.
            $p_contacts = isys_format_json::decode($p_contacts, true);
        } elseif (!is_array($p_contacts)) {
            // Assume we got a comma-separated list.
            $p_contacts = explode(',', $p_contacts);
        } elseif (($p_contact_id + 0) > 0) {
            try {
                $this->load($p_contact_id);
                $this->flush();
            } catch (isys_exception_contact $e) {
                ;
            }
        }

        if (is_array($p_contacts)) {
            foreach ($p_contacts as $l_item) {
                if (is_numeric($l_item) && $l_item > 0) {
                    $this->insert_data_item($l_item);
                }
            }
        }

        if ($this->save($p_contact_id)) {
            return $this->get_id();
        } else {
            return false;
        }
    }

    public function get_contact_data($p_contact__id)
    {
        if ($p_contact__id) {
            try {
                $this->load($p_contact__id);
                $l_data_items = $this->get_data_item_array();
                $l_return = [];
                foreach ($l_data_items as $l_object_id => $l_status) {
                    if ($l_object_id > 0) {
                        $l_data = $this->get_data_item_info($l_object_id);
                        $l_row = $l_data->get_row();
                        $l_return[$l_object_id] = $l_row;
                    }
                }

                return $l_return;
            } catch (isys_exception_contact $e) {
                ;
            }
        }
    }

    /**
     * @desc return selected contacts as string
     *
     * @param int     $p_contact__id
     * @param boolean $p_as_link
     */
    public function get_selected_contacts($p_contact__id, $p_as_link = true)
    {
        $l_contact_string = "";
        $l_assigned = "";

        /**
         * @desc load contacts
         */
        if ($p_contact__id > 0) {
            $l_obj_id_str = '';

            $l_res = $this->get('isys_contact_2_isys_obj__isys_contact__id = ' . $this->convert_sql_id($p_contact__id));

            while ($l_row = $l_res->get_row()) {
                $l_obj_id_str .= $l_row["isys_contact_2_isys_obj__isys_obj__id"] . ",";
            }

            return rtrim($l_obj_id_str, ",");
        }

        return [
            0 => rtrim(trim($l_assigned), ","),
            1 => rtrim($l_contact_string, ",")
        ];
    }

    /**
     * Returns the access DAO for the specified dataitem-type
     *
     * @param integer $p_type
     *
     * @return isys_contact_dao
     */
    public function get_data_item_dao($p_type)
    {
        if (is_value_in_constants($p_type, [
            'C__OBJTYPE__PERSON', 'C__CONTACT__DATA_ITEM__PERSON_INTERN', 'C__CONTACT__DATA_ITEM__PERSON_EXTERN'
        ])) {
            $l_class = "isys_cmdb_dao_category_s_person_master";
        } elseif (is_value_in_constants($p_type, ['C__OBJTYPE__PERSON_GROUP', 'C__CONTACT__DATA_ITEM__GROUP'])) {
            $l_class = "isys_cmdb_dao_category_s_person_group_master";
        } elseif (is_value_in_constants($p_type, ['C__OBJTYPE__ORGANIZATION', 'C__CONTACT__DATA_ITEM__ORGANISATION'])) {
            $l_class = "isys_cmdb_dao_category_s_organization";
        } else {
            $l_class = "isys_cmdb_dao_category_g_global";
        }

        return new $l_class($this->m_db);
    }

    /**
     * Returns information about specified data item
     *
     * @param integer $p_id
     * @param integer $p_type
     *
     * @return isys_component_dao_result
     */
    public function get_data_item_info($p_id, $p_type = null)
    {
        if ($p_type) {
            $l_dao = $this->get_data_item_dao($p_type);

            if (is_object($l_dao) && method_exists($l_dao, "get_data")) {
                return $l_dao->get_data(null, $p_id);
            }
        } else {
            $l_data = $this->get_data_item_data($p_id);

            return $l_data["dao"];
        }

        return null;
    }

    /**
     * Return structured data item info without knowing the data item type
     *  struct (
     *    id,
     *    type,
     *    key,
     *    dao
     *  )
     *
     * @param int $p_id
     *
     * @return array
     */
    public function get_data_item_data($p_id)
    {
        $l_data = [];

        $l_data_item = $this->get_data_item($p_id)
            ->__to_array();

        $l_data["id"] = $l_data_item["isys_obj__id"];
        $l_data["data_item_id"] = $p_id;
        $l_data["key"] = "isys_contact_2_isys_obj__isys_obj__id";
        $l_data["table"] = "isys_obj";

        if ($l_data_item["isys_obj__isys_obj_type__id"] == defined_or_default('C__OBJTYPE__PERSON')) {
            $l_data["type"] = C__CONTACT__DATA_ITEM__PERSON_INTERN;
            $l_data["desc"] = "LC__CONTACT_TYPE__INTERN";
        } elseif ($l_data_item["isys_obj__isys_obj_type__id"] == defined_or_default('C__OBJTYPE__PERSON_GROUP')) {
            $l_data["type"] = C__CONTACT__DATA_ITEM__GROUP;
            $l_data["desc"] = "LC__CONTACT_TYPE__GROUP";
        } elseif ($l_data_item["isys_obj__isys_obj_type__id"] == defined_or_default('C__OBJTYPE__ORGANIZATION')) {
            $l_data["type"] = C__CONTACT__DATA_ITEM__ORGANISATION;
            $l_data["desc"] = "LC__CONTACT_TYPE__ORGANISATION";
        }

        if ($l_data_item["isys_obj__isys_obj_type__id"] > 0) {
            $l_data["dao"] = $this->get_data_item_info($l_data["id"], $l_data_item["isys_obj__isys_obj_type__id"]);
        }

        return $l_data;
    }

    /**
     * Saves a contact reference:
     * - If existing, delete all data items
     * - Create isys_contact entry
     *
     * @param integer $p_id
     *
     * @return boolean
     * @throws isys_exception_contact
     */
    public function save($p_id = null)
    {
        if (is_null($p_id)) {
            $p_id = $this->m_nID;
        }

        $this->begin_update();

        if (isset($p_id) && ($this->get($p_id)
                    ->num_rows() > 0)) {
            /* Delete old dataitems / contact references */
            $l_q = "DELETE FROM isys_contact_2_isys_obj " . "WHERE isys_contact_2_isys_obj__isys_contact__id = " . $this->convert_sql_id($p_id);

            $l_res = $this->update($l_q);
            if (!$l_res) {
                throw new isys_exception_contact("Could not delete old data items (" . __LINE__ . "/" . __FILE__ . ")", -1);
            }
        } else {
            /* Create contact reference master record */
            $l_q = "INSERT INTO isys_contact (isys_contact__title, isys_contact__description, isys_contact__property, isys_contact__status) " . "VALUES (NULL, NULL, 0, " .
                C__RECORD_STATUS__NORMAL . ")";
            if ($this->update($l_q)) {
                $p_id = $this->get_last_insert_id();
            } else {
                throw new isys_exception_contact("Unable to create primary contact entry (isys_contact)", -2);
            }
        }

        /* Iterate through data item records */
        if (is_countable($this->m_arDataItems) && count($this->m_arDataItems) > 0) {
            foreach ($this->m_arDataItems as $l_item => $l_bool) {
                $l_q = "INSERT INTO isys_contact_2_isys_obj (isys_contact_2_isys_obj__isys_contact__id, isys_contact_2_isys_obj__isys_obj__id) " . "VALUES " . "(" .
                    $this->convert_sql_id($p_id) . ", " . $this->convert_sql_id($l_item) . ")";

                if (!$this->update($l_q)) {
                    throw new isys_exception_contact("Unable to create link between contact-master- -> $p_id and -> $l_item", -3);
                } else {
                    $this->m_data_item_id = $this->get_last_insert_id();
                    $this->m_data_item_ids[$this->m_data_item_id] = $l_item;
                }
            }
        }
        if ($this->apply_update()) {
            $this->m_nID = $p_id;

            return true;
        } else {
            return false;
        }
    }

    /**
     * Deletes a contact reference and its data items
     *
     * @param integer $p_id
     *
     * @return boolean
     */
    public function delete($p_id = null)
    {
        if (is_null($p_id)) {
            $p_id = $this->m_nID;
        }

        if ($p_id) {
            $this->begin_update();

            /* Delete old dataitems / contact references and main record */
            $l_q = "DELETE FROM isys_contact WHERE isys_contact__id = " . $this->convert_sql_id($p_id);
            $l_res = $this->update($l_q);

            if ($l_res) {
                return $this->apply_update();
            }
        }

        return false;
    }

    /**
     * Clears all read data items.
     */
    public function clear()
    {
        $this->flush();

        $this->m_data_item_id = 0;
        $this->m_nID = null;
    }

    /**
     * Flushes the data_items while still holding the contact id.
     */
    public function flush()
    {
        $this->m_arDataItems = [];
    }

    /**
     * Generic query for contacts and their data items - accepting a condition for exact result determination.
     *
     * @param   string $p_cond
     *
     * @return  isys_component_dao_result
     */
    public function get($p_cond = null)
    {
        $l_q = 'SELECT * FROM isys_contact LEFT JOIN isys_contact_2_isys_obj ON isys_contact__id = isys_contact_2_isys_obj__isys_contact__id ';

        if (!empty($p_cond)) {
            $l_q .= 'WHERE ' . $p_cond;
        }

        return $this->retrieve($l_q);
    }

    /**
     * Method which allows to ask for a specific contact.
     *
     * @param   integer $p_id
     *
     * @return  mixed  isys_component_dao_result on success, null on failure.
     */
    public function get_by_contact_id($p_id)
    {
        if (is_numeric($p_id)) {
            return $this->get('isys_contact__id = ' . $this->convert_sql_id($p_id));
        }

        return null;
    }

    /**
     * Load an existing contact (from isys_contact) and all binded data items.
     *
     * @param  integer $p_id
     *
     * @return  boolean
     * @throws  isys_exception_contact
     */
    public function load($p_id)
    {
        if ($p_id > 0) {
            $this->clear();

            $l_cRes = $this->get_by_contact_id($p_id);

            if ($l_cRes) {
                while ($l_cRow = $l_cRes->get_row()) {
                    if (!$this->insert_data_item($l_cRow["isys_contact_2_isys_obj__isys_obj__id"])) {
                        throw new isys_exception_contact("While loading: Could not insert data item", -2);
                    }
                }

                $this->m_nID = $p_id;

                return true;
            } else {
                throw new isys_exception_contact("Could not find contact ($p_id)", -1);
            }
        }
    }

    /**
     * Magic to-string method.
     *
     * @return  string
     */
    public function __toString()
    {
        return "";
    }

    /**
     * Standard DAO constructor.
     *
     * @param  isys_component_database & $p_db
     */
    public function __construct(isys_component_database & $p_db)
    {
        parent::__construct($p_db);
        $this->m_arDataItems = [];
    }
}

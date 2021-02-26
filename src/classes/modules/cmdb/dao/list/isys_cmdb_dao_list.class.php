<?php

/**
 * Class isys_cmdb_dao_list
 */
abstract class isys_cmdb_dao_list extends isys_component_dao_object_table_list
{
    /**
     * Database component.
     *
     * @var  isys_component_database
     */
    protected $m_db;

    /**
     * Flag for the rec status dialog
     *
     * @var bool
     */
    protected $m_rec_status_list_active = true;

    /**
     * Gets category type.
     *
     * @return  integer
     */
    public function get_category_type()
    {
        return $this->m_cat_dao->get_category_type();
    }

    /**
     * Gets category identifier.
     *
     * @return  integer
     */
    public function get_category()
    {
        return $this->m_cat_dao->get_category_id();
    }

    /**
     * Method for retrieving the CMDB status.
     *
     * @return  array
     */
    public function get_cmdb_status()
    {
        return $this->m_cat_dao->get_cmdb_status();
    }

    /**
     * Method for setting the CMDB status.
     *
     * @param   array $p_cmdb_status
     *
     * @return  isys_cmdb_dao_list
     */
    public function set_cmdb_status($p_cmdb_status)
    {
        $this->m_cat_dao->set_cmdb_status($p_cmdb_status);

        return $this;
    }

    /**
     * Sets the CMDB status prefix.
     *
     * @param   string $p_prefix
     *
     * @return  isys_cmdb_dao_list
     */
    public function set_cmdb_status_prefix($p_prefix)
    {
        $this->m_cat_dao->set_cmdb_status_prefix($p_prefix);

        return $this;
    }

    /**
     * Retrieves the CMDB status prefix.
     *
     * @return  string
     */
    public function get_cmdb_status_prefix()
    {
        return $this->m_cat_dao->get_cmdb_status_prefix();
    }

    /**
     * Order conditioner.
     *
     * @param   string $p_column
     * @param   string $p_direction
     *
     * @return  string
     */
    public function get_order_condition($p_column, $p_direction)
    {
        return $this->m_db->escapeColumnName($p_column) . " " . $p_direction;
    }

    /**
     * Sets flag for the rec status dialog
     *
     * @return bool
     * @author Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function set_rec_status_list($p_value)
    {
        $this->m_rec_status_list_active = $p_value;
    }

    /**
     * Gets flag for the rec status dialog
     *
     * @return bool
     * @author Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function rec_status_list_active()
    {
        return $this->m_rec_status_list_active;
    }

    /**
     * Method for preparing the status filter.
     *
     * @return  string
     */
    protected function prepare_status_filter()
    {
        if (is_array($this->get_cmdb_status())) {
            $l_filter = $this->m_cat_dao->prepare_status_filter();

            if ($l_filter) {
                return " AND (" . $l_filter . ")";
            }
        }

        return "";
    }

    /**
     * Constructor
     *
     * @param  isys_component_database $p_db
     */
    public function __construct($p_db)
    {
        $this->m_db = $p_db;
        parent::__construct($p_db);
    }

}

/**
 * i-doit
 *
 * DAO: CMDB List
 *
 * @package     i-doit
 * @subpackage  CMDB_Lists
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
interface isys_cmdb_dao_list_interface
{
    /**
     * Every list class must have this method to return its category.
     *
     * @author  Niclas Potthast <npotthast@i-doit.org>
     * @return  integer
     */
    public function get_category();

    /**
     * Every list class must have this method to return its category type.
     *
     * @return  integer
     *
     */
    public function get_category_type();

    /**
     * Flag for the rec status dialog
     *
     * @return bool
     * @author Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function rec_status_list_active();
}
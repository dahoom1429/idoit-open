<?php

/**
 * @package     i-doit
 * @subpackage  Export CMDB
 * @author      Van Quyen Hoang <qhoang@i-doit.org>
 * @version     1.0
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
abstract class isys_export_csv
{
    /**
     * @var array
     */
    protected $m_data = [];

    /**
     * @var array
     */
    protected $m_header = [];

    /**
     * @var string
     */
    protected $m_title = "";

    /**
     * Database component.
     *
     * @var  isys_component_database
     */
    private $m_database;

    /**
     * Should export as ARRAY.
     *
     * @param   mixed $p_object_ids
     *
     * @return  array
     */
    abstract public function export($p_object_ids);

    /**
     * Gets header mapping.
     *
     * @return  array
     */
    public function get_header()
    {
        return $this->m_header;
    }

    /**
     * Get database component.
     *
     * @return  isys_component_database
     */
    public function get_database()
    {
        return $this->m_database;
    }

    /**
     * Get Title of CSV import.
     *
     * @return  string
     */
    public function get_title()
    {
        return isys_application::instance()->container->get('language')
            ->get($this->m_title);
    }

    /**
     * Gets connected contact.
     *
     * @param int $p_id
     *
     * @return mixed
     * @throws Exception
     */
    public function get_contact($p_id)
    {
        if ($p_id > 0) {
            $dao = isys_application::instance()->container->get('cmdb_dao');

            $sql = 'SELECT isys_obj__title
                FROM isys_contact_2_isys_obj
                INNER JOIN isys_obj ON isys_obj__id = isys_contact_2_isys_obj__isys_obj__id
                WHERE isys_contact_2_isys_obj__isys_contact__id = ' . $dao->convert_sql_id($p_id) . ';';

            return $dao->retrieve($sql)->get_row_value('isys_obj__title');
        }
    }

    /**
     * Gets title from table.
     *
     * @param   integer $p_id
     * @param   string  $p_table
     *
     * @return  string
     */
    public function get_unit($p_id, $p_table = null)
    {
        if ($p_id > 0) {
            $dao = isys_cmdb_dao::factory($this->get_database());

            return $dao
                ->retrieve("SELECT {$p_table}__title FROM {$p_table} WHERE {$p_table}__id = " . $dao->convert_sql_id($p_id) . ";")
                ->get_row_value($p_table . '__title');
        }
    }

    /**
     * Formats date.
     *
     * @param   string $p_date
     * @param   string $p_format
     *
     * @return  string
     */
    public function format_date($p_date, $p_format = "d.m.y")
    {
        return date($p_format, strtotime($p_date));
    }

    /**
     * Gets object title by connection id.
     *
     * @param   integer $p_id
     *
     * @return  string
     */
    public function connection($p_id)
    {
        if ($p_id > 0) {
            $dao = isys_application::instance()->container->get('cmdb_dao');

            $sql = 'SELECT isys_obj__title
                FROM isys_obj
                INNER JOIN isys_connection ON isys_connection__isys_obj__id = isys_obj__id
                WHERE isys_connection__id = ' . $dao->convert_sql_id($p_id) . ';';

            return $dao->retrieve($sql)->get_row_value('isys_obj__title');
        }
    }

    /**
     * Sets header mapping.
     *
     * @param  array $p_header
     */
    protected function set_header($p_header)
    {
        $this->m_header = $p_header;
    }

    /**
     * Constructor.
     *
     * @param   isys_component_database $p_database
     *
     * @throws  isys_exception_general
     */
    public function __construct(isys_component_database &$p_database = null)
    {
        // Store database component.
        if (is_null($p_database) && !is_object($p_database)) {
            global $g_comp_database;
            $l_database = $g_comp_database;
        } else {
            $l_database = $p_database;
        }

        $this->m_database = $l_database;

        // Check if database component is fine.
        if (is_null($this->m_database)) {
            global $g_comp_session;

            if (!$g_comp_session->is_logged_in()) {
                throw new isys_exception_general("Your session is expired. You need to re-login!");
            } else {
                throw new isys_exception_general("Database component is NULL. Can't work with it! \n");
            }
        }
    }
}

<?php

/**
 * i-doit
 *
 * DAO for category table list template.
 *
 * @package    i-doit
 * @subpackage Components
 * @author     Leonard Fischer <lfischer@i-doit.com>
 * @version    1.0
 * @copyright  synetics GmbH
 * @license    http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_component_dao_category_table_list extends isys_cmdb_dao_list
{
    /**
     * Static build method which will automatically deal with the database component and the category DAO.
     *
     * @param isys_component_database $p_database
     * @param isys_cmdb_dao_category  $p_cat
     *
     * @return static
     */
    public static function build(isys_component_database $p_database, isys_cmdb_dao_category $p_cat)
    {
        $instance = new static($p_database);
        $instance->set_dao_category($p_cat);

        return $instance;
    }

    /**
     * Get counts of entries in several status
     *
     * @return array Counts of several status
     * @throws isys_exception_dao_cmdb
     */
    public function get_rec_counts()
    {
        if ($this->m_rec_counts) {
            return $this->m_rec_counts;
        } else {
            /**
             * Check whether objectId is set - otherwise prevent querying all data
             *
             * @see ID-6355
             */
            if (isset($_GET[C__CMDB__GET__OBJECT])) {
                $l_normal = $this->get_result(null, $_GET[C__CMDB__GET__OBJECT], C__RECORD_STATUS__NORMAL);
                $l_archived = $this->get_result(null, $_GET[C__CMDB__GET__OBJECT], C__RECORD_STATUS__ARCHIVED);
                $l_deleted = $this->get_result(null, $_GET[C__CMDB__GET__OBJECT], C__RECORD_STATUS__DELETED);
            }

            $this->m_rec_counts = [
                C__RECORD_STATUS__NORMAL   => ($l_normal) ? $l_normal->num_rows() : 0,
                C__RECORD_STATUS__ARCHIVED => ($l_archived) ? $l_archived->num_rows() : 0,
                C__RECORD_STATUS__DELETED  => ($l_deleted) ? $l_deleted->num_rows() : 0,
            ];

            if (defined("C__TEMPLATE__STATUS") && C__TEMPLATE__STATUS == 1) {
                $l_template = $this->get_result(null, $_GET[C__CMDB__GET__OBJECT], C__RECORD_STATUS__TEMPLATE);
                $this->m_rec_counts[C__RECORD_STATUS__TEMPLATE] = ($l_template) ? $l_template->num_rows() : 0;
            }

            return $this->m_rec_counts;
        }
    }
}

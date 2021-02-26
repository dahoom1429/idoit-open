<?php
/**
 * i-doit
 *
 * Multiedit DAO.
 *
 * @package     modules
 * @subpackage  multiedit
 * @author      Van Quyen Hoang <lfischer@i-doit.com>
 * @version     1.12
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_multiedit_dao extends isys_cmdb_dao
{
    /**
     * Constructor.
     *
     * @param  isys_component_database $p_db
     * @param  integer $p_cmdb_status
     */
    public function __construct(isys_component_database $p_db, $p_cmdb_status = NULL)
    {
        parent::__construct($p_db, $p_cmdb_status);
    } // function
} // class
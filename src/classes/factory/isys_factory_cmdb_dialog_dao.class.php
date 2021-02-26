<?php

/**
 * i-doit
 *
 * Factory for CMDB dialogs.
 *
 * @deprecated  Please use the DAO instance method.
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 * @since       1.0
 */
class isys_factory_cmdb_dialog_dao extends isys_factory
{
    /**
     * Gets an instance of a category DAO.
     *
     * @param   isys_component_database|string $dialogTable
     * @param   isys_component_database|string $database
     *
     * @deprecated
     * @return  isys_cmdb_dao_dialog
     */
    public static function get_instance($dialogTable, $database = null)
    {
        // This is a "fix" for using the wrong parameter order.
        if (is_string($database) && is_object($dialogTable)) {
            $l_tmp = $dialogTable;
            $dialogTable = $database;
            $database = $l_tmp;
        }

        /** @var string $dialogTable */
        if (isset(self::$m_instances[$dialogTable])) {
            return self::$m_instances[$dialogTable];
        }

        return self::$m_instances[$dialogTable] = new isys_cmdb_dao_dialog($database, $dialogTable);
    }
}

<?php

/**
 * i-doit
 *
 * Import
 *
 * @package     i-doit
 * @subpackage  Import
 * @author      Dennis Stücken <dstuecken@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */

// Set time limit to 12 hours.
set_time_limit(60 * 60 * 24);

// Head.
define('C__HEAD', 'head');

/**
 * Class isys_import
 */
abstract class isys_import
{
    const c__insert  = 1;
    const c__update  = 2;
    const c__replace = 3;

    private static $m_changed = false;

    /**
     * Start time of the import
     *
     * @var mixed|null
     */
    protected $m_import_start_time = null;

    /**
     * set $m_changed to false
     */
    public static function change_reset()
    {
        self::$m_changed = false;
    }

    /**
     * @return bool
     */
    public static function changed()
    {
        return self::$m_changed;
    }

    /**
     * Checks a dialog entry for its existence and creates a new one or returns
     * the identifier of the existing one.
     *
     * @param   string  $p_table
     * @param   string  $p_title
     * @param   string  $p_check_name
     * @param   integer $p_parent_id
     *
     * @deprecated Use isys_cmdb_dao_dialog::check_dialog
     * @todo       remove this function!
     *
     * @return  integer  Returns null if no data could be found.
     */
    public static function check_dialog($p_table, $p_title, $p_check_name = null, $p_parent_id = null)
    {
        return isys_cmdb_dao_dialog::instance(isys_application::instance()->database)
            ->set_table($p_table)
            ->load()
            ->check_dialog($p_table, $p_title, $p_check_name, $p_parent_id);
    }

    /**
     * Synchronizes a single field.
     *
     * @param   string $p_old_value
     * @param   string $p_new_value
     * @param   string $p_fieldname    Used for logging
     * @param   string $p_type         Filed type. Can be 'value' or 'dialog'. Defaults to 'value'.
     * @param   string $p_dialog_table The name of the (optional) dialog-table.
     *
     * @return  string  Old or new value otherwise null
     */
    public static function field_sync($p_old_value, $p_new_value, $p_fieldname, $p_type = 'value', $p_dialog_table = null)
    {
        if ($p_old_value == $p_new_value || is_null($p_new_value)) {
            return $p_old_value;
        } else {
            isys_import_handler_cmdb::set_change(true);
            isys_import::synclog($p_old_value, $p_new_value, $p_fieldname);

            switch ($p_type) {
                case 'dialog':
                case 'dialog_plus':
                    if ($p_new_value) {
                        return isys_import::check_dialog($p_dialog_table, (string)$p_new_value);
                    } else {
                        return null;
                    }

                    break;

                default:
                case 'value':

                    return $p_new_value;

                    break;
            }
        }
    }

    /**
     * Logs a syncronization on data change.
     *
     * @param   string $p_oldval
     * @param   string $p_newval
     * @param   string $l_tag
     *
     * @return  boolean
     */
    public static function synclog($p_oldval, $p_newval, $l_tag)
    {
        if ($p_oldval != $p_newval) {
            if ($p_oldval == '') {
                isys_import_log::add('|- ADD: ' . $l_tag . ' | NV: ' . $p_newval);
            } else {
                isys_import_log::add('|- MERGE: ' . $l_tag . ' | CV: ' . $p_oldval . ' | NV: ' . $p_newval);
            }

            return true;
        } else {
            return false;
        }
    }

    /**
     * Method for parsing the import data
     *
     * @param   string $p_data
     *
     * @return  boolean
     */
    public function parse($p_data = null)
    {
        return true;
    }

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->m_import_start_time = microtime(true);
        // Nothing to do here.
    }
}
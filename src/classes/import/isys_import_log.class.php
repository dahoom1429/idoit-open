<?php

/**
 * i-doit
 *
 * Handler for import logs
 *
 * @package     i-doit
 * @subpackage  Import
 * @author      Dennis Stuecken <dstuecken@i-doitorg>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 *
 * @deprecated  Use isys_factory_log instead.
 */
class isys_import_log
{
    /**
     * Autosave.
     *
     * @var  boolean
     */
    protected static $m_autosave = false;

    /**
     * Alarmlevel.
     *
     * @var  integer
     */
    private static $m_alarmlevel = 1;

    /**
     * Log.
     *
     * @var  array
     */
    private static $m_log = [];

    /**
     * Returns raw log.
     *
     * @return  array
     */
    public static function get_raw()
    {
        return self::$m_log;
    }

    /**
     * Returns import log new line separated.
     *
     * @return  string
     */
    public static function get()
    {
        return implode(PHP_EOL, self::get_raw());
    }

    /**
     * Adds new message to log.
     *
     * @param  string $p_message
     */
    public static function add($p_message)
    {
        self::$m_log[] = date('Y-m-d H:i:s - ') . $p_message;
    }

    /**
     * Change Alarmlevel.
     *
     * @param  integer $p_val
     */
    public static function change_alarmlevel($p_val)
    {
        self::$m_alarmlevel = $p_val;
    }

    /**
     * Gets alarmlevel.
     *
     * @return  integer
     */
    public static function get_alarmlevel()
    {
        return self::$m_alarmlevel;
    }

    /**
     * Saves log to file.
     *
     * @global  array $g_absdir
     */
    public function save()
    {
        global $g_absdir;

        isys_file_put_contents(isys_glob_get_temp_dir() . 'import_log_' . date('ymd_his') . '.txt', self::get());
    }
}

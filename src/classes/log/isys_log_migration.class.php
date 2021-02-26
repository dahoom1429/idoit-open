<?php

/**
 * i-doit
 *
 * Migration logger.
 *
 * @deprecated  Please use \idoit\Component\Logger
 * @package     i-doit
 * @subpackage  Log
 * @author      Van Quyen Hoang <qhoang@synetics.de>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_log_migration extends isys_log
{
    /**
     * Contains a self representation of this class's childs.
     *
     * @var  isys_log_migration
     */
    protected static $m_instance = null;

    /**
     * Containing the migration log messages.
     *
     * @var  array
     */
    private $m_migration_log = [];

    /**
     * Method for returning all migration logs.
     *
     * @return array
     */
    public function get_migration_log()
    {
        return $this->m_migration_log;
    }

    /**
     * @param   string $p_type
     * @param   string $p_message
     *
     * @return  isys_log_migration
     * @throws  isys_exception_general
     */
    public function add_migration_log($p_type, $p_message)
    {
        try {
            if (method_exists($this, $p_type)) {
                $this->$p_type($p_message);
                $this->set_migration_log($p_message, $p_type);
            }
        } catch (Exception $e) {
            throw new isys_exception_general($e->getMessage(), '');
        }

        return $this;
    }

    /**
     * Restes the log
     */
    public function reset_log()
    {
        $this->flush_log();
        $this->m_migration_log = [];

        return $this;
    }

    /**
     * Destructor. Flushs log and writes messages to file.
     */
    public function __destruct()
    {
        $this->flush_log();
    }

    /**
     * Gets the existing instance of this class. If there is no instance already created, a new one will be instantiated.
     *
     * @static
     * @return  isys_log_migration
     */
    public static function get_instance($p_instanceName = 'migration')
    {
        if (isset(self::$m_instance)) {
            return self::$m_instance;
        }

        return self::new_instance();
    }

    /**
     * Creates a new instance of this class and returns a representation of it. This function can only be called once.
     *
     * @static
     * @return  isys_log_migration
     */
    public static function new_instance($p_instance = 'migration')
    {
        assert(!isset(self::$m_instance));
        self::$m_instance = new self();

        return self::$m_instance;
    }

    /**
     * Set a new migration log message.
     *
     * @param   string $p_message
     * @param   string $p_type
     *
     * @return  isys_log_migration
     */
    private function set_migration_log($p_message, $p_type)
    {
        $this->m_migration_log[] = '<span class="text-bold ' .
            (($p_type == 'error') ? 'text-red' : (($p_type == 'warning' || $p_type == 'notice') ? 'text-black' : 'text-grey')) . ' indent">' . $p_message . '</span>';

        return $this;
    }
}

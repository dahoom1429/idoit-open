<?php

/**
 * i-doit
 *
 * Null Logger (nothing happens at all)
 *
 * @package     i-doit
 * @subpackage  Log
 * @author      Dennis Stücken <dstuecken@synetics.de>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_log_null
{

    /**
     * Log nothing.
     */
    const C__NONE = 0;

    /**
     * Log fatal errors.
     */
    const C__FATAL = 1;

    /**
     * Log errors.
     */
    const C__ERROR = 2;

    /**
     * Log warnings.
     */
    const C__WARNING = 4;

    /**
     * Log notices.
     */
    const C__NOTICE = 8;

    /**
     * Log information.
     */
    const C__INFO = 16;

    /**
     * Log debug messages.
     */
    const C__DEBUG = 32;

    /**
     * Log everything.
     */
    const C__ALL = 63;

    /**
     * Contains self representations of this class's instances.
     *
     * @var  array  Associative array of instances
     */
    protected static $m_instances = [];

    /**
     * Flushs automatically log and verbosity after each event.
     *
     * @var boolean Defaults to false.
     */
    protected $m_auto_flush = false;

    /**
     * Flushs the log on "destruct".
     *
     * @var  boolean  Defaults to true.
     */
    protected $m_destruct_flush = true;

    /**
     * Log footer.
     *
     * @var  string
     */
    protected $m_footer;

    /**
     * Log header.
     *
     * @var  string
     */
    protected $m_header;

    /**
     * Headers already sent? Useful to avoid adding headers by flushing.
     *
     * @var  boolean  Defaults to false.
     */
    protected $m_headers_sent = false;

    /**
     * Path to log file.
     *
     * @var  string  Path
     */
    protected $m_log_file;

    /**
     * Current log level.
     *
     * @var  integer
     */
    protected $m_log_level;

    /**
     * Log messages.
     *
     * @var  array
     */
    protected $m_logs = [];

    /**
     * Current verbose level.
     *
     * @var  integer
     */
    protected $m_verbose_level;

    /**
     * Verbosity messages.
     *
     * @var  array
     */
    protected $m_verbosities;

    /**
     * Gets current instances.
     *
     * @return array Array of strings or null if no instance is present.
     */
    public static function get_instances()
    {
        return array_keys(self::$m_instances);
    }

    /**
     * Gets the existing instance of this class. If there is no instance already created, a new one will be instantiated.
     *
     * @param   string $p_instance (optional) Set topic. Defaults to 'i-doit'.
     *
     * @return  isys_log
     */
    public static function get_instance($p_instance = 'i-doit')
    {
        if (array_key_exists($p_instance, self::$m_instances)) {
            return self::$m_instances[$p_instance];
        }

        return self::new_instance($p_instance);
    }

    /**
     * Creates a new instance of this class and returns a representation of it. This function can only be called once.
     *
     * @param   string $p_instance (optional) Set topic. Defaults to 'i-doit'.
     *
     * @return  isys_log
     */
    public static function new_instance($p_instance = 'i-doit')
    {
        self::$m_instances[$p_instance] = new self();

        return self::$m_instances[$p_instance];
    }

    /**
     * Method for retrieving a fitting icon for every log level.
     *
     * @static
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public static function get_log_icons()
    {
        global $g_dirs;

        return [
            self::C__DEBUG   => $g_dirs["images"] . 'icons/silk/bug.png',
            self::C__INFO    => $g_dirs["images"] . 'icons/silk/information.png',
            self::C__NOTICE  => $g_dirs["images"] . 'icons/silk/lightbulb.png',
            self::C__WARNING => $g_dirs["images"] . 'icons/silk/error.png',
            self::C__ERROR   => $g_dirs["images"] . 'icons/error_icon.png',
            self::C__FATAL   => $g_dirs["images"] . 'icons/silk/cross.png'
        ];
    }

    /**
     * Logs an event.
     *
     * @param int    $p_level   Log level
     * @param string $p_message Log message
     * @param string $p_file    (optional) File name. Defaults to null.
     * @param int    $p_line    (optional) Line in file. Defaults to null.
     * @param mixed  $p_details (optional) Log details. Defaults to null.
     *
     * @return array Log event
     */
    public function log($p_level, $p_message, $p_file = null, $p_line = null, $p_details = null)
    {
        return [];
    }

    /**
     * Logs a fatal error.
     *
     * @param string $p_message Log message
     * @param string $p_file    (optional) File name. Defaults to null.
     * @param int    $p_line    (optional) Line in file. Defaults to null.
     * @param mixed  $p_details (optional) Log details. Defaults to null.
     *
     * @return array Log event
     */
    public function fatal($p_message, $p_file = null, $p_line = null, $p_details = null)
    {
        return [];
    }

    /**
     * Logs an error.
     *
     * @param string $p_message Log message
     * @param string $p_file    (optional) File name. Defaults to null.
     * @param int    $p_line    (optional) Line in file. Defaults to null.
     * @param mixed  $p_details (optional) Log details. Defaults to null.
     *
     * @return array Log event
     */
    public function error($p_message, $p_file = null, $p_line = null, $p_details = null)
    {
        return [];
    }

    /**
     * Logs a warning.
     *
     * @param   string  $p_message Log message
     * @param   string  $p_file    (optional) File name. Defaults to null.
     * @param   integer $p_line    (optional) Line in file. Defaults to null.
     * @param   mixed   $p_details (optional) Log details. Defaults to null.
     *
     * @return  array  Log event
     */
    public function warning($p_message, $p_file = null, $p_line = null, $p_details = null)
    {
        return [];
    }

    /**
     * Logs a notice.
     *
     * @param   string  $p_message Log message
     * @param   string  $p_file    (optional) File name. Defaults to null.
     * @param   integer $p_line    (optional) Line in file. Defaults to null.
     * @param   mixed   $p_details (optional) Log details. Defaults to null.
     *
     * @return  array  Log event
     */
    public function notice($p_message, $p_file = null, $p_line = null, $p_details = null)
    {
        return [];
    }

    /**
     * Logs an info message.
     *
     * @param   string  $p_message Log message
     * @param   string  $p_file    (optional) File name. Defaults to null.
     * @param   integer $p_line    (optional) Line in file. Defaults to null.
     * @param   mixed   $p_details (optional) Log details. Defaults to null.
     *
     * @return  array  Log event
     */
    public function info($p_message, $p_file = null, $p_line = null, $p_details = null)
    {
        return [];
    }

    /**
     * Logs a debug message.
     *
     * @param   string  $p_message Log message
     * @param   string  $p_file    (optional) File name. Defaults to null.
     * @param   integer $p_line    (optional) Line in file. Defaults to null.
     * @param   mixed   $p_details (optional) Log details. Defaults to null.
     *
     * @return  array  Log event
     */
    public function debug($p_message, $p_file = null, $p_line = null, $p_details = null)
    {
        return [];
    }

    /**
     * Writes log messages to file and resets log.
     *
     * @param   boolean $p_write      Write to file, otherwise return log messages as string. Defaults to true.
     * @param   boolean $p_standalone Sent header and footer if available. Defaults to true.
     *
     * @throws  isys_exception_general
     * @return  string  Log messages with optional header and footer.
     */
    public function flush_log($p_write = true, $p_standalone = true)
    {
        return '';
    }

    /**
     * Writes verbose messages to standard input and resets messages.
     *
     * @param bool $p_print      (optional) Write to standard error, otherwise return
     *                           verbose messages as string. Defaults to true.
     * @param bool $p_standalone (optional) Sent header and footer if available.
     *                           Defaults to true.
     *
     * @return string Verbose messages with optional header and footer.
     */
    public function flush_verbosity($p_print = true, $p_standalone = true)
    {
        return '';
    }

    /**
     * Gets all log messages.
     *
     * @param   boolean $p_sort_by_date (optional) Sort log by date. Defaults to false.
     * @param   integer $p_level        (optional) Consider only this level. Defaults to null.
     *
     * @return  array  Returns null if no message has been logged yet.
     */
    public function get_log($p_sort_by_date = false, $p_level = null)
    {
        return [];
    }

    /**
     * Gets all verbosity messages.
     *
     * @param   boolean $p_sort_by_date (optional) Sort log by date. Defaults to false.
     * @param   integer $p_level        (optional) Consider only this level. Defaults to null.
     *
     * @return  array  Returns null if no message has been logged yet.
     */
    public function get_verbosity($p_sort_by_date = false, $p_level = null)
    {
        return [];
    }

    /**
     * Gets current log level.
     *
     * @return  integer  Returns null, if no log level is set.
     */
    public function get_log_level()
    {
        return $this->m_log_level;
    }

    /**
     * Sets current log level.
     *
     * @param   integer $p_log_level Any combination of log levels
     *
     * @return  isys_log
     */
    public function set_log_level($p_log_level)
    {
        $this->m_log_level = $p_log_level;

        return $this;
    }

    /**
     * Gets current verbose level.
     *
     * @return  integer  Returns null, if no log level is set.
     */
    public function get_verbose_level()
    {
        return $this->m_verbose_level;
    }

    /**
     * Sets current verbose level.
     *
     * @param   integer $p_verbose_level Any combination of log levels
     *
     * @return  isys_log
     */
    public function set_verbose_level($p_verbose_level)
    {
        $this->m_verbose_level = $p_verbose_level;

        return $this;
    }

    /**
     * Gets log file.
     *
     * @return string Path to file
     */
    public function get_log_file()
    {
        return $this->m_log_file;
    }

    /**
     * Sets log file.
     *
     * @param  string $p_file Path to file
     *
     * @return  isys_log
     */
    public function set_log_file($p_file)
    {
        $this->m_log_file = $p_file;

        return $this;
    }

    /**
     * Gets log header.
     *
     * @return  string  Returns null if header has not been set yet.
     */
    public function get_header()
    {
        return $this->m_header;
    }

    /**
     * Sets log header.
     *
     * @param   string $p_header Header
     *
     * @return  isys_log
     */
    public function set_header($p_header)
    {
        $this->m_header = $p_header;

        return $this;
    }

    /**
     * Gets log footer.
     *
     * @return  string  Returns null if footer has not been set yet.
     */
    public function get_footer()
    {
        return $this->m_footer;
    }

    /**
     * Sets log footer.
     *
     * @param   string $p_footer Footer
     *
     * @return  isys_log
     */
    public function set_footer($p_footer)
    {
        $this->m_footer = $p_footer;

        return $this;
    }

    /**
     * Gets status about auto flush.
     *
     * @return  boolean
     */
    public function get_auto_flush()
    {
        return $this->m_auto_flush;
    }

    /**
     * Sets status about auto flush.
     *
     * @param   boolean $p_mode
     *
     * @return  isys_log  Returns itself.
     */
    public function set_auto_flush($p_mode)
    {
        $this->m_auto_flush = $p_mode;

        return $this;
    }

    /**
     * Gets status about destruct flush.
     *
     * @return  boolean
     */
    public function get_destruct_flush()
    {
        return $this->m_destruct_flush;
    }

    /**
     * Sets status about destruct flush.
     *
     * @param   boolean $p_mode
     *
     * @return  isys_log  Returns itself.
     */
    public function set_destruct_flush($p_mode)
    {
        $this->m_destruct_flush = !!$p_mode;

        return $this;
    }

    /**
     * Logs an event.
     *
     * @param int    $p_level   Log level
     * @param string $p_message Log message
     * @param string $p_file    (optional) File name. Defaults to null.
     * @param int    $p_line    (optional) Line in file. Defaults to null.
     * @param mixed  $p_details (optional) Log details. Defaults to null.
     *
     * @return array Log event
     */
    protected function event($p_level, $p_message, $p_file = null, $p_line = null, $p_details = null)
    {
        return [];
    }

    /**
     * Gets log level as string.
     *
     * @param int $p_level Log level
     *
     * @return string
     */
    protected function level_as_string($p_level)
    {

    }

    /**
     * Gets all event messages.
     *
     * @param   string  $p_type         Event type ('logs', 'verbosities')
     * @param   boolean $p_sort_by_date (optional) Sort log by date. Defaults to false.
     * @param   integer $p_level        (optional) Consider only this level. Defaults to null.
     *
     * @throws  isys_exception_general
     * @return  array  Returns null if no message has been logged yet.
     */
    protected function get_events($p_type, $p_sort_by_date = false, $p_level = null)
    {
        return [];
    }

    /**
     * Sorts events by date.
     *
     * @param   string  $p_type  Event type ('logs', 'verbosities')
     * @param   integer $p_level (optional) Consider only this level. Defaults to null.
     *
     * @throws  isys_exception_general
     * @return  array  Returns null if no events has been logged yet.
     */
    protected function sort_events_by_date($p_type, $p_level = null)
    {
        return [];
    }

    /**
     * Prohibits cloning.
     */
    private final function __clone()
    {
        ;
    }

    /**
     * Constructor
     */
    protected function __construct()
    {
        $this->m_log_level = self::C__NONE;
        $this->m_verbose_level = self::C__NONE;
    }
}
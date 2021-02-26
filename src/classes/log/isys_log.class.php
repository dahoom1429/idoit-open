<?php

/**
 * i-doit
 *
 * Logger
 *
 * @deprecated  Please use \idoit\Component\Logger
 * @package     i-doit
 * @subpackage  Log
 * @author      Benjamin Heisig <bheisig@synetics.de>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_log
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
        global $g_absdir;
        self::$m_instances[$p_instance] = new self();

        self::$m_instances[$p_instance]->set_log_level(isys_log::C__ALL);
        self::$m_instances[$p_instance]->set_log_file($g_absdir . '/log/' . date('Y-m-d_H_i_s') . '-' . $p_instance . '.log');

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
     * Method for retrieving a fitting text-color (via CSS class) for every log level.
     *
     * @static
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public static function get_log_colors()
    {
        return [
            self::C__DEBUG   => 'green',
            self::C__INFO    => 'blue',
            self::C__NOTICE  => '',
            self::C__WARNING => 'yellow',
            self::C__ERROR   => 'red',
            self::C__FATAL   => 'red'
        ];
    }

    /**
     * Gets log level as string.
     *
     * @param   integer $p_level
     *
     * @return  string
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public static function get_log_level_names($p_level = null)
    {
        $l_levels = [
            self::C__DEBUG   => isys_application::instance()->container->get('language')
                ->get('LC_UNIVERSAL__LOG_LEVEL__DEBUG'),
            self::C__INFO    => isys_application::instance()->container->get('language')
                ->get('LC_UNIVERSAL__LOG_LEVEL__INFO'),
            self::C__NOTICE  => isys_application::instance()->container->get('language')
                ->get('LC_UNIVERSAL__LOG_LEVEL__NOTICE'),
            self::C__WARNING => isys_application::instance()->container->get('language')
                ->get('LC_UNIVERSAL__LOG_LEVEL__WARNING'),
            self::C__ERROR   => isys_application::instance()->container->get('language')
                ->get('LC_UNIVERSAL__LOG_LEVEL__ERROR'),
            self::C__FATAL   => isys_application::instance()->container->get('language')
                ->get('LC_UNIVERSAL__LOG_LEVEL__FATAL_ERROR')
        ];

        if ($p_level !== null) {
            return $l_levels[$p_level];
        }

        return $l_levels;
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
        assert($p_level == self::C__FATAL || $p_level == self::C__ERROR ||
            $p_level == self::C__WARNING || $p_level == self::C__NOTICE ||
            $p_level == self::C__INFO || $p_level == self::C__DEBUG);

        return $this->event($p_level, $p_message, $p_file, $p_line, $p_details);
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
        return $this->event(self::C__FATAL, $p_message, $p_file, $p_line, $p_details);
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
        return $this->event(self::C__ERROR, $p_message, $p_file, $p_line, $p_details);
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
        return $this->event(self::C__WARNING, $p_message, $p_file, $p_line, $p_details);
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
        return $this->event(self::C__NOTICE, $p_message, $p_file, $p_line, $p_details);
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
        return $this->event(self::C__INFO, $p_message, $p_file, $p_line, $p_details);
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
        return $this->event(self::C__DEBUG, $p_message, $p_file, $p_line, $p_details);
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
        assert(is_bool($p_write));
        assert(is_bool($p_standalone));

        if (!isset($this->m_log_file)) {
            throw new isys_exception_general('Log file has not been set yet.');
        }

        $l_logs = $this->get_log(true);

        if (is_null($l_logs)) {
            return '';
        }

        $l_content = '';

        // Add header:
        if ($p_standalone && isset($this->m_header)) {
            $l_content .= $this->m_header;
        }

        // Parse log messages:
        foreach ($l_logs as $l_timestamp => $l_msg) {
            $l_time = null;
            $l_micro = null;
            list($l_time, $l_micro) = explode(' ', $l_timestamp);

            $l_content .= '[' . date('Y-m-d H:i:s', $l_time) . ' ' . $l_micro . '] ' . strtoupper($this->level_as_string($l_msg['level'])) . ': ' . $l_msg['message'];

            if (isset($l_msg['file'])) {
                $l_content .= ' [' . $l_msg['file'];
                if (isset($l_msg['line'])) {
                    $l_content .= ':' . $l_msg['line'];
                }
                $l_content .= ']';
            }

            if (isset($l_msg['details'])) {
                $l_content .= var_export($l_msg['details'], true);
            }

            $l_content .= PHP_EOL;
        }

        // Add footer:
        if ($p_standalone && isset($this->m_footer)) {
            $l_content .= $this->m_footer;
        }

        if ($p_write) {
            $l_handle = fopen($this->m_log_file, 'a');

            if ($l_handle === false) {
                throw new isys_exception_general(sprintf('Log file "%s" is not writable.', $this->m_log_file));
            }

            if (fwrite($l_handle, $l_content) === false) {
                throw new isys_exception_general(sprintf('Failed to write to log file "%s".', $this->m_log_file));
            }
            if (fclose($l_handle) === false) {
                throw new isys_exception_general(sprintf('Failed to close log file "%s".', $this->m_log_file));
            }
        }

        $this->m_logs = null;

        return $l_content;
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
        assert(is_bool($p_print));
        assert(is_bool($p_standalone));

        $l_verbosity = $this->get_verbosity(true);
        if (is_null($l_verbosity)) {
            return;
        }

        // Parse messages:
        $l_content = '';

        foreach ($l_verbosity as $l_timestamp => $l_msg) {
            $l_time = null;
            $l_micro = null;
            list($l_time, $l_micro) = explode(' ', $l_timestamp);

            $l_content .= sprintf('[%s] %s: %s', date('Y-m-d H:i:s', $l_time), strtoupper($this->level_as_string($l_msg['level'])), $l_msg['message']);

            if (isset($l_msg['file'])) {
                $l_content .= ' [' . $l_msg['file'];
                if (isset($l_msg['line'])) {
                    $l_content .= ':' . $l_msg['line'];
                }
                $l_content .= ']';
            }

            if (isset($l_msg['details'])) {
                $l_content .= var_export($l_msg['details'], true);
            }
            $l_content .= PHP_EOL;
        }

        // Flush output:
        $this->m_verbosities = null;

        if ($p_print === true) {
            // Print content:
            if (defined('STDERR')) {
                fwrite(STDERR, $l_content);
            } else {
                echo $l_content;
            }

            return true;
        }

        // Return content as string:
        return $l_content;
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
        return $this->get_events('logs', $p_sort_by_date, $p_level);
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
        return $this->get_events('verbosities', $p_sort_by_date, $p_level);
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
        assert(is_string($p_file) && !empty($p_file));
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
        assert(is_bool($p_mode));
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
        assert(is_bool($p_mode));
        $this->m_destruct_flush = !!$p_mode;

        return $this;
    }

    /**
     * Writes log messages to file and destructs object.
     */
    public function __destruct()
    {
        if ($this->m_destruct_flush) {
            $this->flush_log();
        }
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
        $l_log = [
            'message' => $p_message
        ];

        if (isset($p_file)) {
            $l_log['file'] = $p_file;
        }

        if (isset($p_line)) {
            $l_log['line'] = $p_line;
        }

        if (isset($p_details)) {
            $l_log['details'] = $p_details;
        }

        // Add timestamp:
        $l_microseconds = null;
        $l_seconds = null;
        list($l_microseconds, $l_seconds) = explode(' ', microtime());
        $l_log['timestamp'] = $l_seconds . ' ' . $l_microseconds;

        // Check whether level is within current log level:
        if ($p_level & $this->m_log_level) {
            $this->m_logs[$p_level][] = $l_log;
        }

        // Check whether level is within current verbosity:
        if ($p_level & $this->m_verbose_level) {
            $this->m_verbosities[$p_level][] = $l_log;
        }

        // Flush automatically:
        if ($this->m_auto_flush) {
            if ($this->m_headers_sent) {
                $this->flush_log(true, false);
                $this->flush_verbosity(true, false);
            } else {
                $this->flush_log(true, true);
                $this->flush_verbosity(true, true);
                $this->m_headers_sent = true;
            }
        }

        return $l_log;
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
        $l_levels = [
            self::C__FATAL   => 'fatal error',
            self::C__ERROR   => 'error',
            self::C__WARNING => 'warning',
            self::C__NOTICE  => 'notice',
            self::C__INFO    => 'info',
            self::C__DEBUG   => 'debug'
        ];

        return $l_levels[$p_level];
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
        assert(is_bool($p_sort_by_date));

        if ($p_sort_by_date === true) {
            return $this->sort_events_by_date($p_type, $p_level);
        }

        $l_type = null;
        switch ($p_type) {
            case 'logs':
                $l_type = $this->m_logs;
                break;
            case 'verbosities':
                $l_type = $this->m_verbosities;
                break;
            default:
                throw new isys_exception_general(sprintf('Unkown event type: "%s"', $p_type));
        }

        if (isset($p_level)) {
            if (!array_key_exists($p_level, $l_type)) {
                return null;
            }

            return $l_type[$p_level];
        }

        return $l_type;
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
        $l_type = null;
        $l_sorted = [];

        switch ($p_type) {
            case 'logs':
                $l_type = $this->m_logs;
                break;
            case 'verbosities':
                $l_type = $this->m_verbosities;
                break;
            default:
                throw new isys_exception_general(sprintf('Unkown event type: "%s"', $p_type));
        }

        if (isset($p_level)) {
            if (!is_array($l_type[$p_level])) {
                return null;
            }

            foreach ($l_type[$p_level] as $l_event) {
                $l_event['level'] = $p_level;
                $l_sorted[$l_event['timestamp']] = $l_event;
            }
        } else {
            if (!is_array($l_type)) {
                return null;
            }

            foreach ($l_type as $l_event_level => $l_events) {
                foreach ($l_events as $l_event) {
                    $l_event['level'] = $l_event_level;
                    $l_sorted[$l_event['timestamp']] = $l_event;
                }
            }
        }

        ksort($l_sorted);

        return $l_sorted;
    }

    /**
     * Prohibits cloning.
     */
    final private function __clone()
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

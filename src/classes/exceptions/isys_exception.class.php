<?php

/**
 * i-doit
 *
 * Abstract base class for exceptions.
 *
 * @package     i-doit
 * @subpackage  Exceptions
 * @author      Andre Woesten <awoesten@i-doit.de>
 * @author      Dennis Stücken <dstuecken@i-doit.de>
 * @version     0.9
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
abstract class isys_exception extends Exception
{
    /**
     * Exception topic, may contain a language constant!
     *
     * @var  string
     */
    protected $m_exception_topic = 'Error';

    /**
     * Holds the full stack trace.
     *
     * @var  array
     */
    protected $m_full_trace;

    /**
     * Holds the current stack trace.
     *
     * @var  array
     */
    protected $m_last_trace;

    /**
     * This variable will hold the log file name.
     *
     * @var  string
     */
    protected $m_log_file_name;

    /**
     * Variable which holds extended information.
     *
     * @var  string
     */
    private $m_extinfo = '';

    /**
     * Return last trace result - Used for getting the correct trace result inside the graphical user interface.
     *
     * @return  string
     */
    public function get_last_trace()
    {
        return $this->m_last_trace;
    }

    /**
     * Display graphical exception.
     *
     * @param   Exception $p_exception
     *
     * @return  boolean
     */
    public function get(Exception $p_exception)
    {
        return isys_application::instance()->template->assign('error_topic', $this->get_exception_topic())
            ->assign('g_trace', isys_exception::get_trace())
            ->assign('g_error', $p_exception->getMessage())
            ->display('exception.tpl');
    }

    /**
     * Return the trace.
     *
     * @author  Dennis Stücken <dstuecken@syntics.de>
     * @return  mixed
     */
    public function get_trace()
    {
        $l_backtrace = "Backtrace:\n";

        if (!$this->m_full_trace) {
            $this->m_full_trace = $this->getTrace();
        }

        $l_trace = $this->m_full_trace;

        try {
            if (is_array($l_trace) && count($l_trace) > 0) {
                $i = count($l_trace);
                foreach ($l_trace as $l_value) {
                    if (isset($l_value["class"]) && is_object($l_value["class"])) {
                        $l_value["class"] = get_class($l_value["class"]);
                    }

                    $l_backtrace .= "<strong>#" . $i-- . "</strong> called: " . ((isset($l_value["class"])) ? $l_value["class"] : '') .
                        ((isset($l_value["type"])) ? $l_value["type"] : '') . $l_value["function"];

                    $l_backtrace .= " in [" . $l_value["file"] . ":" . $l_value["line"] . "]\n--\n";
                }
            }
        } catch (Exception $e) {
            ;
        }

        return $l_backtrace;
    }

    /**
     * Method for displaying this exception.
     *
     * @param   string $p_headline
     */
    public function display($p_headline = '')
    {
        if (is_object(isys_application::instance()->template)) {
            isys_application::instance()->template->assign('url', base64_encode('?' . $_SERVER['QUERY_STRING']))
                ->assign('ref', base64_encode($_SERVER['HTTP_REFERER']))
                ->assign('message', $this->getMessage())
                ->assign('message_base', base64_encode($this->getMessage() . "\n\n"))
                ->assign("backtrace", $this->m_last_trace)
                ->assign('error_topic', $this->get_exception_topic())
                ->display("exception-full.tpl");
        } else {
            echo "<pre>" . $p_headline . "<strong>" . $this->getMessage() . "</strong>\n\n\n" . $this->m_last_trace . "</pre>";
        }
    }

    /**
     * Magic __toString method.
     *
     * @return  string
     */
    public function __toString()
    {
        return 'Exception occured in (' . $this->getFile() . ':' . $this->getLine() . '): ' . $this->getMessage();
    }

    /**
     * Method for returning the "Exception topic" (will be displayed as a sort of "headline").
     *
     * @return  string
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function get_exception_topic()
    {
        return isys_application::instance()->container->get('language')
            ->get($this->m_exception_topic);
    }

    /**
     * This method will be used to write the exception log. It will only be written, when the exception reaches the GUI.
     * Meaning: It will only be written, if it isn't catched by any specific code.
     *
     * @return  isys_exception
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function write_log()
    {
        global $g_config;

        // We write a log, if an exception occurs!
        $l_log_line = [];
        $l_trace = $this->getTrace();

        // We reverse the array, so we can read from "start" to "end" (or "exception" for this matter).
        krsort($l_trace);
        foreach ($l_trace as $l_trace_item) {
            // We set the class empty, to avoid notices.
            if (!isset($l_trace_item['class'])) {
                $l_trace_item['class'] = '';
            }

            // We set the type empty, to avoid notices.
            if (!isset($l_trace_item['type'])) {
                $l_trace_item['type'] = '';
            }

            $l_log_line[] = '- File: ' . $l_trace_item['file'] . ' (line: ' . $l_trace_item['line'] . ")\n" . '  ' . $l_trace_item['class'] . $l_trace_item['type'] .
                $l_trace_item['function'];
        }

        $l_log_line[] = '- File: ' . $this->getFile() . ' (line: ' . $this->getLine() . ")\n" . '  Message: "' . $this->getMessage() . '"';

        if (class_exists('isys_factory_log')) {
            isys_factory_log::get_instance('exception')
                ->set_header('')
                ->set_footer('')
                ->set_log_file($g_config['base_dir'] . 'log/' . $this->m_log_file_name . '.log')
                ->error(" Exception Trace:\n\n" . implode("\n", $l_log_line) . "\n\n");
        }

        return $this;
    }

    /**
     * Method for returning the extended exception information.
     *
     * @return  string
     */
    protected function getExtendedInformation()
    {
        return $this->m_extinfo;
    }

    /**
     * Exception constructor.
     *
     * @param   string  $p_message
     * @param   string  $p_extinfo
     * @param   integer $p_code
     * @param   string  $p_log_file
     * @param   boolean $p_write_log
     *
     * @author  Dennis Stücken <dstuecken@i-doit.de>
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function __construct($p_message, $p_extinfo = '', $p_code = 0, $p_log_file = 'exception', $p_write_log = true)
    {
        parent::__construct($p_message, $p_code);

        $this->m_log_file_name = $p_log_file;
        $this->m_last_trace = $this->get_trace();
        $this->m_extinfo = $p_extinfo;

        // Emit exceptionTriggered signal
        isys_component_signalcollection::get_instance()
            ->emit('system.exceptionTriggered', $this);

        if ($p_write_log && isys_tenantsettings::get('logging.system.exceptions', true)) {
            $this->write_log();
        }
    }
}

<?php

/**
 * Wrapper function for function debug_backtrace. Good for debugging.
 *
 * @param   integer $p_limit
 * @param   boolean $p_show_args
 *
 * @return  array
 * @author  Van Quyen Hoang <qhoang@i-doit.org>
 */
function _Backtrace($p_limit = 0, $p_show_args = false)
{
    $l_option = ($p_show_args) ? DEBUG_BACKTRACE_PROVIDE_OBJECT : DEBUG_BACKTRACE_IGNORE_ARGS;
    $l_backtrace = debug_backtrace($l_option, (($p_limit > 0) ? $p_limit + 1 : $p_limit));

    unset($l_backtrace[0]);

    return $l_backtrace;
}

/**
 * Function for putting the given backtrace in a nice readable form into a file - helpful for debugging!
 *
 * @param   array   $p_backtrace
 * @param   boolean $p_append
 * @param   boolean $p_show_args
 * @param   integer $p_limit
 *
 * @author  Leonard Fischer <lfischer@i-doit.com>
 */
function print_backtrace_file($p_backtrace = null, $p_append = false, $p_show_args = false, $p_limit = 0)
{
    $l_content = [];

    if ($p_backtrace === null) {
        $p_backtrace = _Backtrace($p_limit, $p_show_args);
    }

    if (!is_array($p_backtrace)) {
        $l_content[] = 'Given backtrace is no array... It\'s a "' . gettype($p_backtrace) . '".';
    } else {
        foreach ($p_backtrace as $l_trace) {
            $l_content[] = $l_trace['file'] . ' (' . $l_trace['line'] . ")\n   " . $l_trace['class'] . ' -> ' . $l_trace['function'] . '()';
        }
    }

    isys_file_put_contents(isys_glob_get_temp_dir() . 'backtrace_output.txt', implode("\n", $l_content) . "\n\n", ($p_append ? FILE_APPEND : 0));
}

/**
 * Function for putting the value into a file - helpful for debugging!
 *
 * @param   mixed   $p_value
 * @param   boolean $p_append
 *
 * @author  Van Quyen Hoang <qhoang@i-doit.org>
 */
function print_ar_file($p_value, $p_append = false)
{
    isys_file_put_contents(isys_glob_get_temp_dir() . 'debug_output.txt', var_export($p_value, true) . "\n", ($p_append ? FILE_APPEND : 0));
}

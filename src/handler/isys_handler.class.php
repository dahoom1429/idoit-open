<?php

/**
 * Class isys_handler
 */
abstract class isys_handler implements isys_handler_interface
{
    /* The current date */
    /**
     * @var
     */
    protected $m_day;

    /**
     * @var
     */
    protected $m_month;

    /**
     * @var
     */
    protected $m_year;

    /**
     * @return bool
     */
    public function needs_login()
    {
        return true;
    }

    /**
     * Logout current session
     */
    public function logout()
    {
        global $g_comp_session;

        if (is_object($g_comp_session)) {
            if ($g_comp_session->is_logged_in()) {
                if (function_exists("verbose")) {
                    verbose("Logging out\n");
                }

                $g_comp_session->logout();
            }
        }
    }

    /**
     * @param string $headline
     * @param int    $done
     * @param int    $total
     * @param int    $size
     */
    public function progress($name, $done, $total, $size = 42)
    {
        static $start_time;

        if ($done === 0) {
            return;
        }

        // if we go over our bound, just ignore it
        if ($done > $total) {
            return;
        }

        if (empty($start_time)) {
            $start_time = time();
        }
        $now = time();

        $perc = (double)($done / $total);

        $bar = floor($perc * $size);

        $status_bar = "\r" . $name . ": [";
        $status_bar .= str_repeat("=", $bar);
        if ($bar < $size) {
            $status_bar .= ">";
            $status_bar .= str_repeat(" ", $size - $bar);
        } else {
            $status_bar .= "=";
        }

        $disp = number_format($perc * 100, 0);

        $status_bar .= "] $disp%  $done/$total";

        $rate = ($now - $start_time) / $done;
        $left = $total - $done;
        $eta = round($rate * $left, 2);

        $elapsed = $now - $start_time;

        $status_bar .= " remaining: ~" . number_format($eta) . " sec.  elapsed: " . number_format($elapsed) . " sec.";

        echo "$status_bar  ";

        flush();

        // when done, send a newline
        if ($done == $total) {
            echo "\n";
        }

    }

    /**
     * Sends a mail.
     *
     * @param string $p_email
     * @param string $p_subject
     * @param string $p_message
     *
     * @return boolean
     */
    public function _mail($p_email, $p_subject, $p_message)
    {
        try {
            $l_mailer = new isys_library_mail();

            if ($l_mailer->check_address($p_email)) {
                // Configure mail.
                $l_mailer->AddAddress($p_email);
                $l_mailer->Subject = isys_tenantsettings::get('system.email.subject-prefix', '') . $p_subject;
                $l_mailer->Body = nl2br($p_message);
                $l_mailer->isHTML(true);

                // Use SMTP and send.
                $l_mailer->IsSMTP();

                if ($l_mailer->Send()) {
                    verbose(".. successfull.", false);

                    return true;
                } else {
                    verbose(".. error: " . $l_mailer->ErrorInfo . "", false);

                    return false;
                }
            } else {
                verbose("E-mail: " . $p_email . " is not a valid address.");

                return false;
            }
        } catch (Exception $e) {
            verbose(" ### Error: " . $e->getMessage());
        }
    }

    /**
     * Displays a message, which shows which config file the user has to edit.
     */
    public function display_config_hint()
    {
        global $g_handler_config, $g_absdir;

        if (C__WINDOWS) {
            $l_mandator_cmd = "php.exe controller.php -v -m mandator ls";
        } else {
            $l_mandator_cmd = "./mandator ls";
        }

        error("Login configuration error: You should setup \$g_userconf " . "in {$g_handler_config} to do an automated (script-based) login.\n\n" .

            "Check the example in \n" . str_replace("config/", "config/examples/", $g_handler_config) . " and copy it to \n" . $g_absdir . "/src/handler/config/.\n\n" .

            "Or use -u user -p pass -i mandator-id instead. (e.g. -u admin -p admin -i 1)\n\n" . "Get a list of your mandator ids with \"" . $l_mandator_cmd . "\"\n\n");
    }

    /**
     *
     */
    public function __destruct()
    {
        //$this->logout();
    }

    /**
     * @return mixed
     */
    protected function get_title()
    {
        return str_replace('isys_handler_', '', get_class($this));
    }
}

/**
 * i-doit
 *
 * Workflow handler
 *
 * @package    i-doit
 * @subpackage General
 * @author     Dennis Stücken <dstuecken@i-doit.de>
 * @copyright  synetics GmbH
 * @license    http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 *
 */
interface isys_handler_interface
{
    /**
     * @return mixed
     */
    public function init();

    /**
     * @return mixed
     */
    public function needs_login();
}

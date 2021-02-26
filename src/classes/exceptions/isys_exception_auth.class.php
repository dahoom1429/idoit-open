<?php

use idoit\Component\Logger;
use Monolog\Handler\StreamHandler;

/**
 * Class isys_exception_auth
 *
 * @package     i-doit
 * @subpackage  Exceptions
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_exception_auth extends isys_exception
{
    /**
     * @var  string
     */
    protected $m_exception_topic = 'LC__AUTH__EXCEPTION_TITLE';

    /**
     * @var Logger
     */
    private static $logger = null;

    /**
     * isys_exception_auth constructor.
     *
     * @param string $p_message
     * @param string $p_extinfo
     * @param int    $p_code
     *
     * @throws Exception
     */
    public function __construct($p_message, $p_extinfo = '', $p_code = 0)
    {
        parent::__construct(
            isys_application::instance()->container->get('language')->get('LC__AUTH__EXCEPTION') . $p_message,
            $p_extinfo,
            $p_code,
            '',
            false
        );
    }

    /**
     * This method will be used to write the exception log. It will only be written, when the exception reaches the GUI.
     * Meaning: It will only be written, if it isn't catched by any specific code.
     *
     * @return $this
     * @throws Exception
     */
    public function write_log()
    {
        if (isys_tenantsettings::get('auth.logging', 0)) {
            $session = isys_application::instance()->container->get('session');

            if (self::$logger === null) {
                $path = isys_application::instance()->app_path;
                $tenant = isys_helper_upload::prepare_filename($session->get_mandator_name());
                $date = date('Y-m-d');

                self::$logger = new Logger('Auth', [new StreamHandler("{$path}/log/auth__{$tenant}__{$date}.log")]);
            }

            self::$logger->error('Triggered by "' . $session->get_current_username() . '": ' . $this->getMessage());
        }

        return $this;
    }
}

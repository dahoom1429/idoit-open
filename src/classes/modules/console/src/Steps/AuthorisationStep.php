<?php
/**
 *
 *
 * @package     i-doit
 * @subpackage
 * @author      Pavel Abduramanov <pabduramanov@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */

namespace idoit\Module\Console\Steps;

use idoit\Component\Security\Hash\Password;
use idoit\Component\Security\Hash\PasswordVerify;
use idoit\Module\Console\Steps\Message\ErrorLevel;

class AuthorisationStep extends Check
{
    private $password;

    private $user;

    public function __construct($user, $password, $level = ErrorLevel::ERROR)
    {
        $this->user = $user;
        $this->password = $password;
        $this->level = $level;
    }

    protected function check()
    {
        global $g_admin_auth;
        $pw = is_array($g_admin_auth) && isset($g_admin_auth[$this->user]) ? $g_admin_auth[$this->user] : '';

        return PasswordVerify::instance()
            ->verify($this->password, $pw);
    }

    /**
     * Get name of the step
     *
     * @return string
     */
    public function getName()
    {
        return 'Authorisation';
    }
}

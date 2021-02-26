<?php

/**
 * i-doit
 *
 * Export helper for global category password.
 *
 * @package     i-doit
 * @subpackage  Export
 * @author      Leonard Fischer <lfischer@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_global_password_export_helper extends isys_export_helper
{
    /**
     * Export helper for password - will decrypt it, if the tenant setting is active.
     *
     * @param  string $passwordHash
     *
     * @return string
     */
    public function password($passwordHash)
    {
        if (!empty($passwordHash) && isys_tenantsettings::get('password.decrypt.in-export-import', 0)) {
            return isys_helper_crypt::decrypt($passwordHash);
        }

        return $passwordHash;
    }

    /**
     * Import method for passwords - will encrypt it, if the tenant setting is active.
     *
     * @param  mixed $password
     *
     * @return string
     */
    public function password_import($password)
    {
        if (is_array($password) && isset($password[C__DATA__VALUE])) { // @see ID-5752
            $password = $password[C__DATA__VALUE];
        }

        if (!empty($password) && isys_tenantsettings::get('password.decrypt.in-export-import', 0)) {
            return isys_helper_crypt::encrypt($password);
        }

        return $password;
    }
}
<?php

namespace idoit\Module\System\Cleanup;

use idoit\Component\Security\Hash\PasswordVerify;
use isys_cmdb_dao;
use isys_helper_crypt;

/**
 * Class TenantDatabaseEncryption
 *
 * @package idoit\Module\System\Cleanup
 */
class TenantDatabaseEncryption extends AbstractCleanup
{
    /**
     * Method for starting the cleanup process.
     */
    public function process()
    {
        global $g_admin_auth;

        $adminUser = $_POST['user'];
        $adminPass = $_POST['pass'];

        if (!isset($g_admin_auth[$adminUser]) || !(PasswordVerify::instance()->verify($adminPass, $g_admin_auth[$adminUser]) || $adminPass == $g_admin_auth[$adminUser])) {
            echo 'The provided credentials did not match.';
            return;
        }

        // Create a DAO instance with the system database.
        $dao = isys_cmdb_dao::instance($this->container->get('database_system'));
        $tenantId = $dao->convert_sql_id($this->container->get('session')->get_mandator_id());

        $sql = "SELECT isys_mandator__db_pass AS pass 
            FROM isys_mandator 
            WHERE isys_mandator__id = {$tenantId} 
            AND isys_mandator__db_pass <> ''
            AND (isys_mandator__db_password IS NULL OR isys_mandator__db_password = '')
            LIMIT 1;";

        $password = $dao->retrieve($sql)->get_row_value('pass');

        if (empty($password)) {
            echo 'It seems as if your tenant database password is empty or has already been encrypted!';
            return;
        }

        $encryptedPassword = $dao->convert_sql_text(isys_helper_crypt::encrypt($password));

        $sql = "UPDATE isys_mandator 
            SET isys_mandator__db_pass = NULL,
            isys_mandator__db_password = {$encryptedPassword}
            WHERE isys_mandator__id = {$tenantId} 
            LIMIT 1;";

        $dao->update($sql) && $dao->apply_update();

        echo 'The password has been encrypted successfully!';
    }
}

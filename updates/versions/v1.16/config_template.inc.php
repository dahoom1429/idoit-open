<?php

/**
 * i-doit
 *
 * Basic configuration
 *
 * @package     i-doit
 * @subpackage  General
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */

/*
 * Database configuration
 *
 * This configuration is for the system database. Don't forget to use MySQL with the InnoDB table-driver.
 * Only TCP/IP Hosts are supported here, no UNIX sockets!
 */
$g_db_system = [
    'type' => 'mysqli',
    'host' => '%config.db.host%',
    'port' => '%config.db.port%',
    'user' => '%config.db.username%',
    'pass' => '%config.db.password%',
    'name' => '%config.db.name%'
];

/*
 * This login is used for the i-doit administration GUI. Note that an empty password will not work.
 * Leave the password empty to disable the admin center.
 *
 * Use the GUI or bcrypt to crypt your password.
 *
 * Syntax: 'username' => 'bcrypt-encrypted-password'
 */
$g_admin_auth = [
    '%config.adminauth.username%' => '%config.adminauth.password%',
];

/*
 * Crypto hash used as key for encription with phpseclib.
 */
$g_crypto_hash = '%config.crypt.hash%';

/*
 * It is possible to deactivate add-on uploads for the admin-center.
 */
$g_disable_addon_upload = '%config.admin.disable_addon_upload%';

/*
 * i-doit License token.
 */
$g_license_token = '%config.license.token%';

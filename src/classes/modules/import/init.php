<?php
/**
 * i-doit
 *
 * Module initializer
 *
 * @package     i-doit
 * @subpackage  Modules
 * @author      Dennis Stücken <dstuecken@i-doit.com>
 * @version     1.1
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */

if (include_once('isys_module_import_autoload.class.php')) {
    spl_autoload_register('isys_module_import_autoload::init');
}

define('C__IMPORT__GET__IMPORT', 1);
define('C__IMPORT__GET__FINISHED_IMPORTS', 2);
define('C__IMPORT__GET__SCRIPTS', 3);
define('C__IMPORT__GET__OCS_OBJECTS', 4);
define('C__IMPORT__GET__CSV', 5);
define('C__IMPORT__GET__JDISC', 6);
define('C__IMPORT__GET__LDAP', 7);
define('C__IMPORT__GET__SHAREPOINT', 8);
define('C__IMPORT__GET__CABLING', 9);
define('C__IMPORT__GET__LOGINVENTORY', 10);
define('C__IMPORT__GET__DOWNLOAD', 11);
define('C__CMDB__GET__CSV_AJAX', 'call_csv_handler_action');
// Path to log files.
define('C__IMPORT__LOG_DIRECTORY', BASE_DIR . '/log/');

// Append import config how to handle with validation errors
isys_tenantsettings::extend([
    'LC__MODULE__IMPORT' => [
        'import.validation.break-on-error' => [
            'title'       => 'LC__MODULE__IMPORT__VALIDATION_BREAK_ON_ERROR',
            'type'        => 'select',
            'options'     => [
                '0' => 'LC__UNIVERSAL__NO',
                '1' => 'LC__UNIVERSAL__YES'
            ],
            'default'     => '1',
            'description' => 'LC__MODULE__IMPORT__VALIDATION_BREAK_ON_ERROR_DESCRIPTION'
        ],
        'import.validation.empty-attribute-on-error' => [
            'title'       => 'LC__MODULE__IMPORT__VALIDATION_EMPTY_ATTRIBUTE_ON_ERROR',
            'type'        => 'select',
            'options'     => [
                '0' => 'LC__UNIVERSAL__NO',
                '1' => 'LC__UNIVERSAL__YES'
            ],
            'default'     => '0',
            'description' => 'LC__MODULE__IMPORT__VALIDATION_EMPTY_ATTRIBUTE_ON_ERROR_DESCRIPTION'
        ],
        'import.csv.overwrite-objecttype' => [
            'title'       => 'LC__MODULE__IMPORT__CSV_OVERWRITE_OBJECT_TYPE',
            'type'        => 'select',
            'options'     => [
                '0' => 'LC__UNIVERSAL__NO',
                '1' => 'LC__UNIVERSAL__YES'
            ],
            'default'     => '0',
            'description' => 'LC__MODULE__IMPORT__CSV_OVERWRITE_OBJECT_TYPE_DESCRIPTION'
        ]
    ]
]);

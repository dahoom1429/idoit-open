<?php

/**
 * i-doit
 *
 * Class autoloader.
 *
 * @package     modules
 * @subpackage  multiedit
 * @author      Van Quyen Hoang <qhoang@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_module_multiedit_autoload extends isys_module_manager_autoload
{
    /**
     * Module specific autoloader.
     *
     * @param string $className
     *
     * @return bool
     */
    public static function init($className)
    {
        $addOnPath = '/src/classes/modules/multiedit/';
        $classMap = [
            'isys_auth_multiedit'                => 'auth/isys_auth_multiedit.class.php',
            'isys_multiedit_dao'                 => 'dao/isys_multiedit_dao.class.php',
            'isys_popup_multiedit_add_values'    => 'src/Popup/isys_popup_multiedit_add_values.class.php',
            'isys_ajax_handler_multiedit'        => 'handler/ajax/isys_ajax_handler_multiedit.class.php',
            'isys_cmdb_dao_category_g_multiedit' => 'cmdb/dao/global/isys_cmdb_dao_category_g_multiedit.class.php',
            'isys_cmdb_ui_category_g_multiedit'  => 'cmdb/ui/global/isys_cmdb_ui_category_g_multiedit.class.php',
        ];

        if (isset($classMap[$className]) && parent::include_file($addOnPath . $classMap[$className])) {
            isys_cache::keyvalue()->ns('autoload')->set($className, $addOnPath . $classMap[$className]);

            return true;
        }

        return false;
    }
}

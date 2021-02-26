<?php

/**
 * i-doit
 *
 * Class autoloader.
 *
 * @package     Modules
 * @subpackage  Jdisc
 * @author      Leonard Fischer <lfischer@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_module_jdisc_autoload extends isys_module_manager_autoload
{
    /**
     * Module specific autoloader.
     *
     * @param   string $className
     *
     * @return  boolean
     */
    public static function init($className)
    {
        $addOnPath = '/src/classes/modules/jdisc/';
        $classMap = [
            'isys_handler_jdisc_discovery'                    => 'handler/controller/isys_handler_jdisc_discovery.class.php',
            'isys_handler_jdisc'                              => 'handler/controller/isys_handler_jdisc.class.php',
            'isys_ajax_handler_jdisc'                         => 'handler/ajax/isys_ajax_handler_jdisc.class.php',
            'isys_module_jdisc'                               => 'isys_module_jdisc.class.php',
            'isys_module_jdisc_autoload'                      => 'isys_module_jdisc_autoload.class.php',
            'isys_jdisc_dao'                                  => 'dao/isys_jdisc_dao.class.php',
            'isys_jdisc_dao_category'                         => 'dao/isys_jdisc_dao_category.class.php',
            'isys_jdisc_dao_category_interface'               => 'dao/isys_jdisc_dao_category_interface.class.php',
            'isys_jdisc_dao_category_connection_endpoint'     => 'dao/isys_jdisc_dao_category_connection_endpoint.class.php',
            'isys_jdisc_dao_category_support_entitlement'     => 'dao/isys_jdisc_dao_category_support_entitlement.class.php',
            'isys_jdisc_dao_cluster'                          => 'dao/isys_jdisc_dao_cluster.class.php',
            'isys_jdisc_dao_devices'                          => 'dao/isys_jdisc_dao_devices.class.php',
            'isys_jdisc_dao_custom_attributes'                => 'dao/isys_jdisc_dao_custom_attributes.class.php',
            'isys_jdisc_dao_discovery'                        => 'dao/isys_jdisc_dao_discovery.class.php',
            'isys_jdisc_dao_network'                          => 'dao/isys_jdisc_dao_network.class.php',
            'isys_jdisc_dao_software'                         => 'dao/isys_jdisc_dao_software.class.php',
            'isys_jdisc_dao_software_database'                => 'dao/isys_jdisc_dao_software_database.class.php',
            'isys_jdisc_dao_data'                             => 'dao/isys_jdisc_dao_data.class.php',
            'isys_jdisc_dao_matching'                         => 'dao/isys_jdisc_dao_matching.class.php',
            'isys_popup_duplicate_jdisc_profile'              => 'popups/isys_popup_duplicate_jdisc_profile.class.php',
            'isys_cmdb_ui_category_g_jdisc_discovery'         => 'cmdb/ui/global/isys_cmdb_ui_category_g_jdisc_discovery.class.php',
            'isys_cmdb_ui_category_g_jdisc_ca'                => 'cmdb/ui/global/isys_cmdb_ui_category_g_jdisc_ca.class.php',
            'isys_cmdb_ui_category_g_jdisc_custom_attributes' => 'cmdb/ui/global/isys_cmdb_ui_category_g_jdisc_custom_attributes.class.php',
            'isys_cmdb_ui_category_g_assigned_subscriptions'  => 'cmdb/ui/global/isys_cmdb_ui_category_g_assigned_subscriptions.class.php',
            'isys_cmdb_ui_category_g_assigned_users'          => 'cmdb/ui/global/isys_cmdb_ui_category_g_assigned_users.class.php',
            'isys_cmdb_ui_category_g_cloud_subscriptions'     => 'cmdb/ui/global/isys_cmdb_ui_category_g_cloud_subscriptions.class.php',
            'isys_cmdb_ui_category_g_connection_endpoint'     => 'cmdb/ui/global/isys_cmdb_ui_category_g_connection_endpoint.class.php',
            'isys_cmdb_ui_category_g_support_entitlement'     => 'cmdb/ui/global/isys_cmdb_ui_category_g_support_entitlement.class.php',
            'isys_cmdb_dao_jdisc'                             => 'cmdb/dao/isys_cmdb_dao_jdisc.class.php',
            'isys_cmdb_dao_list_catg_jdisc_custom_attributes' => 'cmdb/dao/list/isys_cmdb_dao_list_catg_jdisc_custom_attributes.class.php',
            'isys_cmdb_dao_list_catg_jdisc_ca'                => 'cmdb/dao/list/isys_cmdb_dao_list_catg_jdisc_ca.class.php',
            'isys_cmdb_dao_list_catg_assigned_subscriptions'  => 'cmdb/dao/list/isys_cmdb_dao_list_catg_assigned_subscriptions.class.php',
            'isys_cmdb_dao_list_catg_assigned_users'          => 'cmdb/dao/list/isys_cmdb_dao_list_catg_assigned_users.class.php',
            'isys_cmdb_dao_list_catg_cloud_subscriptions'     => 'cmdb/dao/list/isys_cmdb_dao_list_catg_cloud_subscriptions.class.php',
            'isys_cmdb_dao_list_catg_connection_endpoint'     => 'cmdb/dao/list/isys_cmdb_dao_list_catg_connection_endpoint.class.php',
            'isys_cmdb_dao_list_catg_support_entitlement'     => 'cmdb/dao/list/isys_cmdb_dao_list_catg_support_entitlement.class.php',
            'isys_cmdb_dao_category_g_jdisc_ca'               => 'cmdb/dao/global/isys_cmdb_dao_category_g_jdisc_ca.class.php',
            'isys_cmdb_dao_category_g_jdisc_discovery'        => 'cmdb/dao/global/isys_cmdb_dao_category_g_jdisc_discovery.class.php',
            'isys_cmdb_dao_category_g_assigned_subscriptions' => 'cmdb/dao/global/isys_cmdb_dao_category_g_assigned_subscriptions.class.php',
            'isys_cmdb_dao_category_g_assigned_users'         => 'cmdb/dao/global/isys_cmdb_dao_category_g_assigned_users.class.php',
            'isys_cmdb_dao_category_g_cloud_subscriptions'    => 'cmdb/dao/global/isys_cmdb_dao_category_g_cloud_subscriptions.class.php',
            'isys_cmdb_dao_category_g_connection_endpoint'    => 'cmdb/dao/global/isys_cmdb_dao_category_g_connection_endpoint.class.php',
            'isys_cmdb_dao_category_g_support_entitlement'    => 'cmdb/dao/global/isys_cmdb_dao_category_g_support_entitlement.class.php',
        ];

        if (isset($classMap[$className]) && parent::include_file($addOnPath . $classMap[$className])) {
            isys_cache::keyvalue()->ns('autoload')->set($className, $addOnPath . $classMap[$className]);

            return true;
        }

        return false;
    }
}

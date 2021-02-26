<?php

use idoit\Component\Property\Exception\UnsupportedConfigurationTypeException;
use idoit\Component\Property\Type\DialogProperty;
use idoit\Component\Property\Type\DialogYesNoProperty;
use idoit\Component\Property\Type\DynamicProperty;

/**
 * i-doit
 *
 * DAO: global folder category for monitoring.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Leonard Fischer <lfischer@i-doit.com>
 * @version     1.0.0
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 * @since       i-doit 1.3.0
 */
class isys_cmdb_dao_category_g_monitoring extends isys_cmdb_dao_category_global
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'monitoring';

    /**
     * Category entry is purgable
     *
     * @var  boolean
     */
    protected $m_is_purgable = true;

    /**
     * Callback method for property monitoring_host.
     *
     * @param   isys_request $p_request
     *
     * @return  string
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function callback_property_monitoring_host(isys_request $p_request)
    {
        global $g_comp_database;

        $l_hosts = [];
        $l_hosts_res = isys_monitoring_dao_hosts::instance($g_comp_database)
            ->get_data();

        if (is_countable($l_hosts_res) && count($l_hosts_res)) {
            while ($l_row = $l_hosts_res->get_row()) {
                if ($l_row['isys_monitoring_hosts__active']) {
                    $l_hosts[$l_row['isys_monitoring_hosts__id']] = $l_row['isys_monitoring_hosts__title'];
                }
            }
        }

        return $l_hosts;
    }

    /**
     * This method selects all host configurations, which are used at least once.
     *
     * @return  isys_component_dao_result
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function get_used_livestatus_host()
    {
        return $this->retrieve('SELECT isys_monitoring_hosts__id
			FROM isys_monitoring_hosts
			INNER JOIN isys_catg_monitoring_list ON isys_catg_monitoring_list__isys_monitoring_hosts__id = isys_monitoring_hosts__id
			LEFT JOIN isys_obj ON isys_obj__id = isys_catg_monitoring_list__isys_obj__id
			WHERE isys_catg_monitoring_list__status = ' . $this->convert_sql_int(C__RECORD_STATUS__NORMAL) . '
			AND isys_monitoring_hosts__type = "livestatus"
			AND isys_obj__status = ' . $this->convert_sql_int(C__RECORD_STATUS__NORMAL) . ';');
    }

    /**
     * @param $row
     *
     * @return string
     * @throws isys_exception_database
     */
    public function dynamicHostnameFormatter($row)
    {
        $query = 'SELECT 
            (
                CASE 
                    WHEN isys_catg_monitoring_list__host_name_selection = 1 THEN
                        (SELECT (CASE WHEN isys_catg_ip_list__domain IS NULL THEN isys_catg_ip_list__hostname ELSE CONCAT(isys_catg_ip_list__hostname, \'.\', isys_catg_ip_list__domain) END) FROM isys_catg_ip_list WHERE isys_catg_ip_list__primary = 1 AND isys_catg_ip_list__isys_obj__id = isys_catg_monitoring_list__isys_obj__id) 
                    WHEN isys_catg_monitoring_list__host_name_selection = 2 THEN
                        (SELECT isys_catg_ip_list__hostname FROM isys_catg_ip_list WHERE isys_catg_ip_list__primary = 1 AND isys_catg_ip_list__isys_obj__id = isys_catg_monitoring_list__isys_obj__id)
                    WHEN isys_catg_monitoring_list__host_name_selection = 3 THEN
                        (SELECT isys_obj__title FROM isys_obj WHERE isys_obj__id = isys_catg_monitoring_list__isys_obj__id)
                    ELSE isys_catg_monitoring_list__host_name
                END
            ) as hostname
            FROM isys_catg_monitoring_list
            WHERE isys_catg_monitoring_list__id = ' . $this->convert_sql_id($row['isys_catg_monitoring_list__id']);
        return $this->retrieve($query)->get_row_value('hostname') ?: isys_tenantsettings::get('gui.empty_value', '-');
    }

    /**
     * @return array
     * @throws UnsupportedConfigurationTypeException
     */
    protected function dynamic_properties()
    {
        return [
            '_host_name' => (new DynamicProperty(
                'LC__CATG__MONITORING__HOSTNAME',
                'isys_catg_monitoring_list__id',
                'isys_catg_monitoring_list',
                [
                    $this,
                    'dynamicHostnameFormatter'
                ]
            ))
        ];
    }

    /**
     * Method for returning the properties.
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.com>
     * @author  Van Quyen Hoang <qhoang@i-doit.com>
     */
    protected function properties()
    {
        return [
            'active' => new DialogYesNoProperty(
                'C__CATG__MONITORING__ACTIVE',
                'LC__CATG__MONITORING__ACTIVE',
                'isys_catg_monitoring_list__active',
                'isys_catg_monitoring_list'
            ),
            'monitoring_host' => (new DialogProperty(
                'C__CATG__MONITORING__HOST',
                'LC__CATG__MONITORING__HOST',
                'isys_catg_monitoring_list__isys_monitoring_hosts__id',
                'isys_catg_monitoring_list',
                'isys_monitoring_hosts'
            ))->mergePropertyUiParams(
                [
                    'p_arData' => new isys_callback([
                        'isys_cmdb_dao_category_g_monitoring',
                        'callback_property_monitoring_host'
                    ]),
                    'p_strTable' => null
                ]
            ),
            'host_name'           => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CATG__MONITORING__HOSTNAME',
                    C__PROPERTY__INFO__DESCRIPTION => 'Hostname'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_monitoring_list__host_name',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT (CASE WHEN isys_catg_monitoring_list__host_name_selection = ' .
                        C__MONITORING__NAME_SELECTION__OBJ_ID . ' THEN isys_obj__title
                                   WHEN isys_catg_monitoring_list__host_name_selection = ' . C__MONITORING__NAME_SELECTION__HOSTNAME . ' THEN isys_catg_ip_list__hostname
                                   WHEN isys_catg_monitoring_list__host_name_selection = ' . C__MONITORING__NAME_SELECTION__HOSTNAME_FQDN . ' THEN CONCAT(isys_catg_ip_list__hostname, \'.\', isys_catg_ip_list__domain)
                                   ELSE isys_catg_monitoring_list__host_name END)
                            FROM isys_catg_monitoring_list
                            INNER JOIN isys_obj ON isys_obj__id = isys_catg_monitoring_list__isys_obj__id
                            INNER JOIN isys_catg_ip_list ON isys_catg_ip_list__isys_obj__id = isys_obj__id AND isys_catg_ip_list__primary = 1',
                        'isys_catg_monitoring_list',
                        'isys_catg_monitoring_list__id',
                        'isys_catg_monitoring_list__isys_obj__id'
                    )
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CATG__MONITORING_HOST_NAME',
                    C__PROPERTY__UI__PARAMS => [
                        'p_bInfoIconSpacer' => 0,
                        'disableInputGroup' => true
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__VIRTUAL => true,
                    C__PROPERTY__PROVIDES__IMPORT  => true,
                    C__PROPERTY__PROVIDES__EXPORT  => true,
                    C__PROPERTY__PROVIDES__REPORT => false
                ]
            ]),
            'host_name_selection' => array_replace_recursive(isys_cmdb_dao_category_pattern::int(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CATG__MONITORING__HOSTNAME_SELECTION',
                    C__PROPERTY__INFO__DESCRIPTION => 'Hostname selection'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD => 'isys_catg_monitoring_list__host_name_selection'
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID => 'C__CATG__MONITORING__HOSTNAME'
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__LIST   => false,
                    C__PROPERTY__PROVIDES__REPORT => false,
                    C__PROPERTY__PROVIDES__IMPORT => true,
                    C__PROPERTY__PROVIDES__EXPORT => true
                ]
            ]),
            'description'         => array_replace_recursive(isys_cmdb_dao_category_pattern::commentary(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__LOGBOOK__DESCRIPTION',
                    C__PROPERTY__INFO__DESCRIPTION => 'Description'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD => 'isys_catg_monitoring_list__description'
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_GLOBAL . defined_or_default('C__CATG__MONITORING', 'C__CATG__MONITORING')
                ]
            ])
        ];
    }
}

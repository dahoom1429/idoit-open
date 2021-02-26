<?php

use idoit\Component\Property\Type\DialogPlusProperty;
use idoit\Component\Property\Type\DialogProperty;
use idoit\Component\Property\Type\DialogYesNoProperty;

/**
 * i-doit
 *
 * DAO: global category for SIM cards
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_category_g_sim extends isys_cmdb_dao_category_global
{
    /**
     * Category's name. Will be used for the identifier, constant, main table,
     * and many more.
     *
     * @var string
     */
    protected $m_category = 'sim';

    /**
     * Category entry is purgable
     *
     * @var bool
     */
    protected $m_is_purgable = true;


    /**
     * Return Category Data.
     *
     * @param  integer $p_catg_list_id
     * @param  mixed   $p_obj_id
     * @param  string  $p_condition
     * @param  mixed   $p_filter
     * @param  integer $p_status
     *
     * @return isys_component_dao_result
     */
    public function get_data($p_catg_list_id = null, $p_obj_id = null, $p_condition = '', $p_filter = null, $p_status = null)
    {
        $sql = 'SELECT * FROM isys_catg_sim_list 
            LEFT JOIN isys_cp_contract_type ON isys_catg_sim_list__isys_cp_contract_type__id = isys_cp_contract_type__id 
            LEFT JOIN isys_network_provider ON isys_catg_sim_list__isys_network_provider__id = isys_network_provider__id 
            LEFT JOIN isys_telephone_rate ON isys_catg_sim_list__isys_telephone_rate__id = isys_telephone_rate__id  
            INNER JOIN isys_obj ON isys_obj__id = isys_catg_sim_list__isys_obj__id 
            WHERE TRUE ' . $p_condition . ' ' . $this->prepare_filter($p_filter);

        if ($p_obj_id !== null) {
            $sql .= $this->get_object_condition($p_obj_id);
        }

        if ($p_catg_list_id !== null) {
            $sql .= ' AND isys_catg_sim_list__id = ' . $this->convert_sql_id($p_catg_list_id);
        }

        if ($p_status !== null) {
            $sql .= ' AND isys_catg_sim_list__status = ' . $this->convert_sql_int($p_status);
        }

        return $this->retrieve($sql . ';');
    }

    /**
     * Method for returning the properties.
     *
     * @return  array
     */
    protected function properties()
    {
        return [
            'type' => new DialogProperty(
                'C__CATS__CP_CONTRACT__TYPE',
                'LC__CMDB__CATG__TYPE',
                'isys_catg_sim_list__isys_cp_contract_type__id',
                'isys_catg_sim_list',
                'isys_cp_contract_type'
            ),
            'network_provider' => new DialogPlusProperty(
                'C__CATS__CP_CONTRACT__NETWORK_PROVIDER',
                'LC__CMDB__CATS_CP_CONTRACT__NETWORK_PROVIDER',
                'isys_catg_sim_list__isys_network_provider__id',
                'isys_catg_sim_list',
                'isys_network_provider'
            ),
            'telephone_rate' => new DialogPlusProperty(
                'C__CATS__CP_CONTRACT__TELEPHONE_RATE',
                'LC__CMDB__CATS_CP_CONTRACT__TELEPHONE_RATE',
                'isys_catg_sim_list__isys_telephone_rate__id',
                'isys_catg_sim_list',
                'isys_telephone_rate'
            ),
            'start'            => array_replace_recursive(isys_cmdb_dao_category_pattern::date(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS_CP_CONTRACT__START_DATE',
                    C__PROPERTY__INFO__DESCRIPTION => 'LC__CMDB__CATS_CP_CONTRACT__START_DATE'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD => 'isys_catg_sim_list__start_date'
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CATS__CP_CONTRACT__START_DATE'
                ]
            ]),
            'end'              => array_replace_recursive(isys_cmdb_dao_category_pattern::date(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS_CP_CONTRACT__END_DATE',
                    C__PROPERTY__INFO__DESCRIPTION => 'LC__CMDB__CATS_CP_CONTRACT__END_DATE'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD => 'isys_catg_sim_list__end_date'
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CATS__CP_CONTRACT__END_DATE'
                ]
            ]),
            'threshold_date'   => array_replace_recursive(isys_cmdb_dao_category_pattern::date(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS_CP_CONTRACT__THRESHOLD',
                    C__PROPERTY__INFO__DESCRIPTION => 'LC__CMDB__CATS_CP_CONTRACT__THRESHOLD'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD => 'isys_catg_sim_list__threshold_date'
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CATS__CP_CONTRACT__THRESHOLD'
                ]
            ]),
            'phone_no'         => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS_CP_CONTRACT__PHONE_NUMBER',
                    C__PROPERTY__INFO__DESCRIPTION => 'LC__CMDB__CATS_CP_CONTRACT__PHONE_NUMBER'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD => 'isys_catg_sim_list__phone_number'
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CATS__CP_CONTRACT__PHONE_NUMBER'
                ]
            ]),
            'client_no'        => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS_CP_CONTRACT__CLIENT_NUMBER',
                    C__PROPERTY__INFO__DESCRIPTION => 'LC__CMDB__CATS_CP_CONTRACT__CLIENT_NUMBER'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD => 'isys_catg_sim_list__client_number'
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CATS__CP_CONTRACT__CLIENT_NUMBER'
                ]
            ]),
            'description'      => array_replace_recursive(isys_cmdb_dao_category_pattern::commentary(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__LOGBOOK__DESCRIPTION',
                    C__PROPERTY__INFO__DESCRIPTION => 'LC__CMDB__LOGBOOK__DESCRIPTION'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD => 'isys_catg_sim_list__description'
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_GLOBAL . defined_or_default('C__CATG__SIM', 'C__CATG__SIM')
                ]
            ])
        ];
    }
}

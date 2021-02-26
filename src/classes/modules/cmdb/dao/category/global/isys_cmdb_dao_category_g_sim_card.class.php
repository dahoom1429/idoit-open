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
 * @author      Dennis Stücken <dstuecken@synetics.de>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_category_g_sim_card extends isys_cmdb_dao_category_global
{
    /**
     * Category's name. Will be used for the identifier, constant, main table,
     * and many more.
     *
     * @var string
     */
    protected $m_category = 'sim_card';

    /**
     * Category entry is purgable
     *
     * @var bool
     */
    protected $m_is_purgable = true;

    /**
     * Dynamic property handling for retrieving the object ID.
     *
     * @param   array $row
     *
     * @return  string
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function dynamic_property_callback_assigned_mobile(array $row)
    {
        $return = '';

        $dao = self::instance(isys_application::instance()->container->get('database'));

        $entryRow = $dao->get_data(null, $row['isys_obj__id'])->get_row();

        if ($entryRow !== false && $entryRow['isys_catg_assigned_cards_list__isys_obj__id'] > 0) {
            $cellphoneData = $dao->get_object_by_id($entryRow['isys_catg_assigned_cards_list__isys_obj__id'])->get_row();

            $return = (new isys_ajax_handler_quick_info())->get_quick_info(
                $cellphoneData['isys_obj__id'],
                isys_application::instance()->container->get('language')->get($cellphoneData['isys_obj_type__title']) . ' &raquo; ' . $cellphoneData['isys_obj__title'],
                C__LINK__OBJECT
            );
        }

        return $return;
    }

    /**
     * @param  array $categoryData
     *
     * @return mixed
     * @throws isys_exception_dao
     */
    public function create_data($categoryData)
    {
        // Remember the assigned mobile and unset it, since the generic logic will produce an SQL error.
        $assignedMobile = $categoryData['assigned_mobile'];

        unset($categoryData['assigned_mobile']);

        $returnValue = parent::create_data($categoryData);

        if (is_numeric($returnValue)) {
            // After saving the data, proceed with the assigned mobile.
            $assignedCardsDao = isys_cmdb_dao_category_g_assigned_cards::instance($this->get_database_component());

            $objectId = $this->get_data($returnValue)->get_row_value('isys_catg_sim_card_list__isys_obj__id');

            if ($objectId > 0) {
                $assignedCardsDao->remove_component(null, $objectId);

                if ($assignedMobile > 0) {
                    $assignedCardsDao->add_component($assignedMobile, $objectId);
                }
            }
        }

        return $returnValue;
    }

    /**
     * @param  int   $categoryEntryId
     * @param  array $categoryData
     *
     * @return bool
     * @throws isys_exception_dao
     * @throws isys_exception_dao_cmdb
     */
    public function save_data($categoryEntryId, $categoryData)
    {
        // Remember the assigned mobile and unset it, since the generic logic will produce an SQL error.
        $assignedMobile = $categoryData['assigned_mobile'];

        unset($categoryData['assigned_mobile']);

        $returnValue = parent::save_data($categoryEntryId, $categoryData);

        // After saving the data, proceed with the assigned mobile.
        $assignedCardsDao = isys_cmdb_dao_category_g_assigned_cards::instance($this->get_database_component());

        $objectId = $this->get_data($categoryEntryId)->get_row_value('isys_catg_sim_card_list__isys_obj__id');

        if ($objectId > 0) {
            $assignedCardsDao->remove_component(null, $objectId);

            if ($assignedMobile > 0) {
                $assignedCardsDao->add_component($assignedMobile, $objectId);
            }
        }

        return $returnValue;
    }


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
        $sql = 'SELECT * FROM isys_catg_sim_card_list 
            LEFT OUTER JOIN isys_cp_contract_type ON isys_catg_sim_card_list__isys_cp_contract_type__id = isys_cp_contract_type__id 
            LEFT OUTER JOIN isys_network_provider ON isys_catg_sim_card_list__isys_network_provider__id = isys_network_provider__id 
            LEFT OUTER JOIN isys_telephone_rate ON isys_catg_sim_card_list__isys_telephone_rate__id = isys_telephone_rate__id 
            LEFT OUTER JOIN isys_catg_assigned_cards_list ON isys_catg_assigned_cards_list__isys_obj__id__card = isys_catg_sim_card_list__isys_obj__id 
            INNER JOIN isys_obj ON isys_obj__id = isys_catg_sim_card_list__isys_obj__id 
            WHERE TRUE ' . $p_condition . ' ' . $this->prepare_filter($p_filter);

        if ($p_obj_id !== null) {
            $sql .= $this->get_object_condition($p_obj_id);
        }

        if ($p_catg_list_id !== null) {
            $sql .= ' AND isys_catg_sim_card_list__id = ' . $this->convert_sql_id($p_catg_list_id);
        }

        if ($p_status !== null) {
            $sql .= ' AND isys_catg_sim_card_list__status = ' . $this->convert_sql_int($p_status);
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
                'isys_catg_sim_card_list__isys_cp_contract_type__id',
                'isys_catg_sim_card_list',
                'isys_cp_contract_type'
            ),
            'assigned_mobile'  => array_replace_recursive(isys_cmdb_dao_category_pattern::object_browser(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__SIM_CARD__ASSIGNED_MOBILE_PHONE',
                    C__PROPERTY__INFO__DESCRIPTION => 'Assigned mobile phone'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD       => 'isys_catg_assigned_cards_list__isys_obj__id',
                    C__PROPERTY__DATA__TABLE_ALIAS => 'isys_catg_assigned_cards_list',
                    C__PROPERTY__DATA__SELECT      => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT CONCAT(isys_obj__title, \' {\', isys_obj__id, \'}\')
                            FROM isys_catg_assigned_cards_list
                            INNER JOIN isys_obj ON isys_obj__id = isys_catg_assigned_cards_list__isys_obj__id',
                        'isys_catg_assigned_cards_list',
                        'isys_catg_assigned_cards_list__id',
                        'isys_catg_assigned_cards_list__isys_obj__id__card',
                        '',
                        '',
                        null,
                        null,
                        '',
                        1
                    )
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CATS__SIM_CARD__ASSIGNED_MOBILE_PHONE',
                    C__PROPERTY__UI__PARAMS => [
                        isys_popup_browser_object_ng::C__CAT_FILTER => 'C__CATG__ASSIGNED_CARDS'
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__LIST    => true,
                    C__PROPERTY__PROVIDES__VIRTUAL => true,
                    C__PROPERTY__PROVIDES__REPORT  => true
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'object'
                    ]
                ]
            ]),
            'network_provider' => new DialogPlusProperty(
                'C__CATS__CP_CONTRACT__NETWORK_PROVIDER',
                'LC__CMDB__CATS_CP_CONTRACT__NETWORK_PROVIDER',
                'isys_catg_sim_card_list__isys_network_provider__id',
                'isys_catg_sim_card_list',
                'isys_network_provider'
            ),
            'telephone_rate' => new DialogPlusProperty(
                'C__CATS__CP_CONTRACT__TELEPHONE_RATE',
                'LC__CMDB__CATS_CP_CONTRACT__TELEPHONE_RATE',
                'isys_catg_sim_card_list__isys_telephone_rate__id',
                'isys_catg_sim_card_list',
                'isys_telephone_rate'
            ),
            'start'            => array_replace_recursive(isys_cmdb_dao_category_pattern::date(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS_CP_CONTRACT__START_DATE',
                    C__PROPERTY__INFO__DESCRIPTION => 'LC__CMDB__CATS_CP_CONTRACT__START_DATE'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD => 'isys_catg_sim_card_list__start_date'
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
                    C__PROPERTY__DATA__FIELD => 'isys_catg_sim_card_list__end_date'
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
                    C__PROPERTY__DATA__FIELD => 'isys_catg_sim_card_list__threshold_date'
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CATS__CP_CONTRACT__THRESHOLD'
                ]
            ]),
            'card_no'          => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS_CP_CONTRACT__CARD_NUMBER',
                    C__PROPERTY__INFO__DESCRIPTION => 'LC__CMDB__CATS_CP_CONTRACT__CARD_NUMBER'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD => 'isys_catg_sim_card_list__card_number'
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CATS__CP_CONTRACT__CARD_NUMBER'
                ]
            ]),
            'phone_no'         => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS_CP_CONTRACT__PHONE_NUMBER',
                    C__PROPERTY__INFO__DESCRIPTION => 'LC__CMDB__CATS_CP_CONTRACT__PHONE_NUMBER'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD => 'isys_catg_sim_card_list__phone_number'
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
                    C__PROPERTY__DATA__FIELD => 'isys_catg_sim_card_list__client_number'
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CATS__CP_CONTRACT__CLIENT_NUMBER'
                ]
            ]),
            'pin'              => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS_CP_CONTRACT__PIN',
                    C__PROPERTY__INFO__DESCRIPTION => 'LC__CMDB__CATS_CP_CONTRACT__PIN'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD => 'isys_catg_sim_card_list__pin'
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CATS__CP_CONTRACT__PIN'
                ]
            ]),
            'pin2'             => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS_CP_CONTRACT__PIN2',
                    C__PROPERTY__INFO__DESCRIPTION => 'LC__CMDB__CATS_CP_CONTRACT__PIN2'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD => 'isys_catg_sim_card_list__pin2'
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CATS__CP_CONTRACT__PIN2'
                ]
            ]),
            'puk'              => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS_CP_CONTRACT__PUK',
                    C__PROPERTY__INFO__DESCRIPTION => 'LC__CMDB__CATS_CP_CONTRACT__PUK'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD => 'isys_catg_sim_card_list__puk'
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CATS__CP_CONTRACT__PUK'
                ]
            ]),
            'puk2'             => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS_CP_CONTRACT__PUK2',
                    C__PROPERTY__INFO__DESCRIPTION => 'LC__CMDB__CATS_CP_CONTRACT__PUK2'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD => 'isys_catg_sim_card_list__puk2'
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CATS__CP_CONTRACT__PUK2'
                ]
            ]),
            'serial'           => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__SERIAL',
                    C__PROPERTY__INFO__DESCRIPTION => 'LC__CMDB__CATG__SERIAL'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD => 'isys_catg_sim_card_list__serial_number'
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CATS__CP_CONTRACT__SERIAL_NUMBER'
                ]
            ]),
            'twincard' => new DialogYesNoProperty(
                'C__CMDB__CATG__SIM_CARD__TWINCARD',
                'LC__CMDB__CATS_CP_CONTRACT__TWINCARD',
                'isys_catg_sim_card_list__twincard',
                'isys_catg_sim_card_list'
            ),
            'tc_card_no'       => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => isys_application::instance()->container->get('language')
                            ->get('LC__CMDB__CATS_CP_CONTRACT__CARD_NUMBER') . ' (' . isys_application::instance()->container->get('language')
                            ->get('LC__CMDB__CATS_CP_CONTRACT__TWINCARD') . ')',
                    C__PROPERTY__INFO__DESCRIPTION => 'LC__CMDB__CATS_CP_CONTRACT__CARD_NUMBER'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD => 'isys_catg_sim_card_list__tc_card_number'
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CATS__CP_CONTRACT__TC_CARD_NUMBER'
                ]
            ]),
            'tc_phone_no'      => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => isys_application::instance()->container->get('language')
                            ->get('LC__CMDB__CATS_CP_CONTRACT__PHONE_NUMBER') . ' (' . isys_application::instance()->container->get('language')
                            ->get('LC__CMDB__CATS_CP_CONTRACT__TWINCARD') . ')',
                    C__PROPERTY__INFO__DESCRIPTION => 'LC__CMDB__CATS_CP_CONTRACT__PHONE_NUMBER'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD => 'isys_catg_sim_card_list__tc_phone_number'
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CATS__CP_CONTRACT__TC_PHONE_NUMBER'
                ]
            ]),
            'tc_pin'           => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => isys_application::instance()->container->get('language')
                            ->get('LC__CMDB__CATS_CP_CONTRACT__PIN') . ' (' . isys_application::instance()->container->get('language')
                            ->get('LC__CMDB__CATS_CP_CONTRACT__TWINCARD') . ')',
                    C__PROPERTY__INFO__DESCRIPTION => 'LC__CMDB__CATS_CP_CONTRACT__PIN'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD => 'isys_catg_sim_card_list__tc_pin'
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CATS__CP_CONTRACT__TC_PIN'
                ]
            ]),
            'tc_pin2'          => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => isys_application::instance()->container->get('language')
                            ->get('LC__CMDB__CATS_CP_CONTRACT__PIN2') . ' (' . isys_application::instance()->container->get('language')
                            ->get('LC__CMDB__CATS_CP_CONTRACT__TWINCARD') . ')',
                    C__PROPERTY__INFO__DESCRIPTION => 'LC__CMDB__CATS_CP_CONTRACT__PIN2'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD => 'isys_catg_sim_card_list__tc_pin2'
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CATS__CP_CONTRACT__TC_PIN2'
                ]
            ]),
            'tc_puk'           => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => isys_application::instance()->container->get('language')
                            ->get('LC__CMDB__CATS_CP_CONTRACT__PUK') . ' (' . isys_application::instance()->container->get('language')
                            ->get('LC__CMDB__CATS_CP_CONTRACT__TWINCARD') . ')',
                    C__PROPERTY__INFO__DESCRIPTION => 'LC__CMDB__CATS_CP_CONTRACT__PUK'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD => 'isys_catg_sim_card_list__tc_puk'
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CATS__CP_CONTRACT__TC_PUK'
                ]
            ]),
            'tc_puk2'          => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => isys_application::instance()->container->get('language')
                            ->get('LC__CMDB__CATS_CP_CONTRACT__PUK2') . ' (' . isys_application::instance()->container->get('language')
                            ->get('LC__CMDB__CATS_CP_CONTRACT__TWINCARD') . ')',
                    C__PROPERTY__INFO__DESCRIPTION => 'LC__CMDB__CATS_CP_CONTRACT__PUK2'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD => 'isys_catg_sim_card_list__tc_puk2'
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CATS__CP_CONTRACT__TC_PUK2'
                ]
            ]),
            'tc_serial_no'     => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => isys_application::instance()->container->get('language')
                            ->get('LC__CMDB__CATG__SERIAL') . ' (' . isys_application::instance()->container->get('language')
                            ->get('LC__CMDB__CATS_CP_CONTRACT__TWINCARD') . ')',
                    C__PROPERTY__INFO__DESCRIPTION => 'LC__CMDB__CATG__SERIAL'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD => 'isys_catg_sim_card_list__tc_serial_number'
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CATS__CP_CONTRACT__TC_SERIAL_NUMBER'
                ]
            ]),
            'optional_info'    => array_replace_recursive(isys_cmdb_dao_category_pattern::textarea(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => isys_application::instance()->container->get('language')
                            ->get('LC__CMDB__CATS_CP_CONTRACT__OPTIONAL_INFO') . ' (' . isys_application::instance()->container->get('language')
                            ->get('LC__CMDB__CATS_CP_CONTRACT__TWINCARD') . ')',
                    C__PROPERTY__INFO__DESCRIPTION => 'LC__CMDB__CATS_CP_CONTRACT__OPTIONAL_INFO'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD => 'isys_catg_sim_card_list__optional_info'
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CATS__CP_CONTRACT__TC_DESCRIPTION'
                ]
            ]),
            'description'      => array_replace_recursive(isys_cmdb_dao_category_pattern::commentary(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__LOGBOOK__DESCRIPTION',
                    C__PROPERTY__INFO__DESCRIPTION => 'LC__CMDB__LOGBOOK__DESCRIPTION'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD => 'isys_catg_sim_card_list__description'
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_GLOBAL . defined_or_default('C__CATG__SIM_CARD', 'C__CATG__SIM_CARD')
                ]
            ])
        ];
    }

    public function rank_record($p_object_id, $p_direction, $p_table, $p_checkMethod = null, $p_purge = false)
    {
        if ($p_purge) {
            $l_dao_relation = isys_cmdb_dao_category_g_relation::instance($this->get_database_component());

            $l_sql = 'SELECT  isys_catg_assigned_cards_list__isys_catg_relation_list__id FROM isys_catg_assigned_cards_list ' .
                'INNER JOIN  isys_catg_sim_card_list ON isys_catg_assigned_cards_list__isys_obj__id__card =  isys_catg_sim_card_list__isys_obj__id ' .
                'WHERE isys_catg_sim_card_list__id = ' . $this->convert_sql_id($p_object_id);

            $l_relation_id = $this->retrieve($l_sql)->get_row_value('isys_catg_assigned_cards_list__isys_catg_relation_list__id');

            // Delete relation.
            if ($l_relation_id > 0) {
                $l_dao_relation->delete_relation($l_relation_id);
            }
        }

        return parent::rank_record($p_object_id, $p_direction, $p_table, $p_checkMethod, $p_purge);
    }
}

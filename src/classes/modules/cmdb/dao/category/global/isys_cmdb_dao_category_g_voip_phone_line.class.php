<?php
use idoit\Module\Report\SqlQuery\Structure\SelectSubSelect;
use idoit\Module\Report\SqlQuery\Structure\SelectCondition;
use idoit\Module\Report\SqlQuery\Structure\SelectGroupBy;
use idoit\Module\Report\SqlQuery\Structure\SelectJoin;
use idoit\Component\Property\Type\DialogListObjectDataProperty;
use idoit\Component\Property\Property;

/**
 * i-doit
 *
 * DAO: global category for voice over IP phone lines.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 * @author      Leonard Fischer <lfischer@i-doit.org>
 * @since       1.0
 */
class isys_cmdb_dao_category_g_voip_phone_line extends isys_cmdb_dao_category_global
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'voip_phone_line';

    /**
     * @var string
     */
    protected $m_entry_identifier = 'line_text_label';

    /**
     * Is category multi-valued or single-valued?
     *
     * @var  boolean
     */
    protected $m_multivalued = true;

    /**
     * Callback method for the associated phones.
     *
     * @param   isys_request $p_request
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function callback_property_associated_devices(isys_request $p_request)
    {
        $l_return = [];
        $l_assigned_objects = [];
        $l_cat_id = $p_request->get_category_data_id();

        $l_query = 'SELECT isys_obj__id FROM isys_catg_voip_phone_line_2_isys_obj
			WHERE isys_catg_voip_phone_line__id = ' . $this->convert_sql_id($l_cat_id);
        $l_res = $this->retrieve($l_query);

        while ($l_row = $l_res->get_row()) {
            $l_assigned_objects[] = $l_row['isys_obj__id'];
        }

        $l_query = 'SELECT isys_obj__id, isys_obj__title FROM isys_obj
			WHERE isys_obj__isys_obj_type__id = ' . $this->convert_sql_id(defined_or_default('C__OBJTYPE__VOIP_PHONE')) . '
			AND isys_obj__status = ' . $this->convert_sql_int(C__RECORD_STATUS__NORMAL);
        $l_res = $this->retrieve($l_query);

        while ($l_row = $l_res->get_row()) {
            $l_return[] = [
                "id"   => $l_row['isys_obj__id'],
                "val"  => $l_row['isys_obj__title'],
                "sel"  => in_array($l_row['isys_obj__id'], $l_assigned_objects),
                "link" => ''
            ];
        }

        return $l_return;
    }

    /**
     * Method for returning the properties.
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    protected function properties()
    {
        return [
            'directory_number'              => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__VOIP_PHONE_LINE__DIRECTORY_NUMBER',
                    C__PROPERTY__INFO__DESCRIPTION => 'Directory number'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_voip_phone_line_list__directory_number',
                    C__PROPERTY__DATA__SELECT => SelectSubSelect::factory(
                        'SELECT isys_catg_voip_phone_line_list__directory_number FROM isys_catg_voip_phone_line_list',
                        'isys_catg_voip_phone_line_list',
                        'isys_catg_voip_phone_line_list__id',
                        'isys_catg_voip_phone_line_list__isys_obj__id',
                        '',
                        '',
                        null,
                        SelectGroupBy::factory(['isys_catg_voip_phone_line_list__isys_obj__id'])
                    )
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CATG__VOIP_PHONE_LINE__DIRECTORY_NUMBER',
                ]
            ]),
            'route_partition'               => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__VOIP_PHONE_LINE__ROUTE_PARTITION',
                    C__PROPERTY__INFO__DESCRIPTION => 'Route partition'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_voip_phone_line_list__route_partition',
                    C__PROPERTY__DATA__SELECT => SelectSubSelect::factory(
                        'SELECT isys_catg_voip_phone_line_list__route_partition FROM isys_catg_voip_phone_line_list',
                        'isys_catg_voip_phone_line_list',
                        'isys_catg_voip_phone_line_list__id',
                        'isys_catg_voip_phone_line_list__isys_obj__id',
                        '',
                        '',
                        null,
                        SelectGroupBy::factory(['isys_catg_voip_phone_line_list__isys_obj__id'])
                    )
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CATG__VOIP_PHONE_LINE__ROUTE_PARTITION',
                ]
            ]),
            'description2'                  => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__VOIP_PHONE_LINE__DESCRIPTION',
                    C__PROPERTY__INFO__DESCRIPTION => 'Description'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_voip_phone_line_list__description2',
                    C__PROPERTY__DATA__SELECT => SelectSubSelect::factory(
                        'SELECT isys_catg_voip_phone_line_list__description2 FROM isys_catg_voip_phone_line_list',
                        'isys_catg_voip_phone_line_list',
                        'isys_catg_voip_phone_line_list__id',
                        'isys_catg_voip_phone_line_list__isys_obj__id',
                        '',
                        '',
                        null,
                        SelectGroupBy::factory(['isys_catg_voip_phone_line_list__isys_obj__id'])
                    )
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CATG__VOIP_PHONE_LINE__DESCRIPTION',
                ]
            ]),
            'alerting_name'                 => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__VOIP_PHONE_LINE__ALERTING_NAME',
                    C__PROPERTY__INFO__DESCRIPTION => 'Alerting name'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_voip_phone_line_list__alerting_name',
                    C__PROPERTY__DATA__SELECT => SelectSubSelect::factory(
                        'SELECT isys_catg_voip_phone_line_list__alerting_name FROM isys_catg_voip_phone_line_list',
                        'isys_catg_voip_phone_line_list',
                        'isys_catg_voip_phone_line_list__id',
                        'isys_catg_voip_phone_line_list__isys_obj__id',
                        '',
                        '',
                        null,
                        SelectGroupBy::factory(['isys_catg_voip_phone_line_list__isys_obj__id'])
                    )
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CATG__VOIP_PHONE_LINE__ALERTING_NAME',
                ]
            ]),
            'ascii_alerting_name'           => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__VOIP_PHONE_LINE__ASCII_ALERTING_NAME',
                    C__PROPERTY__INFO__DESCRIPTION => 'ASCII alerting name'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_voip_phone_line_list__ascii_alerting_name',
                    C__PROPERTY__DATA__SELECT => SelectSubSelect::factory(
                        'SELECT isys_catg_voip_phone_line_list__ascii_alerting_name FROM isys_catg_voip_phone_line_list',
                        'isys_catg_voip_phone_line_list',
                        'isys_catg_voip_phone_line_list__id',
                        'isys_catg_voip_phone_line_list__isys_obj__id',
                        '',
                        '',
                        null,
                        SelectGroupBy::factory(['isys_catg_voip_phone_line_list__isys_obj__id'])
                    )
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CATG__VOIP_PHONE_LINE__ASCII_ALERTING_NAME',
                ]
            ]),
            'allow_cti_control'             => array_replace_recursive(isys_cmdb_dao_category_pattern::int(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__VOIP_PHONE_LINE__ALLOW_CTI_CONTROL',
                    C__PROPERTY__INFO__DESCRIPTION => 'Allow control of device from CTI'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_voip_phone_line_list__allow_cti_control',
                    C__PROPERTY__DATA__SELECT => SelectSubSelect::factory(
                        'SELECT (CASE WHEN isys_catg_voip_phone_line_list__allow_cti_control = \'1\' THEN ' .
                        $this->convert_sql_text('LC__MODULE__QCW__ACTIVE') . '
                                    WHEN isys_catg_voip_phone_line_list__allow_cti_control = \'0\' THEN ' . $this->convert_sql_text('LC__MODULE__QCW__INACTIVE') . ' END)
                                FROM isys_catg_voip_phone_line_list',
                        'isys_catg_voip_phone_line_list',
                        'isys_catg_voip_phone_line_list__id',
                        'isys_catg_voip_phone_line_list__isys_obj__id',
                        '',
                        '',
                        SelectCondition::factory([]),
                        SelectGroupBy::factory(['isys_catg_voip_phone_line_list__isys_obj__id'])
                    ),
                    C__PROPERTY__DATA__JOIN   => [
                        SelectJoin::factory(
                            'isys_catg_voip_phone_line_list',
                            'LEFT',
                            'isys_catg_voip_phone_line_list__isys_obj__id',
                            'isys_obj__id'
                        )
                    ]
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID   => 'C__CMDB__CATG__VOIP_PHONE_LINE__ALLOW_CTI_CONTROL',
                    C__PROPERTY__UI__TYPE => C__PROPERTY__UI__TYPE__CHECKBOX
                ]
            ]),
            'associated_devices'            => (new DialogListObjectDataProperty(
                'C__CMDB__CATG__VOIP_PHONE_LINE__ASSOCIATED_DEVICES',
                'LC__CMDB__CATG__VOIP_PHONE_LINE__ASSOCIATED_DEVICES',
                'isys_catg_voip_phone_line_list__id',
                'isys_catg_voip_phone_line_list',
                'isys_catg_voip_phone_line_2_isys_obj',
                null,
                'isys_catg_voip_phone_line__id',
                new isys_callback([
                    'isys_cmdb_dao_category_g_voip_phone_line',
                    'callback_property_associated_devices'
                ])
            ))->setPropertyDataOffset(
                Property::C__PROPERTY__DATA__SELECT,
                SelectSubSelect::factory(
                    'SELECT CONCAT(obj.isys_obj__title, \' {\', obj.isys_obj__id, \'}\')
                                FROM isys_catg_voip_phone_line_list
                                INNER JOIN isys_catg_voip_phone_line_2_isys_obj AS v2obj ON v2obj.isys_catg_voip_phone_line__id = isys_catg_voip_phone_line_list__id
                                INNER JOIN isys_obj obj ON obj.isys_obj__id = v2obj.isys_obj__id',
                    'isys_catg_voip_phone_line_list',
                    'isys_catg_voip_phone_line_list__id',
                    'isys_catg_voip_phone_line_list__isys_obj__id',
                    '',
                    '',
                    SelectCondition::factory([]),
                    SelectGroupBy::factory(['isys_catg_voip_phone_line_list__isys_obj__id'])
                )
            )->setPropertyDataOffset(
                Property::C__PROPERTY__DATA__JOIN,
                [
                    SelectJoin::factory(
                        'isys_catg_voip_phone_line_list',
                        'LEFT',
                        'isys_catg_voip_phone_line_list__isys_obj__id',
                        'isys_obj__id'
                    ),
                    SelectJoin::factory(
                        'isys_catg_voip_phone_line_2_isys_obj',
                        'LEFT',
                        'isys_catg_voip_phone_line_list__id',
                        'isys_catg_voip_phone_line__id',
                        '',
                        'v2obj',
                        'v2obj'
                    ),
                    SelectJoin::factory('isys_obj', 'LEFT', 'isys_obj__id', 'isys_obj__id', 'v2obj', 'obj', 'obj')
                ]
            ),
            'voice_mail_profile'            => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__VOIP_PHONE_LINE__VOICE_MAIL_PROFILE',
                    C__PROPERTY__INFO__DESCRIPTION => 'Voice mail profile'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_voip_phone_line_list__voice_mail_profile',
                    C__PROPERTY__DATA__SELECT => SelectSubSelect::factory(
                        'SELECT isys_catg_voip_phone_line_list__voice_mail_profile FROM isys_catg_voip_phone_line_list',
                        'isys_catg_voip_phone_line_list',
                        'isys_catg_voip_phone_line_list__id',
                        'isys_catg_voip_phone_line_list__isys_obj__id',
                        '',
                        '',
                        null,
                        SelectGroupBy::factory(['isys_catg_voip_phone_line_list__isys_obj__id'])
                    )
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CATG__VOIP_PHONE_LINE__VOICE_MAIL_PROFILE',
                ]
            ]),
            'calling_search_space'          => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__VOIP_PHONE_LINE__CALLING_SEARCH_SPACE',
                    C__PROPERTY__INFO__DESCRIPTION => 'Calling search space'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_voip_phone_line_list__calling_search_space',
                    C__PROPERTY__DATA__SELECT => SelectSubSelect::factory(
                        'SELECT isys_catg_voip_phone_line_list__calling_search_space FROM isys_catg_voip_phone_line_list',
                        'isys_catg_voip_phone_line_list',
                        'isys_catg_voip_phone_line_list__id',
                        'isys_catg_voip_phone_line_list__isys_obj__id',
                        '',
                        '',
                        null,
                        SelectGroupBy::factory(['isys_catg_voip_phone_line_list__isys_obj__id'])
                    )
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CATG__VOIP_PHONE_LINE__CALLING_SEARCH_SPACE',
                ]
            ]),
            'presence_group'                => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__VOIP_PHONE_LINE__PRESENCE_GROUP',
                    C__PROPERTY__INFO__DESCRIPTION => 'Presence group'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_voip_phone_line_list__presence_group',
                    C__PROPERTY__DATA__SELECT => SelectSubSelect::factory(
                        'SELECT isys_catg_voip_phone_line_list__presence_group FROM isys_catg_voip_phone_line_list',
                        'isys_catg_voip_phone_line_list',
                        'isys_catg_voip_phone_line_list__id',
                        'isys_catg_voip_phone_line_list__isys_obj__id',
                        '',
                        '',
                        null,
                        SelectGroupBy::factory(['isys_catg_voip_phone_line_list__isys_obj__id'])
                    )
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CATG__VOIP_PHONE_LINE__PRESENCE_GROUP',
                ]
            ]),
            'user_hold_moh_audio_source'    => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__VOIP_PHONE_LINE__USER_HOLD_MOH_AUDIO_SOURCE',
                    C__PROPERTY__INFO__DESCRIPTION => 'User hold MOH audio source'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_voip_phone_line_list__user_hold_moh_audio_source',
                    C__PROPERTY__DATA__SELECT => SelectSubSelect::factory(
                        'SELECT isys_catg_voip_phone_line_list__user_hold_moh_audio_source FROM isys_catg_voip_phone_line_list',
                        'isys_catg_voip_phone_line_list',
                        'isys_catg_voip_phone_line_list__id',
                        'isys_catg_voip_phone_line_list__isys_obj__id',
                        '',
                        '',
                        null,
                        SelectGroupBy::factory(['isys_catg_voip_phone_line_list__isys_obj__id'])
                    )
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CATG__VOIP_PHONE_LINE__USER_HOLD_MOH_AUDIO_SOURCE',
                ]
            ]),
            'network_hold_moh_audio_source' => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__VOIP_PHONE_LINE__NETWORK_HOLD_MOH_AUDIO_SOURCE',
                    C__PROPERTY__INFO__DESCRIPTION => 'Network hold MOH audio source'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_voip_phone_line_list__network_hold_moh_audio_source',
                    C__PROPERTY__DATA__SELECT => SelectSubSelect::factory(
                        'SELECT isys_catg_voip_phone_line_list__directory_number FROM isys_catg_voip_phone_line_list',
                        'isys_catg_voip_phone_line_list',
                        'isys_catg_voip_phone_line_list__id',
                        'isys_catg_voip_phone_line_list__isys_obj__id',
                        '',
                        '',
                        null,
                        SelectGroupBy::factory(['isys_catg_voip_phone_line_list__isys_obj__id'])
                    )
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CATG__VOIP_PHONE_LINE__NETWORK_HOLD_MOH_AUDIO_SOURCE',
                ]
            ]),
            'auto_answer'                   => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__VOIP_PHONE_LINE__AUTO_ANSWER',
                    C__PROPERTY__INFO__DESCRIPTION => 'Auto answer'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_voip_phone_line_list__auto_answer',
                    C__PROPERTY__DATA__SELECT => SelectSubSelect::factory(
                        'SELECT isys_catg_voip_phone_line_list__auto_answer FROM isys_catg_voip_phone_line_list',
                        'isys_catg_voip_phone_line_list',
                        'isys_catg_voip_phone_line_list__id',
                        'isys_catg_voip_phone_line_list__isys_obj__id',
                        '',
                        '',
                        null,
                        SelectGroupBy::factory(['isys_catg_voip_phone_line_list__isys_obj__id'])
                    )
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CATG__VOIP_PHONE_LINE__AUTO_ANSWER',
                ]
            ]),
            'call_forward_all'              => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__VOIP_PHONE_LINE__CALL_FORWARD_ALL',
                    C__PROPERTY__INFO__DESCRIPTION => 'Call forward all'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_voip_phone_line_list__call_forward_all',
                    C__PROPERTY__DATA__SELECT => SelectSubSelect::factory(
                        'SELECT isys_catg_voip_phone_line_list__call_forward_all FROM isys_catg_voip_phone_line_list',
                        'isys_catg_voip_phone_line_list',
                        'isys_catg_voip_phone_line_list__id',
                        'isys_catg_voip_phone_line_list__isys_obj__id',
                        '',
                        '',
                        null,
                        SelectGroupBy::factory(['isys_catg_voip_phone_line_list__isys_obj__id'])
                    )
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CATG__VOIP_PHONE_LINE__CALL_FORWARD_ALL',
                ]
            ]),
            'sec_calling_search_space'      => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__VOIP_PHONE_LINE__SEC_CALLING_SEARCH_SPACE',
                    C__PROPERTY__INFO__DESCRIPTION => 'Secondary Calling Search Space for Forward All'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_voip_phone_line_list__sec_calling_search_space',
                    C__PROPERTY__DATA__SELECT => SelectSubSelect::factory(
                        'SELECT isys_catg_voip_phone_line_list__sec_calling_search_space FROM isys_catg_voip_phone_line_list',
                        'isys_catg_voip_phone_line_list',
                        'isys_catg_voip_phone_line_list__id',
                        'isys_catg_voip_phone_line_list__isys_obj__id',
                        '',
                        '',
                        null,
                        SelectGroupBy::factory(['isys_catg_voip_phone_line_list__isys_obj__id'])
                    )
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CATG__VOIP_PHONE_LINE__SEC_CALLING_SEARCH_SPACE',
                ]
            ]),
            'forward_busy_internal'         => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__VOIP_PHONE_LINE__FORWARD_BUSY_INTERNAL',
                    C__PROPERTY__INFO__DESCRIPTION => 'Forward busy internal'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_voip_phone_line_list__forward_busy_internal',
                    C__PROPERTY__DATA__SELECT => SelectSubSelect::factory(
                        'SELECT isys_catg_voip_phone_line_list__forward_busy_internal FROM isys_catg_voip_phone_line_list',
                        'isys_catg_voip_phone_line_list',
                        'isys_catg_voip_phone_line_list__id',
                        'isys_catg_voip_phone_line_list__isys_obj__id',
                        '',
                        '',
                        null,
                        SelectGroupBy::factory(['isys_catg_voip_phone_line_list__isys_obj__id'])
                    )
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CATG__VOIP_PHONE_LINE__FORWARD_BUSY_INTERNAL',
                ]
            ]),
            'forward_busy_external'         => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__VOIP_PHONE_LINE__FORWARD_BUSY_EXTERNAL',
                    C__PROPERTY__INFO__DESCRIPTION => 'Forward busy external'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_voip_phone_line_list__forward_busy_external',
                    C__PROPERTY__DATA__SELECT => SelectSubSelect::factory(
                        'SELECT isys_catg_voip_phone_line_list__forward_busy_external FROM isys_catg_voip_phone_line_list',
                        'isys_catg_voip_phone_line_list',
                        'isys_catg_voip_phone_line_list__id',
                        'isys_catg_voip_phone_line_list__isys_obj__id',
                        '',
                        '',
                        null,
                        SelectGroupBy::factory(['isys_catg_voip_phone_line_list__isys_obj__id'])
                    )
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CATG__VOIP_PHONE_LINE__FORWARD_BUSY_EXTERNAL',
                ]
            ]),
            'forward_no_answer_internal'    => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__VOIP_PHONE_LINE__FORWARD_NO_ANSWER_INTERNAL',
                    C__PROPERTY__INFO__DESCRIPTION => 'Forward no answer internal'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_voip_phone_line_list__forward_no_answer_internal',
                    C__PROPERTY__DATA__SELECT => SelectSubSelect::factory(
                        'SELECT isys_catg_voip_phone_line_list__forward_no_answer_internal FROM isys_catg_voip_phone_line_list',
                        'isys_catg_voip_phone_line_list',
                        'isys_catg_voip_phone_line_list__id',
                        'isys_catg_voip_phone_line_list__isys_obj__id',
                        '',
                        '',
                        null,
                        SelectGroupBy::factory(['isys_catg_voip_phone_line_list__isys_obj__id'])
                    )
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CATG__VOIP_PHONE_LINE__FORWARD_NO_ANSWER_INTERNAL',
                ]
            ]),
            'forward_no_answer_external'    => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__VOIP_PHONE_LINE__FORWARD_NO_ANSWER_EXTERNAL',
                    C__PROPERTY__INFO__DESCRIPTION => 'Forward no answer external'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_voip_phone_line_list__forward_no_answer_external',
                    C__PROPERTY__DATA__SELECT => SelectSubSelect::factory(
                        'SELECT isys_catg_voip_phone_line_list__forward_no_answer_external FROM isys_catg_voip_phone_line_list',
                        'isys_catg_voip_phone_line_list',
                        'isys_catg_voip_phone_line_list__id',
                        'isys_catg_voip_phone_line_list__isys_obj__id',
                        '',
                        '',
                        null,
                        SelectGroupBy::factory(['isys_catg_voip_phone_line_list__isys_obj__id'])
                    )
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CATG__VOIP_PHONE_LINE__FORWARD_NO_ANSWER_EXTERNAL',
                ]
            ]),
            'forward_no_coverage_internal'  => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__VOIP_PHONE_LINE__FORWARD_NO_COVERAGE_INTERNAL',
                    C__PROPERTY__INFO__DESCRIPTION => 'Forward no coverage internal'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_voip_phone_line_list__forward_no_coverage_internal',
                    C__PROPERTY__DATA__SELECT => SelectSubSelect::factory(
                        'SELECT isys_catg_voip_phone_line_list__forward_no_coverage_internal FROM isys_catg_voip_phone_line_list',
                        'isys_catg_voip_phone_line_list',
                        'isys_catg_voip_phone_line_list__id',
                        'isys_catg_voip_phone_line_list__isys_obj__id',
                        '',
                        '',
                        null,
                        SelectGroupBy::factory(['isys_catg_voip_phone_line_list__isys_obj__id'])
                    )
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CATG__VOIP_PHONE_LINE__FORWARD_NO_COVERAGE_INTERNAL',
                ]
            ]),
            'forward_no_coverage_external'  => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__VOIP_PHONE_LINE__FORWARD_NO_COVERAGE_EXTERNAL',
                    C__PROPERTY__INFO__DESCRIPTION => 'Forward no coverage external'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_voip_phone_line_list__forward_no_coverage_external',
                    C__PROPERTY__DATA__SELECT => SelectSubSelect::factory(
                        'SELECT isys_catg_voip_phone_line_list__forward_no_coverage_external FROM isys_catg_voip_phone_line_list',
                        'isys_catg_voip_phone_line_list',
                        'isys_catg_voip_phone_line_list__id',
                        'isys_catg_voip_phone_line_list__isys_obj__id',
                        '',
                        '',
                        null,
                        SelectGroupBy::factory(['isys_catg_voip_phone_line_list__isys_obj__id'])
                    )
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CATG__VOIP_PHONE_LINE__FORWARD_NO_COVERAGE_EXTERNAL',
                ]
            ]),
            'forward_on_cti_fail'           => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__VOIP_PHONE_LINE__FORWARD_ON_CTI_FAIL',
                    C__PROPERTY__INFO__DESCRIPTION => 'Forward on CTI fail'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_voip_phone_line_list__forward_on_cti_fail',
                    C__PROPERTY__DATA__SELECT => SelectSubSelect::factory(
                        'SELECT isys_catg_voip_phone_line_list__forward_on_cti_fail FROM isys_catg_voip_phone_line_list',
                        'isys_catg_voip_phone_line_list',
                        'isys_catg_voip_phone_line_list__id',
                        'isys_catg_voip_phone_line_list__isys_obj__id',
                        '',
                        '',
                        null,
                        SelectGroupBy::factory(['isys_catg_voip_phone_line_list__isys_obj__id'])
                    )
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CATG__VOIP_PHONE_LINE__FORWARD_ON_CTI_FAIL',
                ]
            ]),
            'forward_unregistered_internal' => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__VOIP_PHONE_LINE__FORWARD_UNREGISTERED_INTERNAL',
                    C__PROPERTY__INFO__DESCRIPTION => 'Forward unregistered internal'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_voip_phone_line_list__forward_unregistered_internal',
                    C__PROPERTY__DATA__SELECT => SelectSubSelect::factory(
                        'SELECT isys_catg_voip_phone_line_list__forward_unregistered_internal FROM isys_catg_voip_phone_line_list',
                        'isys_catg_voip_phone_line_list',
                        'isys_catg_voip_phone_line_list__id',
                        'isys_catg_voip_phone_line_list__isys_obj__id',
                        '',
                        '',
                        null,
                        SelectGroupBy::factory(['isys_catg_voip_phone_line_list__isys_obj__id'])
                    )
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CATG__VOIP_PHONE_LINE__FORWARD_UNREGISTERED_INTERNAL',
                ]
            ]),
            'forward_unregistered_external' => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__VOIP_PHONE_LINE__FORWARD_UNREGISTERED_EXTERNAL',
                    C__PROPERTY__INFO__DESCRIPTION => 'Forward unregistered external'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_voip_phone_line_list__forward_unregistered_external',
                    C__PROPERTY__DATA__SELECT => SelectSubSelect::factory(
                        'SELECT isys_catg_voip_phone_line_list__forward_unregistered_external FROM isys_catg_voip_phone_line_list',
                        'isys_catg_voip_phone_line_list',
                        'isys_catg_voip_phone_line_list__id',
                        'isys_catg_voip_phone_line_list__isys_obj__id',
                        '',
                        '',
                        null,
                        SelectGroupBy::factory(['isys_catg_voip_phone_line_list__isys_obj__id'])
                    )
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CATG__VOIP_PHONE_LINE__FORWARD_UNREGISTERED_EXTERNAL',
                ]
            ]),
            'no_answer_ring_duration'       => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__VOIP_PHONE_LINE__NO_ANSWER_RING_DURATION',
                    C__PROPERTY__INFO__DESCRIPTION => 'No answer ring duration'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_voip_phone_line_list__no_answer_ring_duration',
                    C__PROPERTY__DATA__SELECT => SelectSubSelect::factory(
                        'SELECT isys_catg_voip_phone_line_list__no_answer_ring_duration FROM isys_catg_voip_phone_line_list',
                        'isys_catg_voip_phone_line_list',
                        'isys_catg_voip_phone_line_list__id',
                        'isys_catg_voip_phone_line_list__isys_obj__id',
                        '',
                        '',
                        null,
                        SelectGroupBy::factory(['isys_catg_voip_phone_line_list__isys_obj__id'])
                    )
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CATG__VOIP_PHONE_LINE__NO_ANSWER_RING_DURATION',
                ]
            ]),
            'call_pickup_group'             => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__VOIP_PHONE_LINE__CALL_PICKUP_GROUP',
                    C__PROPERTY__INFO__DESCRIPTION => 'Call pickup group'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_voip_phone_line_list__call_pickup_group',
                    C__PROPERTY__DATA__SELECT => SelectSubSelect::factory(
                        'SELECT isys_catg_voip_phone_line_list__call_pickup_group FROM isys_catg_voip_phone_line_list',
                        'isys_catg_voip_phone_line_list',
                        'isys_catg_voip_phone_line_list__id',
                        'isys_catg_voip_phone_line_list__isys_obj__id',
                        '',
                        '',
                        null,
                        SelectGroupBy::factory(['isys_catg_voip_phone_line_list__isys_obj__id'])
                    )
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CATG__VOIP_PHONE_LINE__CALL_PICKUP_GROUP',
                ]
            ]),
            'display'                       => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__VOIP_PHONE_LINE__DISPLAY',
                    C__PROPERTY__INFO__DESCRIPTION => 'Display'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_voip_phone_line_list__display',
                    C__PROPERTY__DATA__SELECT => SelectSubSelect::factory(
                        'SELECT isys_catg_voip_phone_line_list__display FROM isys_catg_voip_phone_line_list',
                        'isys_catg_voip_phone_line_list',
                        'isys_catg_voip_phone_line_list__id',
                        'isys_catg_voip_phone_line_list__isys_obj__id',
                        '',
                        '',
                        null,
                        SelectGroupBy::factory(['isys_catg_voip_phone_line_list__isys_obj__id'])
                    )
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CATG__VOIP_PHONE_LINE__DISPLAY',
                ]
            ]),
            'ascii_display'                 => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__VOIP_PHONE_LINE__ASCII_DISPLAY',
                    C__PROPERTY__INFO__DESCRIPTION => 'ASCII Display'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_voip_phone_line_list__ascii_display',
                    C__PROPERTY__DATA__SELECT => SelectSubSelect::factory(
                        'SELECT isys_catg_voip_phone_line_list__ascii_display FROM isys_catg_voip_phone_line_list',
                        'isys_catg_voip_phone_line_list',
                        'isys_catg_voip_phone_line_list__id',
                        'isys_catg_voip_phone_line_list__isys_obj__id',
                        '',
                        '',
                        null,
                        SelectGroupBy::factory(['isys_catg_voip_phone_line_list__isys_obj__id'])
                    )
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CATG__VOIP_PHONE_LINE__ASCII_DISPLAY',
                ]
            ]),
            'line_text_label'               => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__VOIP_PHONE_LINE__LINE_TEXT_LABEL',
                    C__PROPERTY__INFO__DESCRIPTION => 'Line text label'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_voip_phone_line_list__line_text_label',
                    C__PROPERTY__DATA__SELECT => SelectSubSelect::factory(
                        'SELECT isys_catg_voip_phone_line_list__line_text_label FROM isys_catg_voip_phone_line_list',
                        'isys_catg_voip_phone_line_list',
                        'isys_catg_voip_phone_line_list__id',
                        'isys_catg_voip_phone_line_list__isys_obj__id',
                        '',
                        '',
                        null,
                        SelectGroupBy::factory(['isys_catg_voip_phone_line_list__isys_obj__id'])
                    )
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CATG__VOIP_PHONE_LINE__LINE_TEXT_LABEL',
                ]
            ]),
            'ascii_line_text_label'         => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__VOIP_PHONE_LINE__ASCII_LINE_TEXT_LABEL',
                    C__PROPERTY__INFO__DESCRIPTION => 'ASCII Line text label'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_voip_phone_line_list__ascii_line_text_label',
                    C__PROPERTY__DATA__SELECT => SelectSubSelect::factory(
                        'SELECT isys_catg_voip_phone_line_list__ascii_line_text_label FROM isys_catg_voip_phone_line_list',
                        'isys_catg_voip_phone_line_list',
                        'isys_catg_voip_phone_line_list__id',
                        'isys_catg_voip_phone_line_list__isys_obj__id',
                        '',
                        '',
                        null,
                        SelectGroupBy::factory(['isys_catg_voip_phone_line_list__isys_obj__id'])
                    )
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CATG__VOIP_PHONE_LINE__ASCII_LINE_TEXT_LABEL',
                ]
            ]),
            'visual_message_indicator'      => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__VOIP_PHONE_LINE__VISUAL_MESSAGE_INDICATOR',
                    C__PROPERTY__INFO__DESCRIPTION => 'Visual Message Waiting Indicator Policy'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_voip_phone_line_list__visual_message_indicator',
                    C__PROPERTY__DATA__SELECT => SelectSubSelect::factory(
                        'SELECT isys_catg_voip_phone_line_list__visual_message_indicator FROM isys_catg_voip_phone_line_list',
                        'isys_catg_voip_phone_line_list',
                        'isys_catg_voip_phone_line_list__id',
                        'isys_catg_voip_phone_line_list__isys_obj__id',
                        '',
                        '',
                        null,
                        SelectGroupBy::factory(['isys_catg_voip_phone_line_list__isys_obj__id'])
                    )
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CATG__VOIP_PHONE_LINE__VISUAL_MESSAGE_INDICATOR',
                ]
            ]),
            'audible_message_indicator'     => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__VOIP_PHONE_LINE__AUDIBLE_MESSAGE_INDICATOR',
                    C__PROPERTY__INFO__DESCRIPTION => 'Audible Message Waiting Indicator Policy'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_voip_phone_line_list__audible_message_indicator',
                    C__PROPERTY__DATA__SELECT => SelectSubSelect::factory(
                        'SELECT isys_catg_voip_phone_line_list__audible_message_indicator FROM isys_catg_voip_phone_line_list',
                        'isys_catg_voip_phone_line_list',
                        'isys_catg_voip_phone_line_list__id',
                        'isys_catg_voip_phone_line_list__isys_obj__id',
                        '',
                        '',
                        null,
                        SelectGroupBy::factory(['isys_catg_voip_phone_line_list__isys_obj__id'])
                    )
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CATG__VOIP_PHONE_LINE__AUDIBLE_MESSAGE_INDICATOR',
                ]
            ]),
            'ring_settings_idle'            => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__VOIP_PHONE_LINE__RING_SETTINGS_IDLE',
                    C__PROPERTY__INFO__DESCRIPTION => 'Ring Setting (Phone idle)'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_voip_phone_line_list__ring_settings_idle',
                    C__PROPERTY__DATA__SELECT => SelectSubSelect::factory(
                        'SELECT isys_catg_voip_phone_line_list__ring_settings_idle FROM isys_catg_voip_phone_line_list',
                        'isys_catg_voip_phone_line_list',
                        'isys_catg_voip_phone_line_list__id',
                        'isys_catg_voip_phone_line_list__isys_obj__id',
                        '',
                        '',
                        null,
                        SelectGroupBy::factory(['isys_catg_voip_phone_line_list__isys_obj__id'])
                    )
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CATG__VOIP_PHONE_LINE__RING_SETTINGS_IDLE',
                ]
            ]),
            'ring_settings_active'          => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__VOIP_PHONE_LINE__RING_SETTINGS_ACTIVE',
                    C__PROPERTY__INFO__DESCRIPTION => 'Ring Setting (Phone active)'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_voip_phone_line_list__ring_settings_active',
                    C__PROPERTY__DATA__SELECT => SelectSubSelect::factory(
                        'SELECT isys_catg_voip_phone_line_list__ring_settings_active FROM isys_catg_voip_phone_line_list',
                        'isys_catg_voip_phone_line_list',
                        'isys_catg_voip_phone_line_list__id',
                        'isys_catg_voip_phone_line_list__isys_obj__id',
                        '',
                        '',
                        null,
                        SelectGroupBy::factory(['isys_catg_voip_phone_line_list__isys_obj__id'])
                    )
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CATG__VOIP_PHONE_LINE__RING_SETTINGS_ACTIVE',
                ]
            ]),
            'call_pickup_group_idle'        => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__VOIP_PHONE_LINE__CALL_PICKUP_GROUP_IDLE',
                    C__PROPERTY__INFO__DESCRIPTION => 'Call pickup group audio alert setting (phone idle)'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_voip_phone_line_list__call_pickup_group_idle',
                    C__PROPERTY__DATA__SELECT => SelectSubSelect::factory(
                        'SELECT isys_catg_voip_phone_line_list__call_pickup_group_idle FROM isys_catg_voip_phone_line_list',
                        'isys_catg_voip_phone_line_list',
                        'isys_catg_voip_phone_line_list__id',
                        'isys_catg_voip_phone_line_list__isys_obj__id',
                        '',
                        '',
                        null,
                        SelectGroupBy::factory(['isys_catg_voip_phone_line_list__isys_obj__id'])
                    )
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CATG__VOIP_PHONE_LINE__CALL_PICKUP_GROUP_IDLE',
                ]
            ]),
            'call_pickup_group_active'      => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__VOIP_PHONE_LINE__CALL_PICKUP_GROUP_ACTIVE',
                    C__PROPERTY__INFO__DESCRIPTION => 'Call pickup group audio alert setting (phone active)'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_voip_phone_line_list__call_pickup_group_active',
                    C__PROPERTY__DATA__SELECT => SelectSubSelect::factory(
                        'SELECT isys_catg_voip_phone_line_list__call_pickup_group_active FROM isys_catg_voip_phone_line_list',
                        'isys_catg_voip_phone_line_list',
                        'isys_catg_voip_phone_line_list__id',
                        'isys_catg_voip_phone_line_list__isys_obj__id',
                        '',
                        '',
                        null,
                        SelectGroupBy::factory(['isys_catg_voip_phone_line_list__isys_obj__id'])
                    )
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CATG__VOIP_PHONE_LINE__CALL_PICKUP_GROUP_ACTIVE',
                ]
            ]),
            'recording_option'              => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__VOIP_PHONE_LINE__RECORDING_OPTION',
                    C__PROPERTY__INFO__DESCRIPTION => 'Recording option'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_voip_phone_line_list__recording_option',
                    C__PROPERTY__DATA__SELECT => SelectSubSelect::factory(
                        'SELECT isys_catg_voip_phone_line_list__recording_option FROM isys_catg_voip_phone_line_list',
                        'isys_catg_voip_phone_line_list',
                        'isys_catg_voip_phone_line_list__id',
                        'isys_catg_voip_phone_line_list__isys_obj__id',
                        '',
                        '',
                        null,
                        SelectGroupBy::factory(['isys_catg_voip_phone_line_list__isys_obj__id'])
                    )
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CATG__VOIP_PHONE_LINE__RECORDING_OPTION',
                ]
            ]),
            'recording_profile'             => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__VOIP_PHONE_LINE__RECORDING_PROFILE',
                    C__PROPERTY__INFO__DESCRIPTION => 'Recording profile'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_voip_phone_line_list__recording_profile',
                    C__PROPERTY__DATA__SELECT => SelectSubSelect::factory(
                        'SELECT isys_catg_voip_phone_line_list__recording_profile FROM isys_catg_voip_phone_line_list',
                        'isys_catg_voip_phone_line_list',
                        'isys_catg_voip_phone_line_list__id',
                        'isys_catg_voip_phone_line_list__isys_obj__id',
                        '',
                        '',
                        null,
                        SelectGroupBy::factory(['isys_catg_voip_phone_line_list__isys_obj__id'])
                    )
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CATG__VOIP_PHONE_LINE__RECORDING_PROFILE',
                ]
            ]),
            'monitoring_css'                => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__VOIP_PHONE_LINE__MONITORING_CSS',
                    C__PROPERTY__INFO__DESCRIPTION => 'Monitoring calling search space'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_voip_phone_line_list__monitoring_css',
                    C__PROPERTY__DATA__SELECT => SelectSubSelect::factory(
                        'SELECT isys_catg_voip_phone_line_list__monitoring_css FROM isys_catg_voip_phone_line_list',
                        'isys_catg_voip_phone_line_list',
                        'isys_catg_voip_phone_line_list__id',
                        'isys_catg_voip_phone_line_list__isys_obj__id',
                        '',
                        '',
                        null,
                        SelectGroupBy::factory(['isys_catg_voip_phone_line_list__isys_obj__id'])
                    )
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CATG__VOIP_PHONE_LINE__MONITORING_CSS',
                ]
            ]),
            'log_missed_calls'              => array_replace_recursive(isys_cmdb_dao_category_pattern::int(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__VOIP_PHONE_LINE__LOG_MISSED_CALLS',
                    C__PROPERTY__INFO__DESCRIPTION => 'Log missed calls'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_voip_phone_line_list__log_missed_calls',
                    C__PROPERTY__DATA__SELECT => SelectSubSelect::factory(
                        'SELECT (CASE
                                    WHEN isys_catg_voip_phone_line_list__log_missed_calls = \'1\' THEN ' . $this->convert_sql_text('LC__MODULE__QCW__ACTIVE') . '
                                    WHEN isys_catg_voip_phone_line_list__log_missed_calls = \'0\' THEN ' . $this->convert_sql_text('LC__MODULE__QCW__INACTIVE') . ' END)
                                FROM isys_catg_voip_phone_line_list',
                        'isys_catg_voip_phone_line_list',
                        'isys_catg_voip_phone_line_list__id',
                        'isys_catg_voip_phone_line_list__isys_obj__id',
                        '',
                        '',
                        SelectCondition::factory([]),
                        SelectGroupBy::factory(['isys_catg_voip_phone_line_list__isys_obj__id'])
                    ),
                    C__PROPERTY__DATA__JOIN   => [
                        SelectJoin::factory(
                            'isys_catg_voip_phone_line_list',
                            'LEFT',
                            'isys_catg_voip_phone_line_list__isys_obj__id',
                            'isys_obj__id'
                        )
                    ]
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID   => 'C__CMDB__CATG__VOIP_PHONE_LINE__LOG_MISSED_CALLS',
                    C__PROPERTY__UI__TYPE => C__PROPERTY__UI__TYPE__CHECKBOX
                ]
            ]),
            'external_phone_number_mask'    => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__VOIP_PHONE_LINE__EXTERNAL_PHONE_NUMBER_MASK',
                    C__PROPERTY__INFO__DESCRIPTION => 'External phone number mask'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_voip_phone_line_list__external_phone_number_mask',
                    C__PROPERTY__DATA__SELECT => SelectSubSelect::factory(
                        'SELECT isys_catg_voip_phone_line_list__external_phone_number_mask FROM isys_catg_voip_phone_line_list',
                        'isys_catg_voip_phone_line_list',
                        'isys_catg_voip_phone_line_list__id',
                        'isys_catg_voip_phone_line_list__isys_obj__id',
                        '',
                        '',
                        null,
                        SelectGroupBy::factory(['isys_catg_voip_phone_line_list__isys_obj__id'])
                    )
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CATG__VOIP_PHONE_LINE__EXTERNAL_PHONE_NUMBER_MASK',
                ]
            ]),
            'max_number_of_calls'           => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__VOIP_PHONE_LINE__MAX_NUMBER_OF_CALLS',
                    C__PROPERTY__INFO__DESCRIPTION => 'Maximum number of calls'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_voip_phone_line_list__max_number_of_calls',
                    C__PROPERTY__DATA__SELECT => SelectSubSelect::factory(
                        'SELECT isys_catg_voip_phone_line_list__max_number_of_calls FROM isys_catg_voip_phone_line_list',
                        'isys_catg_voip_phone_line_list',
                        'isys_catg_voip_phone_line_list__id',
                        'isys_catg_voip_phone_line_list__isys_obj__id',
                        '',
                        '',
                        null,
                        SelectGroupBy::factory(['isys_catg_voip_phone_line_list__isys_obj__id'])
                    )
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CATG__VOIP_PHONE_LINE__MAX_NUMBER_OF_CALLS',
                ]
            ]),
            'busy_trigger'                  => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__VOIP_PHONE_LINE__BUSY_TRIGGER',
                    C__PROPERTY__INFO__DESCRIPTION => 'Busy trigger'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_voip_phone_line_list__busy_trigger',
                    C__PROPERTY__DATA__SELECT => SelectSubSelect::factory(
                        'SELECT isys_catg_voip_phone_line_list__busy_trigger FROM isys_catg_voip_phone_line_list',
                        'isys_catg_voip_phone_line_list',
                        'isys_catg_voip_phone_line_list__id',
                        'isys_catg_voip_phone_line_list__isys_obj__id',
                        '',
                        '',
                        null,
                        SelectGroupBy::factory(['isys_catg_voip_phone_line_list__isys_obj__id'])
                    )
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CATG__VOIP_PHONE_LINE__BUSY_TRIGGER',
                ]
            ]),
            'caller_name'                   => array_replace_recursive(isys_cmdb_dao_category_pattern::int(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__VOIP_PHONE_LINE__CALLER_NAME',
                    C__PROPERTY__INFO__DESCRIPTION => 'Caller name'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_voip_phone_line_list__caller_name',
                    C__PROPERTY__DATA__SELECT => SelectSubSelect::factory(
                        'SELECT (CASE
                                    WHEN isys_catg_voip_phone_line_list__caller_name = \'1\' THEN ' . $this->convert_sql_text('LC__MODULE__QCW__ACTIVE') . '
                                    WHEN isys_catg_voip_phone_line_list__caller_name = \'0\' THEN ' . $this->convert_sql_text('LC__MODULE__QCW__INACTIVE') . ' END)
                                FROM isys_catg_voip_phone_line_list',
                        'isys_catg_voip_phone_line_list',
                        'isys_catg_voip_phone_line_list__id',
                        'isys_catg_voip_phone_line_list__isys_obj__id',
                        '',
                        '',
                        SelectCondition::factory([]),
                        SelectGroupBy::factory(['isys_catg_voip_phone_line_list__isys_obj__id'])
                    ),
                    C__PROPERTY__DATA__JOIN   => [
                        SelectJoin::factory(
                            'isys_catg_voip_phone_line_list',
                            'LEFT',
                            'isys_catg_voip_phone_line_list__isys_obj__id',
                            'isys_obj__id'
                        )
                    ]
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID   => 'C__CMDB__CATG__VOIP_PHONE_LINE__CALLER_NAME',
                    C__PROPERTY__UI__TYPE => C__PROPERTY__UI__TYPE__CHECKBOX
                ]
            ]),
            'caller_number'                 => array_replace_recursive(isys_cmdb_dao_category_pattern::int(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__VOIP_PHONE_LINE__CALLER_NUMBER',
                    C__PROPERTY__INFO__DESCRIPTION => 'Caller number'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_voip_phone_line_list__caller_number',
                    C__PROPERTY__DATA__SELECT => SelectSubSelect::factory(
                        'SELECT (CASE
                                    WHEN isys_catg_voip_phone_line_list__caller_number = \'1\' THEN ' . $this->convert_sql_text('LC__MODULE__QCW__ACTIVE') . '
                                    WHEN isys_catg_voip_phone_line_list__caller_number = \'0\' THEN ' . $this->convert_sql_text('LC__MODULE__QCW__INACTIVE') . ' END)
                                FROM isys_catg_voip_phone_line_list',
                        'isys_catg_voip_phone_line_list',
                        'isys_catg_voip_phone_line_list__id',
                        'isys_catg_voip_phone_line_list__isys_obj__id',
                        '',
                        '',
                        SelectCondition::factory([]),
                        SelectGroupBy::factory(['isys_catg_voip_phone_line_list__isys_obj__id'])
                    ),
                    C__PROPERTY__DATA__JOIN   => [
                        SelectJoin::factory(
                            'isys_catg_voip_phone_line_list',
                            'LEFT',
                            'isys_catg_voip_phone_line_list__isys_obj__id',
                            'isys_obj__id'
                        )
                    ]
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID   => 'C__CMDB__CATG__VOIP_PHONE_LINE__CALLER_NUMBER',
                    C__PROPERTY__UI__TYPE => C__PROPERTY__UI__TYPE__CHECKBOX
                ]
            ]),
            'redirected_number'             => array_replace_recursive(isys_cmdb_dao_category_pattern::int(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__VOIP_PHONE_LINE__REDIRECTED_NUMBER',
                    C__PROPERTY__INFO__DESCRIPTION => 'Redirected number'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_voip_phone_line_list__redirected_number',
                    C__PROPERTY__DATA__SELECT => SelectSubSelect::factory(
                        'SELECT (CASE
                                    WHEN isys_catg_voip_phone_line_list__redirected_number = \'1\' THEN ' . $this->convert_sql_text('LC__MODULE__QCW__ACTIVE') . '
                                    WHEN isys_catg_voip_phone_line_list__redirected_number = \'0\' THEN ' . $this->convert_sql_text('LC__MODULE__QCW__INACTIVE') . ' END)
                                FROM isys_catg_voip_phone_line_list',
                        'isys_catg_voip_phone_line_list',
                        'isys_catg_voip_phone_line_list__id',
                        'isys_catg_voip_phone_line_list__isys_obj__id',
                        '',
                        '',
                        SelectCondition::factory([]),
                        SelectGroupBy::factory(['isys_catg_voip_phone_line_list__isys_obj__id'])
                    ),
                    C__PROPERTY__DATA__JOIN   => [
                        SelectJoin::factory(
                            'isys_catg_voip_phone_line_list',
                            'LEFT',
                            'isys_catg_voip_phone_line_list__isys_obj__id',
                            'isys_obj__id'
                        )
                    ]
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID   => 'C__CMDB__CATG__VOIP_PHONE_LINE__REDIRECTED_NUMBER',
                    C__PROPERTY__UI__TYPE => C__PROPERTY__UI__TYPE__CHECKBOX
                ]
            ]),
            'dialed_number'                 => array_replace_recursive(isys_cmdb_dao_category_pattern::int(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__VOIP_PHONE_LINE__DIALED_NUMBER',
                    C__PROPERTY__INFO__DESCRIPTION => 'Dialed number'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_voip_phone_line_list__dialed_number',
                    C__PROPERTY__DATA__SELECT => SelectSubSelect::factory(
                        'SELECT (CASE
                                    WHEN isys_catg_voip_phone_line_list__dialed_number = \'1\' THEN ' . $this->convert_sql_text('LC__MODULE__QCW__ACTIVE') . '
                                    WHEN isys_catg_voip_phone_line_list__dialed_number = \'0\' THEN ' . $this->convert_sql_text('LC__MODULE__QCW__INACTIVE') . ' END)
                                FROM isys_catg_voip_phone_line_list',
                        'isys_catg_voip_phone_line_list',
                        'isys_catg_voip_phone_line_list__id',
                        'isys_catg_voip_phone_line_list__isys_obj__id',
                        '',
                        '',
                        SelectCondition::factory([]),
                        SelectGroupBy::factory(['isys_catg_voip_phone_line_list__isys_obj__id'])
                    ),
                    C__PROPERTY__DATA__JOIN   => [
                        SelectJoin::factory(
                            'isys_catg_voip_phone_line_list',
                            'LEFT',
                            'isys_catg_voip_phone_line_list__isys_obj__id',
                            'isys_obj__id'
                        )
                    ]
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID   => 'C__CMDB__CATG__VOIP_PHONE_LINE__DIALED_NUMBER',
                    C__PROPERTY__UI__TYPE => C__PROPERTY__UI__TYPE__CHECKBOX
                ]
            ]),
            'description'                   => array_replace_recursive(isys_cmdb_dao_category_pattern::commentary(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__LOGBOOK__DESCRIPTION',
                    C__PROPERTY__INFO__DESCRIPTION => 'Description'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_voip_phone_line_list__description',
                    C__PROPERTY__DATA__SELECT => SelectSubSelect::factory(
                        'SELECT isys_catg_voip_phone_line_list__description FROM isys_catg_voip_phone_line_list',
                        'isys_catg_voip_phone_line_list',
                        'isys_catg_voip_phone_line_list__id',
                        'isys_catg_voip_phone_line_list__isys_obj__id',
                        '',
                        '',
                        null,
                        SelectGroupBy::factory(['isys_catg_voip_phone_line_list__isys_obj__id'])
                    )
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_GLOBAL . defined_or_default('C__CATG__VOIP_PHONE_LINE', 'C__CATG__VOIP_PHONE_LINE')
                ]
            ])
        ];
    }

    /**
     * Updates existing entity given by user via HTTP GET and POST.
     *
     * @param   boolean $p_create
     *
     * @return  mixed Category data's identifier (int) or false (bool), otherwise null if nothing is created/saved
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function save_user_data($p_create)
    {
        $l_set_empty = [
            'C__CMDB__CATG__VOIP_PHONE_LINE__ALLOW_CTI_CONTROL',
            'C__CMDB__CATG__VOIP_PHONE_LINE__LOG_MISSED_CALLS',
            'C__CMDB__CATG__VOIP_PHONE_LINE__CALLER_NAME',
            'C__CMDB__CATG__VOIP_PHONE_LINE__CALLER_NUMBER',
            'C__CMDB__CATG__VOIP_PHONE_LINE__REDIRECTED_NUMBER',
            'C__CMDB__CATG__VOIP_PHONE_LINE__DIALED_NUMBER'
        ];

        // We need to set the checkbox-fields "0" if they are not set in the UI.
        foreach ($l_set_empty as $l_field) {
            if (!isset($_POST[$l_field])) {
                $_POST[$l_field] = 0;
            }
        }

        $l_associate_devices = explode(',', $_POST['C__CMDB__CATG__VOIP_PHONE_LINE__ASSOCIATED_DEVICES__selected_values']);

        // Also, we have to save the associated phones manually.
        $this->reset_associated_phones($_GET[C__CMDB__GET__CATLEVEL]);

        if (is_array($l_associate_devices) && count($l_associate_devices) > 0) {
            foreach ($l_associate_devices as $l_device) {
                $this->associate_phone($_GET[C__CMDB__GET__CATLEVEL], $l_device);
            }
        }

        return parent::save_user_data($p_create);
    }

    /**
     * Adds a new associate device.
     *
     * @param   integer $p_line_id
     * @param   integer $p_obj_id
     *
     * @return  boolean
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    protected function associate_phone($p_line_id, $p_obj_id)
    {
        if ($p_line_id > 0 && $p_obj_id > 0) {
            $l_query = 'INSERT INTO isys_catg_voip_phone_line_2_isys_obj (isys_catg_voip_phone_line__id, isys_obj__id) VALUES (' . $this->convert_sql_id($p_line_id) . ', ' .
                $this->convert_sql_id($p_obj_id) . ');';

            return ($this->update($l_query) && $this->apply_update());
        }

        return false;
    }

    /**
     * Resets the relation between phone-lines and objects.
     *
     * @param   integer $p_cat_id
     *
     * @return  boolean
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    protected function reset_associated_phones($p_cat_id)
    {
        $l_query = 'DELETE FROM isys_catg_voip_phone_line_2_isys_obj WHERE isys_catg_voip_phone_line__id = ' . $this->convert_sql_id($p_cat_id) . ';';

        return ($this->update($l_query) && $this->apply_update());
    }
}

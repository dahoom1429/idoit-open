<?php

use idoit\Component\Property\Type\DialogPlusProperty;

/**
 * i-doit
 *
 * DAO: global category for voice over IP phones.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 * @author      Leonard Fischer <lfischer@i-doit.org>
 * @since       1.0
 */
class isys_cmdb_dao_category_g_voip_phone extends isys_cmdb_dao_category_global
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'voip_phone';

    /**
     * Category entry is purgable
     *
     * @var bool
     */
    protected $m_is_purgable = true;

    /**
     * Method for returning the properties.
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    protected function properties()
    {
        return [
            'device_protocol'               => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__VOIP_PHONE__DEVICE_PROTOCOL',
                    C__PROPERTY__INFO__DESCRIPTION => 'Device protocol'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD => 'isys_catg_voip_phone_list__device_protocol'
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CATG__VOIP_PHONE__PROTOCOL',
                ]
            ]),
            'description2'                  => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__VOIP_PHONE__DESCRIPTION',
                    C__PROPERTY__INFO__DESCRIPTION => 'Description'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD => 'isys_catg_voip_phone_list__description2'
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CATG__VOIP_PHONE__DESCRIPTION',
                ]
            ]),
            'device_pool'                   => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__VOIP_PHONE__DEVICE_POOL',
                    C__PROPERTY__INFO__DESCRIPTION => 'Device pool'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD => 'isys_catg_voip_phone_list__device_pool'
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CATG__VOIP_PHONE__POOL',
                ]
            ]),
            'common_configuration'          => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__VOIP_PHONE__COMMON_DEVICE_CONFIGURATION',
                    C__PROPERTY__INFO__DESCRIPTION => 'Common device configuration'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD => 'isys_catg_voip_phone_list__common_device_configuration'
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CATG__VOIP_PHONE__COMMON_DEVICE_CONFIGURATION',
                ]
            ]),
            'button_template' => new DialogPlusProperty(
                'C__CMDB__CATG__VOIP_PHONE__BUTTON_TEMPLATE',
                'LC__CMDB__CATG__VOIP_PHONE__BUTTON_TEMPLATE',
                'isys_catg_voip_phone_list__isys_voip_phone_button_template__id',
                'isys_catg_voip_phone_list',
                'isys_voip_phone_button_template'
            ),
            'softkey_template' => new DialogPlusProperty(
                'C__CMDB__CATG__VOIP_PHONE__SOFTKEY_TEMPLATE',
                'LC__CMDB__CATG__VOIP_PHONE__SOFTKEY_TEMPLATE',
                'isys_catg_voip_phone_list__isys_voip_phone_softkey_template__id',
                'isys_catg_voip_phone_list',
                'isys_voip_phone_softkey_template'
            ),
            'common_profile'                => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__VOIP_PHONE__COMMON_PROFILE',
                    C__PROPERTY__INFO__DESCRIPTION => 'Common device profile'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD => 'isys_catg_voip_phone_list__common_profile'
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CATG__VOIP_PHONE__COMMON_PROFILE',
                ]
            ]),
            'calling_search_space'          => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__VOIP_PHONE__CALLING_SEARCH_SPACE',
                    C__PROPERTY__INFO__DESCRIPTION => 'Calling search space'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD => 'isys_catg_voip_phone_list__calling_search_space'
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CATG__VOIP_PHONE__CALLING_SEARCH_SPACE',
                ]
            ]),
            'aar_calling_search_space'      => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__VOIP_PHONE__AAR_CALLING_SEARCH_SPACE',
                    C__PROPERTY__INFO__DESCRIPTION => 'AAR Calling search space'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD => 'isys_catg_voip_phone_list__aar_calling_search_space'
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CATG__VOIP_PHONE__AAR_CALLING_SEARCH_SPACE',
                ]
            ]),
            'media_resource_group_list'     => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__VOIP_PHONE__MEDIA_RESOURCE_GROUP_LIST',
                    C__PROPERTY__INFO__DESCRIPTION => 'Media resource group list'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD => 'isys_catg_voip_phone_list__media_resource_group_list'
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CATG__VOIP_PHONE__MEDIA_RESOURCE_GROUP_LIST',
                ]
            ]),
            'user_hold_moh_audio_source'    => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__VOIP_PHONE__USER_HOLD_MOH_AUDIO_SOURCE',
                    C__PROPERTY__INFO__DESCRIPTION => 'User "Music on Hold" audio source'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD => 'isys_catg_voip_phone_list__user_hold_moh_audio_source'
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CATG__VOIP_PHONE__USER_HOLD_MOH_AUDIO_SOURCE',
                ]
            ]),
            'network_hold_moh_audio_source' => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__VOIP_PHONE__NETWORK_HOLD_MOH_AUDIO_SOURCE',
                    C__PROPERTY__INFO__DESCRIPTION => 'Network "Music on Hold" audio source'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD => 'isys_catg_voip_phone_list__network_hold_moh_audio_source'
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CATG__VOIP_PHONE__NETWORK_HOLD_MOH_AUDIO_SOURCE',
                ]
            ]),
            'location'                      => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__VOIP_PHONE__LOCATION',
                    C__PROPERTY__INFO__DESCRIPTION => 'Location'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD => 'isys_catg_voip_phone_list__location'
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CATG__VOIP_PHONE__LOCATION',
                ]
            ]),
            'aar_group'                     => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__VOIP_PHONE__AAR_GROUP',
                    C__PROPERTY__INFO__DESCRIPTION => 'AAR group'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD => 'isys_catg_voip_phone_list__aar_group'
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CATG__VOIP_PHONE__AAR_GROUP',
                ]
            ]),
            'user_locale'                   => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__VOIP_PHONE__USER_LOCALE',
                    C__PROPERTY__INFO__DESCRIPTION => 'User locale'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD => 'isys_catg_voip_phone_list__user_locale'
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CATG__VOIP_PHONE__USER_LOCALE',
                ]
            ]),
            'network_locale'                => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__VOIP_PHONE__NETWORK_LOCALE',
                    C__PROPERTY__INFO__DESCRIPTION => 'Network locale'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD => 'isys_catg_voip_phone_list__network_locale'
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CATG__VOIP_PHONE__NETWORK_LOCALE',
                ]
            ]),
            'built_in_bridge'               => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__VOIP_PHONE__BUILT_IN_BRIDGE',
                    C__PROPERTY__INFO__DESCRIPTION => 'Built in bridge'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD => 'isys_catg_voip_phone_list__built_in_bridge'
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CATG__VOIP_PHONE__BUILT_IN_BRIDGE',
                ]
            ]),
            'privacy'                       => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__VOIP_PHONE__PRIVACY',
                    C__PROPERTY__INFO__DESCRIPTION => 'Privacy'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD => 'isys_catg_voip_phone_list__privacy'
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CATG__VOIP_PHONE__PRIVACY',
                ]
            ]),
            'device_mobility_mode'          => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__VOIP_PHONE__DEVICE_MOBILITY_MODE',
                    C__PROPERTY__INFO__DESCRIPTION => 'Device mobility mode'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD => 'isys_catg_voip_phone_list__device_mobility_mode'
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CATG__VOIP_PHONE__DEVICE_MOBILITY_MODE',
                ]
            ]),
            'owner_user_id'                 => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__VOIP_PHONE__OWNER_USER_ID',
                    C__PROPERTY__INFO__DESCRIPTION => 'Owner user ID'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD => 'isys_catg_voip_phone_list__owner_user_id'
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CATG__VOIP_PHONE__OWNER_USER_ID',
                ]
            ]),
            'phone_suite'                   => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__VOIP_PHONE__PHONE_SUITE',
                    C__PROPERTY__INFO__DESCRIPTION => 'Phone suite'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD => 'isys_catg_voip_phone_list__phone_suite'
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CATG__VOIP_PHONE__PHONE_SUITE',
                ]
            ]),
            'services_provisioning'         => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__VOIP_PHONE__SERVICES_PROVISIONING',
                    C__PROPERTY__INFO__DESCRIPTION => 'Services provisioning'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD => 'isys_catg_voip_phone_list__services_provisioning'
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CATG__VOIP_PHONE__SERVICES_PROVISIONING',
                ]
            ]),
            'load_name'                     => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__VOIP_PHONE__LOAD_NAME',
                    C__PROPERTY__INFO__DESCRIPTION => 'Load name'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD => 'isys_catg_voip_phone_list__load_name'
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CATG__VOIP_PHONE__LOAD_NAME',
                ]
            ]),
            'description'                   => array_replace_recursive(isys_cmdb_dao_category_pattern::commentary(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__LOGBOOK__DESCRIPTION',
                    C__PROPERTY__INFO__DESCRIPTION => 'Description'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD => 'isys_catg_voip_phone_list__description'
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_GLOBAL . defined_or_default('C__CATG__VOIP_PHONE', 'C__CATG__VOIP_PHONE')
                ]
            ])
        ];
    }
}

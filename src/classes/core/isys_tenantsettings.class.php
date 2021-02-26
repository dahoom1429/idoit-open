<?php

/**
 * i-doit core classes.
 *
 * @package     i-doit
 * @subpackage  Core
 * @author      Dennis Stücken <dstuecken@synetics.de>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_tenantsettings implements isys_settings_interface
{
    const LDAP_SALUTATION_MAN_DEFAULT = 'Herr, Mr., Mr';
    const LDAP_SALUTATION_WOMAN_DEFAULT = 'Frau, Mrs., Mrs, Ms., Ms, Miss';

    use isys_settings_trait;

    /**
     * Database component.
     *
     * @var  isys_component_dao_tenant_settings
     */
    public static $m_dao;

    /**
     * Settings register.
     * Constant C__TREE__TITLE__MAXLEN is not used
     *
     * @var array
     */
    public static $m_definition = [
        'LC__SETTINGS__SYSTEM__URL_SETTINGS'     => [
            'system.base.uri' => [
                'title'       => 'LC__SETTINGS__SYSTEM__URL_SETTINGS__IDOIT_URL',
                'default'     => '',
                'placeholder' => 'https://i-doit.int/',
                'type'        => 'text',
                'description' => 'LC__SETTINGS__SYSTEM__URL_SETTINGS__IDOIT_URL_DESCRIPTION'
            ]
        ],
        'Display Limits'                         => [
            'cmdb.limits.obj-browser.objects-in-viewmode'     => [
                'title'       => 'LC__SETTINGS__CMDB__OBJ_BROWSER__OBJECTS_IN_VIEWMODE',
                'type'        => 'int',
                'placeholder' => 8,
                'default'     => 8,
                'description' => 'LC__SETTINGS__CMDB__OBJ_BROWSER__OBJECTS_IN_VIEWMODE_DESCRIPTION'
            ],
            'cmdb.limits.obj-browser.objects-rendering'       => [
                'title'   => 'LC__SETTINGS__CMDB__OBJ_BROWSER__OBJECT_RENDERING_IN_VIEWMODE',
                'type'    => 'select',
                'options' => [
                    'comma' => 'LC__SETTINGS__CMDB__OBJ_BROWSER__OBJECT_RENDERING_IN_VIEWMODE__COMMA',
                    'list'  => 'LC__SETTINGS__CMDB__OBJ_BROWSER__OBJECT_RENDERING_IN_VIEWMODE__LIST'
                ],
                'default' => 'comma'
            ],
            'gui.lists.preload-pages'                         => [
                'title'       => 'LC__SYSTEM__SETTINGS__TENANT__PRELOAD_PAGES_TITLE',
                'type'        => 'int',
                'placeholder' => 30,
                'default'     => 30
            ],
            'cmdb.lists.field-length-limit'                   => [
                'title'       => 'LC__SETTINGS__CMDB__FIELD_LENGTH_LIMIT',
                'type'        => 'int',
                'placeholder' => 75,
                'default'     => 0,
                'description' => 'LC__SETTINGS__CMDB__FIELD_LENGTH_LIMIT_DESCRIPTION'
            ],
            'cmdb.object-browser.max-objects'                 => [
                'title'       => 'LC__SYSTEM_SETTINGS__OBJECT_BROWSER_RESULT_LIMIT',
                'type'        => 'int',
                'placeholder' => 1500
            ],
            'cmdb.limits.port-lists-vlans'                    => [
                'title'       => 'LC__SETTINGS__CMDB__VLAN_LIMIT_IN_PORT_LISTS',
                'type'        => 'int',
                'placeholder' => 5,
                'default'     => 5
            ],
            'cmdb.limits.port-lists-layer2'                   => [
                'title'       => 'LC__SETTINGS__CMDB__LAYER2_LIMIT_IN_LOGICAL_PORT_LISTS',
                'placeholder' => 5,
                'default'     => 5,
                'type'        => 'int'
            ],
            'cmdb.limits.port-overview-default-vlan-only'     => [
                'title'   => 'LC__SETTINGS__CMDB__PORT_OVERVIEW_DEFAULT_VLAN_ONLY',
                'type'    => 'select',
                'options' => [
                    '0' => 'LC__UNIVERSAL__NO',
                    '1' => 'LC__UNIVERSAL__YES'
                ]
            ],
            'cmdb.limits.connector-lists-assigned_connectors' => [
                'title'       => 'LC__SETTINGS__CMDB__ASSIGNED_CONNECTOR_LIMIT_IN_CONNECTOR_LISTS',
                'type'        => 'int',
                'placeholder' => 5,
                'default'     => 5
            ],
            'cmdb.limits.ip-lists'                            => [
                'title'       => 'LC__SETTINGS__CMDB__IP_LISTS_LIMIT',
                'type'        => 'int',
                'placeholder' => 5,
                'default'     => 5
            ],
            'cmdb.limits.cmdb-explorer-service-browser'       => [
                'title'       => 'LC__SETTINGS__CMDB_EXPLORER__SERVICE_BROWSER_LIMIT',
                'type'        => 'int',
                'placeholder' => 2500,
                'default'     => 2500
            ],
            'cmdb.limits.location-path'                       => [
                'title'       => 'LC__SETTINGS__CMDB__LOCATION_PATH_LIMIT',
                'type'        => 'int',
                'placeholder' => 5,
                'default'     => 5
            ]
        ],
        'LC__SYSTEM_SETTINGS__TENANT__IP_LIST'   => [
            'cmdb.ip-list.cache-lifetime' => [
                'title'       => 'LC__SYSTEM_SETTINGS__TENANT__IP_LIST__CACHE_LIFETIME',
                'type'        => 'int',
                'default'     => 86400,
                'placeholder' => 86400
            ],
            'cmdb.ip-list.ping-method'    => [
                'title'   => 'LC__SYSTEM_SETTINGS__TENANT__IP_LIST__PING_METHOD',
                'type'    => 'select',
                'options' => [
                    'nmap'  => 'Ping via NMAP',
                    'fping' => 'Ping via FPING'
                ]
            ],
            'cmdb.ip-list.nmap-parameter' => [
                'title'   => 'LC__SYSTEM_SETTINGS__TENANT__IP_LIST__NMAP_PARAMETER',
                'type'    => 'select',
                'options' => [
                    'PE' => 'PE/PP/PM: ICMP echo, timestamp, and netmask request discovery probes',
                    'sP' => 'sP: Ping Scan - go no further than determining if host is online'
                ]
            ]
        ],
        'LC__TENANT_SETTINGS__UNIQUE_CHECKS'                         => [
            'cmdb.unique.object-title' => [
                'title'   => 'LC__UNIVERSAL__OBJECT_TITLE',
                'type'    => 'select',
                'options' => [
                    '0' => 'LC__UNIVERSAL__NO',
                    '1' => 'LC__UNIVERSAL__YES'
                ],
            ],
            'cmdb.unique.layer-2-net'  => [
                'title'   => 'LC__REPORT__VIEW__LAYER2_NETS__TITLE',
                'type'    => 'select',
                'options' => [
                    '0' => 'LC__UNIVERSAL__NO',
                    '1' => 'LC__UNIVERSAL__YES'
                ],
            ],
            'cmdb.unique.ip-address'   => [
                'title'   => 'LC__REPORT__VIEW__LAYER2_NETS__IP_ADDRESSES',
                'type'    => 'select',
                'options' => [
                    '0' => 'LC__UNIVERSAL__NO',
                    '1' => 'LC__UNIVERSAL__YES'
                ],
            ],
            'cmdb.unique.hostname'     => [
                'title'   => 'Hostname',
                'type'    => 'select',
                'options' => [
                    '0' => 'LC__UNIVERSAL__NO',
                    '1' => 'LC__UNIVERSAL__YES'
                ],
            ]
        ],
        'Barcodes'                               => [
            'barcode.enabled' => [
                'title'   => 'LC__SYSTEM_SETTINGS__TENANT__BARCODE_ENABLED',
                'type'    => 'select',
                'options' => [
                    '0' => 'LC__UNIVERSAL__NO',
                    '1' => 'LC__UNIVERSAL__YES'
                ],
                'default' => '1'
            ],
            'barcode.type'    => [
                'title'   => 'LC__SYSTEM_SETTINGS__TENANT__BARCODE_FORM',
                'type'    => 'select',
                'options' => [
                    'qr'     => 'QR-Code',
                    'code39' => 'Code39'
                ],
                'default' => 'qr'
            ]
        ],
        'LC__SYSTEM_SETTINGS__TENANT__GUI'       => [
            'gui.empty_value'                 => [
                'title'       => 'LC__SYSTEM_SETTINGS__TENANT__GUI__EMPTY_VALUES',
                'type'        => 'text',
                'placeholder' => '-',
                'default'     => '-'
            ],
            'gui.separator.location'          => [
                'title'       => 'LC__SYSTEM_SETTINGS__TENANT__GUI__LOCATION_SEPARATOR',
                'type'        => 'text',
                'placeholder' => ' > ',
                'default'     => ' > '
            ],
            'gui.location_path.direction.rtl' => [
                'title'   => 'LC__SYSTEM_SETTINGS__TENANT__GUI__LOCATION_DIRECTION',
                'type'    => 'select',
                'options' => [
                    '0' => 'LC__SYSTEM_SETTINGS__TENANT__GUI__LOCATION_DIRECTION_LTR',
                    '1' => 'LC__SYSTEM_SETTINGS__TENANT__GUI__LOCATION_DIRECTION_RTL'
                ],
                'default' => '0'
            ],
            'gui.separator.connector'         => [
                'title'       => 'LC__SYSTEM_SETTINGS__TENANT__GUI__CONNECTOR_SEPARATOR',
                'type'        => 'text',
                'placeholder' => ' > ',
                'default'     => ' > '
            ],
        ],
        'LC__SYSTEM_SETTINGS__TENANT__MAXLENGTH' => [
            'maxlength.dialog_plus'      => [
                'title'       => 'Dialog-Plus',
                'type'        => 'int',
                'placeholder' => 110,
                'default'     => 110
            ],
            'maxlength.location.objects' => [
                'title'       => 'LC__SYSTEM__SETTINGS__TENANT__MAXLENGTH_OBJECTS_IN_TREE',
                'type'        => 'int',
                'placeholder' => 16,
                'default'     => 16
            ],
            'maxlength.location.path'    => [
                'title'       => 'LC__SYSTEM__SETTINGS__TENANT__MAXLENGTH_LOCATION_PATH',
                'type'        => 'int',
                'placeholder' => 100,
                'default'     => 100
            ],
        ],
        'LC__SYSTEM_SETTINGS__TENANT__LOGBOOK'   => [
            'logbook.changes' => [
                'title'   => 'LC__SYSTEM_SETTINGS__TENANT__LOGBOOK__LOGGING',
                'type'    => 'select',
                'options' => [
                    '1' => 'LC__UNIVERSAL__YES',
                    '0' => 'LC__UNIVERSAL__NO'
                ],
                'default' => '1'
            ],
        ],
        'LC__SYSTEM_SETTINGS__TENANT__SECURITY'  => [
            'minlength.login.password'          => [
                'title'       => 'LC__SYSTEM_SETTINGS__TENANT__SECURITY__PASSWORD_MINLENGTH',
                'type'        => 'int',
                'placeholder' => 4,
                'default'     => 4
            ],
            'password.decrypt.in-export-import' => [
                'title'       => 'LC__SYSTEM_SETTINGS__TENANT__SECURITY__PASSWORD_AS_CLEAR_TEXT',
                'type'        => 'select',
                'options'     => [
                    '1' => 'LC__UNIVERSAL__YES',
                    '0' => 'LC__UNIVERSAL__NO'
                ],
                'default'     => '0',
                'description' => 'LC__SYSTEM_SETTINGS__TENANT__SECURITY__PASSWORD_AS_CLEAR_TEXT__DESCRIPTION'
            ]
        ],
        'Logging'                                => [
            'auth.logging' => [
                'title'       => 'LC__SYSTEM_SETTINGS__SYSTEM__AUTH_LOG',
                'type'        => 'select',
                'options'     => [
                    '0' => 'LC__UNIVERSAL__NO',
                    '1' => 'LC__UNIVERSAL__YES'
                ],
                'default'     => '0',
                'description' => 'LC__SYSTEM_SETTINGS__SYSTEM__AUTH_LOGGING_ENABLED'
            ],
            'logging.system.exceptions' => [
                'title'       => 'Exception Log',
                'type'        => 'select',
                'options'     => [
                    '0' => 'LC__UNIVERSAL__NO',
                    '1' => 'LC__UNIVERSAL__YES'
                ],
                'description' => 'LC__SYSTEM_SETTINGS__SYSTEM__LOGGING_ENABLED'
            ],
        ],
        'Quickinfo (Link mouseover)'             => [
            'cache.quickinfo.expiration'       => [
                'title'       => 'LC__SYSTEM_SETTINGS__TENANT__QUICKINFO_EXPIRATION',
                'type'        => 'select',
                'default'     => isys_convert::DAY,
                'options'     => [
                    isys_convert::MINUTE => 'LC__UNIVERSAL__MINUTE',
                    isys_convert::HOUR   => 'LC__UNIVERSAL__HOUR',
                    isys_convert::DAY    => 'LC__UNIVERSAL__DAY'
                ],
                'description' => 'LC__SYSTEM_SETTINGS__TENANT__QUICKINFO_EXPIRATION__DESCRIPTION'
            ],
            'cmdb.quickinfo.rows-per-category' => [
                'title'       => 'LC__SYSTEM_SETTINGS__TENANT__QUICKINFO_ROWS_PER_CATEGORY',
                'type'        => 'int',
                'placeholder' => 15,
                'default'     => 15,
                'description' => 'LC__SYSTEM_SETTINGS__TENANT__QUICKINFO_ROWS_PER_CATEGORY__DESCRIPTION'
            ]
        ],
        'CMDB'                                   => [
            'system.csv-export-delimiter'             => [
                'title'   => 'LC__SETTINGS__CMDB__EXPORT__CSV_DELIMITER',
                'type'    => 'select',
                'options' => [
                    ','  => 'LC__UNIVERSAL__COMMA',
                    ';'  => 'LC__UNIVERSAL__SEMICOLON',
                    '#'  => 'LC__UNIVERSAL__HASH',
                    "\t" => 'LC__UNIVERSAL__TAB'
                ],
                'default' => ';'
            ],
            'cmdb.gui.objectlist.direct-edit-mode'             => [
                'title'   => 'LC__SETTINGS__CMDB__OBJECT_LISTS__DIRECT_EDIT_MODE',
                'type'    => 'select',
                'options' => [
                    '0' => 'LC__UNIVERSAL__NO',
                    '1' => 'LC__UNIVERSAL__YES'
                ],
                'default' => '0'
            ],
            'cmdb.sysid.prefix'                                => [
                'title'   => 'LC__SYSTEM_SETTINGS__SYSID_PREFIX',
                'type'    => 'text',
                'default' => 'SYSID_',
            ],
            'cmdb.cable.change-cmdb-status-on-attach'          => [
                'title'   => 'LC__SETTINGS__CMDB__CHANGE_CABLE_ATTACH_CMDB_STATUS',
                'type'    => 'select',
                'options' => [
                    '0' => 'LC__UNIVERSAL__NO',
                    '1' => 'LC__UNIVERSAL__YES'
                ],
                'default' => '1'
            ],
            'cmdb.cable.change-cmdb-status-on-detach'          => [
                'title'   => 'LC__SETTINGS__CMDB__CHANGE_CABLE_DETACH_CMDB_STATUS',
                'type'    => 'select',
                'options' => [
                    '0' => 'LC__UNIVERSAL__NO',
                    '1' => 'LC__UNIVERSAL__YES'
                ],
                'default' => '1'
            ],
            'cmdb.rack.segment-template-object-type'           => [
                'title'       => 'LC__SETTINGS__CMDB__RACK_SEGMENT_TEMPLATE__OBJ_TYPE_ID',
                'type'        => 'text',
                'default'     => 'C__OBJTYPE__RACK_SEGMENT',
                'placeholder' => 'C__OBJTYPE__RACK_SEGMENT'
            ],
            'cmdb.rack.vertical-slot-sorting'                  => [
                'title'   => 'LC__SETTINGS__CMDB__RACK_VERTICAL_SORTING',
                'type'    => 'select',
                'options' => [
                    '1' => 'LC__SETTINGS__CMDB__RACK_VERTICAL_SORTING__A',
                    '2' => 'LC__SETTINGS__CMDB__RACK_VERTICAL_SORTING__B',
                    '3' => 'LC__SETTINGS__CMDB__RACK_VERTICAL_SORTING__C'
                ],
                'default' => '1'
            ],
            'cmdb.rack.vertical-slot-rear-mirrored'            => [
                'title'   => 'LC__SETTINGS__CMDB__RACK_VERTICAL_SLOTS_MIRRORED_FOR_REAR',
                'type'    => 'select',
                'options' => [
                    '0' => 'LC__UNIVERSAL__NO',
                    '1' => 'LC__UNIVERSAL__YES'
                ],
                'default' => '1'
            ],
            'cmdb.rack.slot-assignment-sort-direction'         => [
                'title'   => 'LC__SETTINGS__CMDB__RACK_ASSIGNMENT_SORT_DIRECTION',
                'type'    => 'select',
                'options' => [
                    'asc'  => 'LC__SETTINGS__CMDB__RACK_ASSIGNMENT_SORT_DIRECTION_ASC',
                    'desc' => 'LC__SETTINGS__CMDB__RACK_ASSIGNMENT_SORT_DIRECTION_DESC'
                ],
                'default' => 'asc'
            ],
            'cmdb.rack.rank-detached-segment-objects'          => [
                'title'   => 'LC__SETTINGS__CMDB__RACK_RANK_DETACHED_SEGMENT_OBJECTS',
                'type'    => 'select',
                'options' => [
                    C__RACK_DETACH_SEGMENT_ACTION__NONE    => 'LC__SETTINGS__CMDB__RACK_RANK_DETACHED_SEGMENT_OBJECTS_NO_ACTION',
                    C__RACK_DETACH_SEGMENT_ACTION__ARCHIVE => 'LC__SETTINGS__CMDB__RACK_RANK_DETACHED_SEGMENT_OBJECTS_ARCHIVE',
                    C__RACK_DETACH_SEGMENT_ACTION__PURGE   => 'LC__SETTINGS__CMDB__RACK_RANK_DETACHED_SEGMENT_OBJECTS_PURGE'
                ],
                'default' => C__RACK_DETACH_SEGMENT_ACTION__NONE
            ],
            'cmdb.chassis.handle-location-changes'             => [
                'title'       => 'LC__SETTINGS__CMDB__CHASSIS__HANDLE_LOCATION_CHANGES',
                'type'        => 'select',
                'options'     => [
                    '0' => 'LC__UNIVERSAL__NO',
                    '1' => 'LC__UNIVERSAL__YES'
                ],
                'default'     => '0',
                'description' => 'LC__SETTINGS__CMDB__CHASSIS__HANDLE_LOCATION_CHANGES_DESCRIPTION'
            ],
            'cmdb.logical-location.handle-location-inheritage' => [
                'title'       => 'LC__SETTINGS__CMDB__CATEGORY_LOGICAL_UNIT_HANDLE_LOCATION_INHERITAGE',
                'type'        => 'select',
                'options'     => [
                    '0' => 'LC__UNIVERSAL__NO',
                    '1' => 'LC__UNIVERSAL__YES'
                ],
                'default'     => '0',
                'description' => 'LC__SETTINGS__CMDB__CATEGORY_LOGICAL_UNIT_HANDLE_LOCATION_INHERITAGE_DESCRIPTION'
            ],
            'cmdb.logical-location.object-type-filter' => [
                'title'       => 'LC__SETTINGS__CMDB__CATEGORY_LOGICAL_UNIT_OBJECT_TYPE_FILTER',
                'type'        => 'select',
                'options'     => [
                    '0' => 'LC__UNIVERSAL__NO',
                    '1' => 'LC__UNIVERSAL__YES'
                ],
                'default'     => '1',
                'description' => 'LC__SETTINGS__CMDB__CATEGORY_LOGICAL_UNIT_OBJECT_TYPE_FILTER_DESCRIPTION'
            ],
            'cmdb.table.fuzzy-suggestion'                      => [
                'title'       => 'LC__SETTINGS__CMDB__TABLE__FUZZY_SUGGESTION',
                'type'        => 'select',
                'options'     => [
                    '0' => 'LC__UNIVERSAL__NO',
                    '1' => 'LC__UNIVERSAL__YES'
                ],
                'default'     => '0',
                'description' => 'LC__SETTINGS__CMDB__TABLE__FUZZY_SUGGESTION_DESCRIPTION'
            ],
            'cmdb.table.fuzzy-threshold'                       => [
                'title'       => 'LC__SETTINGS__CMDB__TABLE__FUZZY_THRESHOLD',
                'type'        => 'float',
                'placeholder' => 0.2,
                'default'     => 0.2,
                'description' => 'LC__SETTINGS__CMDB__TABLE__FUZZY_THRESHOLD_DESCRIPTION'
            ],
            'cmdb.table.fuzzy-distance'                        => [
                'title'       => 'LC__SETTINGS__CMDB__TABLE__FUZZY_DISTANCE',
                'type'        => 'int',
                'placeholder' => 50,
                'default'     => 50,
                'description' => 'LC__SETTINGS__CMDB__TABLE__FUZZY_DISTANCE_DESCRIPTION'
            ]
        ],
        'LDAP'                                   => [
            'ldap.config' => [
                'title'       => 'LDAP Config:',
                'type'        => 'textarea',
                'default'     => '',
                'description' => 'JSON String'
            ],
            'ldap.person.salutation.man' => [
                'title' => 'LC__LDAP__MAPPING__SALUTATION__MAN',
                'type' => 'text',
                'default' => self::LDAP_SALUTATION_MAN_DEFAULT,
                'description' => 'LC__LDAP__MAPPING__SALUTATION__MAN_DESC'
            ],
            'ldap.person.salutation.woman' => [
                'title' => 'LC__LDAP__MAPPING__SALUTATION__WOMAN',
                'type' => 'text',
                'default' => self::LDAP_SALUTATION_WOMAN_DEFAULT,
                'description' => 'LC__LDAP__MAPPING__SALUTATION__WOMAN_DESC'
            ],
        ],
    ];

    /**
     * Return a system setting
     *
     * @static
     *
     * @param   string $p_key     Setting identifier
     * @param   mixed  $p_default Default value
     *
     * @return  mixed
     */
    public static function get($p_key = null, $p_default = '')
    {
        if ($p_key === null) {
            return self::$m_settings;
        }

        if (isset(self::$m_settings[$p_key]) && self::$m_settings[$p_key] !== '') {
            return self::$m_settings[$p_key];
        }

        return isys_settings::get($p_key, $p_default);
    }

    /**
     * Method for retrieving the cache directory.
     *
     * @static
     * @return string
     * @throws Exception
     * @author Leonard Fischer <lfischer@i-doit.com>
     */
    protected static function get_cache_dir()
    {
        if (empty(self::$m_cache_dir)) {
            if (!is_object(isys_application::instance()->session)) {
                throw new Exception('Tenantsettings are only available after logging in.');
            }

            if (!isys_application::instance()->session->is_logged_in()) {
                throw new Exception('Tenantsettings are only available after logging in.');
            }

            global $g_mandator_info;

            if (!isset($g_mandator_info["isys_mandator__dir_cache"]) && !$g_mandator_info["isys_mandator__dir_cache"]) {
                throw new Exception('Error: Cache directory in $g_mandator_info not set.');
            }

            self::$m_cache_dir = $g_mandator_info["isys_mandator__dir_cache"] . '/';
        }

        return isys_glob_get_temp_dir() . self::$m_cache_dir;
    }

    /**
     * Load cache.
     *
     * @static
     *
     * @param   isys_component_database $p_database
     * @param   integer                 $p_tenant
     *
     * @throws Exception
     */
    public static function initialize(isys_component_database $p_database, $p_tenant = null)
    {
        if (self::$m_initialized === false) {
            // @see  ID-7107  If there is no "PRO" indicator, we remove the barcode definition.
            if (!defined('C__MODULE__PRO') || !constant('C__MODULE__PRO')) {
                unset(self::$m_definition['Barcodes']);
            }

            isys_component_signalcollection::get_instance()
                ->connect('system.shutdown', [
                    'isys_tenantsettings',
                    'shutdown'
                ]);

            self::$m_dao = new isys_component_dao_tenant_settings($p_database, $p_tenant);

            $l_cache_dir = self::get_cache_dir();

            // Generate cache and load settings.
            try {
                if (!file_exists($l_cache_dir . self::$m_cachefile)) {
                    self::regenerate();
                } else {
                    self::load_cache($l_cache_dir);
                }
            } catch (Exception $e) {
                if (isys_application::instance()->container->has('logger')) {
                    isys_application::instance()->container->get('logger')->error($e->getMessage());
                }

                // Load settings from database instead of cache.
                if (!self::$m_settings) {
                    self::$m_settings = self::$m_dao->get_settings();
                }
            }

            self::$m_initialized = true;
        }
    }

    /**
     * (Re)generates cache. Loads the cache into self::$m_settings.
     *
     * @throws  Exception
     * @return  array
     */
    public static function regenerate()
    {
        try {
            self::$m_settings = self::$m_dao->get_settings();

            // Write settings cache.
            self::write(self::get_cache_dir() . self::$m_cachefile, self::$m_settings);
        } catch (Exception $e) {
            throw $e;
        }

        return self::$m_settings;
    }
}

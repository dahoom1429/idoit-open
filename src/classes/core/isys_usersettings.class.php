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
class isys_usersettings implements isys_settings_interface
{
    use isys_settings_trait;

    /**
     * Database component.
     *
     * @var  isys_component_dao_user_settings
     */
    protected static $m_dao;

    /**
     * Settings register.
     *
     * @var  array
     */
    protected static $m_definition = [
        'Quickinfo (Link mouseover)'  => [
            'gui.quickinfo.active' => [
                'title'   => 'LC__USER_SETTINGS__QUICKINFO_ACTIVE',
                'type'    => 'select',
                'default' => '1',
                'options' => [
                    '0' => 'LC__UNIVERSAL__NO',
                    '1' => 'LC__UNIVERSAL__YES'
                ]
            ],
            'gui.quickinfo.delay'  => [
                'title'       => 'LC__UNIVERSAL__DELAY',
                'type'        => 'float',
                'default'     => 0.5,
                'placeholder' => 0.5
            ]
        ],
        'LC__SETTINGS__CMDB__LISTS'   => [
            'gui.objectlist.remember-filter' => [
                'title'       => 'LC__CMDB__TREE__SYSTEM__OBJECT_LIST__FILTER_MEMORIZE',
                'type'        => 'int',
                'default'     => 300,
                'placeholder' => 0,
                'description' => 'LC__CMDB__TREE__SYSTEM__OBJECT_LIST__FILTER_MEMORIZE_DESCRIPTION'
            ],
            'gui.objectlist.rows-per-page'   => [
                'title'       => 'LC__SYSTEM__REGISTRY__PAGELIMIT',
                'type'        => 'int',
                'default'     => 50,
                'placeholder' => 50
            ]
        ],
        'LC__MODULE__QCW__CATEGORIES' => [
            'gui.category.cabling.directly-open-cabling-addon' => [
                'title'   => 'LC__CABLING__NEW_VISUALIZATION__ADDON_READY_DIRECTLY_OPEN_IN_ADDON_SETTING',
                'type'    => 'select',
                'options' => [
                    '0' => 'LC__UNIVERSAL__NO',
                    '1' => 'LC__UNIVERSAL__YES'
                ]
            ]
        ]
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

        if (isset(self::$m_settings[$p_key]) && self::$m_settings[$p_key] != '') {
            return self::$m_settings[$p_key];
        }

        return isys_tenantsettings::get($p_key, $p_default);
    }

    /**
     * Method for retrieving the cache directory.
     *
     * @static
     * @throws  Exception
     * @return  string
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    protected static function get_cache_dir()
    {
        if (empty(self::$m_cache_dir)) {
            $session = isys_application::instance()->container->get('session');

            if (!is_object($session)) {
                throw new Exception('Tenantsettings are only available after logging in.');
            }

            if (!$session->is_logged_in()) {
                throw new Exception('Tenantsettings are only available after logging in.');
            }

            global $g_mandator_info;

            if (!isset($g_mandator_info['isys_mandator__dir_cache']) && !$g_mandator_info['isys_mandator__dir_cache']) {
                throw new Exception('Error: Cache directory in $g_mandator_info not set.');
            }

            self::$m_cache_dir = $g_mandator_info['isys_mandator__dir_cache'] . '/';
        }

        return isys_glob_get_temp_dir() . self::$m_cache_dir;
    }

    /**
     * Load cache.
     *
     * @static
     *
     * @param   isys_component_database $p_database
     *
     * @return  void
     */
    public static function initialize(isys_component_database $p_database)
    {
        if (!self::$m_initialized) {
            isys_component_signalcollection::get_instance()
                ->connect('system.shutdown', [
                    'isys_usersettings',
                    'shutdown'
                ]);

            self::$m_cachefile = 'settings.' . isys_application::instance()->session->get_user_id() . '.cache';

            if (!is_object(self::$m_dao)) {
                self::$m_dao = new isys_component_dao_user_settings($p_database);
            }

            $l_cache_dir = self::get_cache_dir();

            // Generate cache and load settings.
            if ($l_cache_dir) {
                try {
                    if (file_exists($l_cache_dir . self::$m_cachefile)) {
                        self::load_cache($l_cache_dir);
                    } else {
                        self::regenerate();
                    }
                } catch (Exception $e) {
                    isys_application::instance()->container->get('logger')->error('Usersettings cache error: ' . $e->getMessage());

                    // Load settings from database instead of cache.
                    if (!self::$m_settings) {
                        self::$m_settings = self::$m_dao->get_settings();
                    }
                }
            }

            self::$m_initialized = true;
        }
    }

    /**
     * (Re)generates cache. Loads the cache into self::$m_settings.
     *
     * @param  int $userId
     *
     * @throws  Exception
     * @return  array
     */
    public static function regenerate($userId = null)
    {
        try {
            if ($userId === null) {
                $userId = isys_application::instance()->container->get('session')->get_user_id();
            }

            self::$m_settings = self::$m_dao->get_settings();

            self::$m_cachefile = 'settings.' . $userId . '.cache';

            // Write settings cache.
            self::write(self::get_cache_dir() . self::$m_cachefile, self::$m_settings);
        } catch (Exception $e) {
            throw $e;
        }

        return self::$m_settings;
    }

    /**
     * Set a setting value.
     *
     * @static
     *
     * @param  string $p_key
     * @param  mixed  $p_value
     */
    public static function set($p_key, $p_value)
    {
        self::$m_changed = true;

        if (!isset(self::$m_settings[$p_key])) {
            self::$m_dao->set($p_key, $p_value)->apply_update();
        }

        self::$m_settings[$p_key] = $p_value;
    }
}

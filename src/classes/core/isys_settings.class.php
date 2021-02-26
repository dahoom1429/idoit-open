<?php

/**
 * i-doit core classes
 *
 * @package     i-doit
 * @subpackage  Core
 * @author      Dennis Stücken <dstuecken@synetics.de>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_settings implements isys_settings_interface
{
    use isys_settings_trait;

    /**
     * Database component.
     *
     * @var  isys_component_dao_settings
     */
    protected static $m_dao;

    /**
     * Settings register.
     *
     * @var  array
     */
    protected static $m_definition = [
        'User interface'    => [
            'gui.wiki-url'             => [
                'title'       => 'Wiki URL',
                'type'        => 'text',
                'placeholder' => 'https://wikipedia.org/wiki/'
            ],
            'gui.wysiwyg'              => [
                'title'   => 'LC__SYSTEM_SETTINGS__WYSIWYG_EDITOR',
                'type'    => 'select',
                'options' => [
                    '0' => 'LC__UNIVERSAL__NO',
                    '1' => 'LC__UNIVERSAL__YES'
                ]
            ],
            'gui.wysiwyg-all-controls' => [
                'title'   => 'LC__SYSTEM_SETTINGS__WYSIWYG_EDITOR_FULL_CONTROL',
                'type'    => 'select',
                'options' => [
                    '0' => 'LC__UNIVERSAL__NO',
                    '1' => 'LC__UNIVERSAL__YES'
                ]
            ],
            'login.tenantlist.sortby'  => [
                'title'   => 'LC__SYSTEM_SETTINGS__TENANT_SORT_FUNCTION',
                'type'    => 'select',
                'options' => [
                    'isys_mandator__title' => 'LC__UNIVERSAL__TITLE',
                    'isys_mandator__sort'  => 'LC__SYSTEM_SETTINGS__TENANT_SORT_FUNCTION__CUSTOM'
                ]
            ]
        ],
        'Session'           => [
            'session.time' => [
                'title'       => 'Session timeout',
                'type'        => 'int',
                'placeholder' => 300,
                'default'     => 300,
                'description' => 'LC__CMDB__UNIT_OF_TIME__SECOND'
            ]
        ],
        'Single Sign On'    => [
            'session.sso.active'      => [
                'title'   => 'LC__UNIVERSAL__ACTIVE',
                'type'    => 'select',
                'options' => [
                    '0' => 'LC__UNIVERSAL__NO',
                    '1' => 'LC__UNIVERSAL__YES'
                ]
            ],
            'session.sso.mandator-id' => [
                'title' => 'LC__SYSTEM_SETTINGS__DEFAULT_MANDATOR',
                'type'  => 'select'
            ],
            'session.sso.use-domain-part' => [
                'title'   => 'LC__UNIVERSAL__USE_DOMAIN_PART',
                'type'    => 'select',
                'options' => [
                    '0' => 'LC__UNIVERSAL__NO',
                    '1' => 'LC__UNIVERSAL__YES'
                ]
            ],
        ],
        'System Parameters' => [
            'reports.browser-url'            => [
                'title'       => 'Report-Browser URL',
                'type'        => 'text',
                'hidden'      => true,
                'placeholder' => 'https://reports-ng.i-doit.org/'
            ],
            'ldap.default-group'             => [
                'title'       => 'LC__SYSTEM_SETTINGS__DEFAULT_LDAP_GROUP',
                'type'        => 'text',
                'description' => 'LC__SYSTEM_SETTINGS__LDAP_GROUP_DESCRIPTION',
                'placeholder' => ''
            ],
            'cmdb.connector.suffix-schema'   => [
                'title'  => '',
                'type'   => 'select',
                'hidden' => true
            ],
            'system.timezone'                => [
                'title'       => 'LC__SYSTEM_SETTINGS__PHP_TIMEZONE',
                'type'        => 'text',
                'placeholder' => 'Europe/Berlin',
                'description' => '<a href="https://php.net/manual/timezones.php">https://php.net/manual/timezones.php</a>'
            ],
            'auth.active'                    => [
                'title'   => 'LC__MODULE__AUTH',
                'type'    => 'select',
                'options' => [
                    '0' => 'LC__MODULE__QCW__INACTIVE',
                    '1' => 'LC__NOTIFICATIONS__NOTIFICATION_STATUS'
                ]
            ],
            'system.devmode'                 => [
                'title'   => 'Developer mode',
                'type'    => 'select',
                'options' => [
                    '0' => 'LC__UNIVERSAL__NO',
                    '1' => 'LC__UNIVERSAL__YES'
                ],
                'hidden'  => true
            ],
            'system.dir.file-upload'         => [
                'title'       => 'LC__SYSTEM_SETTINGS__FILE_UPLOAD_DIRECTORY',
                'placeholder' => '/path/to/i-doit/upload/files/',
                'type'        => 'text'
            ],
            'system.dir.image-upload'        => [
                'title'       => 'LC__SYSTEM_SETTINGS__IMAGE_UPLOAD_DIRECTORY',
                'placeholder' => '/path/to/i-doit/upload/images/',
                'type'        => 'text'
            ],
            'tts.rt.queues'                  => [
                'title'       => 'Request Tracker queues',
                'type'        => 'text',
                'placeholder' => 'General'
            ],
            'cmdb.quickpurge'                => [
                'title'       => 'LC__SYSTEM_SETTINGS__QUICKPURGE',
                'type'        => 'select',
                'options'     => [
                    '0' => 'LC__UNIVERSAL__NO',
                    '1' => 'LC__UNIVERSAL__YES'
                ],
                'description' => 'LC__SYSTEM_SETTINGS__QUICKPURGE_DESCRIPTION'
            ],
            'cmdb.object.title.cable-prefix' => [
                'title' => 'LC__SYSTEM_SETTINGS__OBJECT_CABLE_PREFIX',
                //'Object cable prefix',
                'type'  => 'text',
            ],
            'import.object.keep-status'      => [
                'title'       => 'LC__SYSTEM_SETTINGS__IMPORT_OBJECT_KEEP',
                'type'        => 'select',
                'options'     => [
                    '0' => 'LC__UNIVERSAL__NO',
                    '1' => 'LC__UNIVERSAL__YES'
                ],
                'description' => 'LC__SYSTEM_SETTINGS__IMPORT_OBJECT_KEEP_STATUS_DESCRIPTION'
            ]
        ],
        'Logging'           => [
            'logging.cmdb.import' => [
                'title'   => 'CMDB Import',
                'type'    => 'select',
                'options' => [
                    '0' => 'LC__UNIVERSAL__NO',
                    '1' => 'LC__UNIVERSAL__YES'
                ]
            ],
            'ldap.debug'          => [
                'title'       => 'LDAP Debug',
                'type'        => 'select',
                'description' => 'ldap_debug in log/',
                'options'     => [
                    '0' => 'LC__UNIVERSAL__NO',
                    '1' => 'LC__UNIVERSAL__YES'
                ]
            ]
        ],
        'E-Mail'            => [
            'system.email.smtp-host'          => [
                'title'       => 'SMTP Host',
                'type'        => 'text',
                'placeholder' => 'mail.i-doit.com'
            ],
            'system.email.port'               => [
                'title'       => 'SMTP Port',
                'type'        => 'int',
                'placeholder' => 25
            ],
            'system.email.username'           => [
                'title'       => 'SMTP Username',
                'type'        => 'text',
                'placeholder' => 'username'
            ],
            'system.email.password'           => [
                'title'       => 'SMTP Password',
                'type'        => 'password',
                'placeholder' => 'password'
            ],
            'system.email.smtp-auto-tls'      => [
                'title'   => 'SMTP TLS',
                'type'    => 'select',
                'options' => [
                    '0' => 'LC__UNIVERSAL__NO',
                    '1' => 'LC__UNIVERSAL__YES'
                ]
            ],
            'system.email.from'               => [
                'title'       => 'LC__SYSTEM_SETTINGS__SENDER',
                'type'        => 'text',
                'placeholder' => 'i-doit@i-doit.com'
            ],
            'system.email.name'               => [
                'title'       => 'Name',
                'type'        => 'text',
                'placeholder' => 'i-doit'
            ],
            'system.email.connection-timeout' => [
                'title'       => 'Timeout',
                'type'        => 'int',
                'placeholder' => 60
            ],
            'system.email.smtpdebug'          => [
                'title'   => 'SMTP Debug',
                'type'    => 'select',
                'options' => [
                    '0' => 'LC__UNIVERSAL__NO',
                    '1' => 'LC__UNIVERSAL__YES'
                ]
            ],
            'system.email.subject-prefix'     => [
                'title'       => 'LC__SYSTEM_SETTINGS__SUBJET_PREFIX',
                'type'        => 'text',
                'placeholder' => '[i-doit] '
            ],
            'email.template.maintenance'      => [
                'title' => 'LC__SYSTEM_SETTINGS__MAINTENANCE_CONTRACT_TEMPLATE',
                'type'  => 'textarea'
            ]
        ],
        'Proxy'             => [
            'proxy.active'   => [
                'title'   => 'LC__UNIVERSAL__ACTIVE',
                'type'    => 'select',
                'options' => [
                    '0' => 'LC__UNIVERSAL__NO',
                    '1' => 'LC__UNIVERSAL__YES'
                ]
            ],
            'proxy.host'     => [
                'title'       => 'LC__SYSTEM_SETTINGS__HOST_IP_ADDRESS',
                'type'        => 'text',
                'placeholder' => 'proxy.i-doit.com'
            ],
            'proxy.port'     => [
                'title'       => 'Port',
                'type'        => 'int',
                'placeholder' => 3128
            ],
            'proxy.username' => [
                'title' => 'LC__LOGIN__USERNAME',
                'type'  => 'text'
            ],
            'proxy.password' => [
                'title' => 'LC__LOGIN__PASSWORD',
                'type'  => 'password'
            ]
        ],
        'Security'          => [
            'system.security.csrf' => [
                'title'       => 'CSRF-Token',
                'description' => 'LC__SYSTEM_SETTINGS__SECURITY__CSRF_IN_LOGIN',
                'type'        => 'select',
                'options'     => [
                    '1' => 'LC__UNIVERSAL__YES',
                    '0' => 'LC__UNIVERSAL__NO'
                ],
                'default'     => '0'
            ]
        ],
        'Login'             => [
            'system.login.welcome-message' => [
                'title'       => 'LC__SYSTEM_SETTINGS__LOGIN_WELCOME_MESSAGE',
                'type'        => 'textarea',
                'placeholder' => 'LC__SYSTEM_SETTINGS__LOGIN_WELCOME_MESSAGE_DEFAULT'
            ]
        ]
    ];

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
        isys_component_signalcollection::get_instance()
            ->connect('system.shutdown', [
                'isys_settings',
                'shutdown'
            ]);

        self::$m_dao = new isys_component_dao_settings($p_database);
        $l_cache_dir = self::get_cache_dir();

        // Generate cache and load settings.
        if ($l_cache_dir) {
            try {
                if (!file_exists($l_cache_dir . self::$m_cachefile)) {
                    self::regenerate();
                } else {
                    self::load_cache($l_cache_dir);
                }
            } catch (Exception $e) {
                // @todo log cache exceptions to system log

                // Load settings from database instead of cache.
                if (!self::$m_settings) {
                    self::$m_settings = self::$m_dao->get_settings();
                }
            }
        }

        self::$m_initialized = true;
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

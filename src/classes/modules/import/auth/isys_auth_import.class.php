<?php

/**
 * i-doit
 * Auth: Class for Notifications module authorization rules.
 *
 * @package     i-doit
 * @subpackage  auth
 * @author      Van Quyen Hoang <qhoang@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_auth_import extends isys_auth implements isys_auth_interface
{
    /**
     * Container for singleton instance.
     *
     * @var  isys_auth_import
     */
    private static $m_instance;

    /**
     * Retrieve singleton instance of authorization class.
     *
     * @return  isys_auth_import
     * @author  Selcuk Kekec <skekec@i-doit.com>
     */
    public static function instance()
    {
        // If the DAO has not been loaded yet, we initialize it now.
        if (self::$m_dao === null) {
            self::$m_dao = new isys_auth_dao(isys_application::instance()->container->get('database'));
        }

        if (self::$m_instance === null) {
            self::$m_instance = new self;
        }

        return self::$m_instance;
    }

    /**
     * Method for returning the available auth-methods. This will be used for the GUI.
     *
     * @return  array
     * @author  Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function get_auth_methods()
    {
        return [
            'import'              => [
                'title' => 'LC__AUTH_GUI__IMPORT_CONDITION',
                'type'  => 'import'
            ],
            'csv_import_profiles' => [
                'title'  => 'LC__AUTH_GUI__IMPORT_CSV_PROFILE_CONDITION',
                'type'   => 'boolean',
                'rights' => [
                    self::VIEW,
                    self::EDIT,
                    self::DELETE,
                    self::CREATE,
                ]
            ]
        ];
    }

    /**
     * Get ID of related module.
     *
     * @return  integer
     */
    public function get_module_id()
    {
        return defined_or_default('C__MODULE__IMPORT');
    }

    /**
     * Get title of related module.
     *
     * @return  string
     */
    public function get_module_title()
    {
        return 'LC__MODULE__IMPORT';
    }

    /**
     * @param $p_right
     *
     * @return bool
     * @throws isys_exception_auth
     */
    public function csv_import_profiles($p_right)
    {
        if (!$this->is_auth_active()) {
            return true;
        }

        $l_right_name = isys_auth::get_right_name($p_right);

        return $this->generic_right(
            $p_right,
            'csv_import_profiles',
            self::EMPTY_ID_PARAM,
            new isys_exception_auth(isys_application::instance()->container->get('language')->get('LC__AUTH_GUI__IMPORT_CSV_PROFILE_CONDITION_EXCEPTION', $l_right_name))
        );
    }

    /**
     * Determines the rights for the import module.
     *
     * @param   integer $p_right
     * @param   mixed   $p_type
     *
     * @return  boolean
     * @throws  isys_exception_auth
     * @author  Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function import($p_right, $p_type)
    {
        if (!$this->is_auth_active()) {
            return true;
        }

        if (!defined('C__MODULE__IMPORT')) {
            return false;
        }

        switch ($p_type) {
            case C__MODULE__IMPORT . C__IMPORT__GET__LDAP:
                $l_exception = isys_application::instance()->container->get('language')
                    ->get('LC__AUTH__EXCEPTION__MISSING_ACTION_RIGHT', [
                        isys_application::instance()->container->get('language')
                            ->get('LC__MODULE__IMPORT__LDAP')
                    ]);
                break;
            case C__MODULE__IMPORT . C__IMPORT__GET__IMPORT:
                $l_exception = isys_application::instance()->container->get('language')
                    ->get('LC__AUTH__EXCEPTION__MISSING_ACTION_RIGHT', [
                        isys_application::instance()->container->get('language')
                            ->get('LC__UNIVERSAL__FILE_IMPORT')
                    ]);
                break;
            case C__MODULE__IMPORT . C__IMPORT__GET__CABLING:
                $l_exception = isys_application::instance()->container->get('language')
                    ->get('LC__AUTH__EXCEPTION__MISSING_ACTION_RIGHT', [
                        isys_application::instance()->container->get('language')
                            ->get('LC__MODULE__IMPORT__CABLING')
                    ]);
                break;
            case C__MODULE__IMPORT . C__IMPORT__GET__OCS_OBJECTS:
                $l_exception = isys_application::instance()->container->get('language')
                    ->get('LC__AUTH__EXCEPTION__MISSING_ACTION_RIGHT', [
                        isys_application::instance()->container->get('language')
                            ->get('LC__MODULE__IMPORT__OCS')
                    ]);
                break;
            case C__MODULE__IMPORT . C__IMPORT__GET__JDISC:
                $l_exception = isys_application::instance()->container->get('language')
                    ->get('LC__AUTH__EXCEPTION__MISSING_ACTION_RIGHT', [
                        isys_application::instance()->container->get('language')
                            ->get('LC__MODULE__JDISC')
                    ]);
                break;
            case C__MODULE__IMPORT . C__IMPORT__GET__SHAREPOINT:
                $l_exception = isys_application::instance()->container->get('language')
                    ->get('LC__AUTH__EXCEPTION__MISSING_ACTION_RIGHT', [
                        isys_application::instance()->container->get('language')
                            ->get('LC__MODULE__IMPORT__SHAREPOINT')
                    ]);
                break;
            default:
                $l_exception = isys_application::instance()->container->get('language')
                    ->get('LC__AUTH__SYSTEM_EXCEPTION__MISSING_RIGHT_FOR_IMPORT');
                break;
        }

        return $this->check_module_rights($p_right, 'import', $p_type, new isys_exception_auth($l_exception));
    }

    /**
     * Method for retrieving the "parameter" in the configuration GUI. Gets called generically by "ajax()" method.
     *
     * @see     isys_module_auth->ajax_retrieve_parameter();
     *
     * @param   string  $p_method
     * @param   string  $p_param
     * @param   integer $p_counter
     * @param   boolean $p_editmode
     *
     * @return  mixed
     * @author  Leonard Fischer <lfischer@synetics.de>
     */
    public function retrieve_parameter($p_method, $p_param, $p_counter, $p_editmode = false)
    {
        $l_return = [
            'html'    => '',
            'method'  => $p_method,
            'param'   => $p_param,
            'counter' => $p_counter
        ];
        if (!defined('C__MODULE__IMPORT')) {
            return false;
        }

        $l_dialog_data = null;

        switch ($p_method) {
            case 'import':
                $l_dialog_data = [
                    C__MODULE__IMPORT . C__IMPORT__GET__LDAP        => 'LC__MODULE__IMPORT__LDAP',
                    C__MODULE__IMPORT . C__IMPORT__GET__IMPORT      => 'LC__UNIVERSAL__FILE_IMPORT',
                    C__MODULE__IMPORT . C__IMPORT__GET__CABLING     => 'LC__MODULE__IMPORT__CABLING',
                    C__MODULE__IMPORT . C__IMPORT__GET__OCS_OBJECTS => 'LC__MODULE__IMPORT__OCS'
                ];

                if (defined('C__MODULE__JDISC')) {
                    $l_dialog_data[C__MODULE__IMPORT . C__IMPORT__GET__JDISC] = 'LC__MODULE__JDISC';
                }

                if (defined('C__MODULE__SHAREPOINT')) {
                    $l_dialog_data[C__MODULE__IMPORT . C__IMPORT__GET__SHAREPOINT] = 'LC__MODULE__IMPORT__SHAREPOINT';
                }
        }

        if ($l_dialog_data !== null && is_array($l_dialog_data)) {
            $l_dialog = new isys_smarty_plugin_f_dialog();

            if (is_string($p_param)) {
                $p_param = strtolower($p_param);
            }

            $l_params = [
                'name'              => 'auth_param_form_' . $p_counter,
                'p_arData'          => $l_dialog_data,
                'p_editMode'        => $p_editmode,
                'p_bDbFieldNN'      => 1,
                'p_bInfoIconSpacer' => 0,
                'p_strClass'        => 'input-small',
                'p_strSelectedID'   => $p_param
            ];

            $l_return['html'] = $l_dialog->navigation_edit(isys_application::instance()->template, $l_params);

            return $l_return;
        }

        return false;
    }
}

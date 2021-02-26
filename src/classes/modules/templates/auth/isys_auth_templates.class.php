<?php

/**
 * i-doit
 * Auth: Class for Templates module authorization rules.
 *
 * @package     i-doit
 * @subpackage  auth
 * @author      Van Quyen Hoang <qhoang@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_auth_templates extends isys_auth implements isys_auth_interface
{
    /**
     * Container for singleton instance.
     *
     * @var  isys_auth_templates
     */
    private static $m_instance;

    /**
     * Retrieve singleton instance of authorization class.
     *
     * @return  isys_auth_templates
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
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function get_auth_methods()
    {
        return [
            'templates'    => [
                'title' => 'LC__AUTH_GUI__TEMPLATES_CONDITION',
                'type'  => 'templates'
            ],
            'mass_changes' => [
                'title' => 'LC__AUTH_GUI__MASS_CHANGES_CONDITION',
                'type'  => 'mass_changes'
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
        return defined_or_default('C__MODULE__TEMPLATES');
    }

    /**
     * Get title of related module.
     *
     * @return  string
     */
    public function get_module_title()
    {
        return 'LC__MODULE__TEMPLATES';
    }

    /**
     * Determines the rights for the template module (templates).
     *
     * @param   integer $p_right
     * @param   mixed   $p_type
     *
     * @return  boolean
     * @throws  isys_exception_auth
     * @author  Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function templates($p_right, $p_type)
    {
        if (!defined('C__MODULE__TEMPLATES')) {
            return false;
        }
        if (!$this->is_auth_active()) {
            return true;
        }

        $l_arr = [
            C__MODULE__TEMPLATES . TPL_PID__EXISTING  => 'LC__TEMPLATES__EXISTING_TEMPLATES',
            C__MODULE__TEMPLATES . TPL_PID__NEW       => 'LC__TEMPLATES__NEW_TEMPLATE',
            C__MODULE__TEMPLATES . TPL_PID__NEW_OBJET => 'LC__TEMPLATES__CREATE_OBJECTS'
        ];

        if (array_key_exists($p_type, $l_arr)) {
            $l_exception = isys_application::instance()->container->get('language')
                ->get('LC__AUTH__EXCEPTION__MISSING_ACTION_RIGHT', [
                    isys_application::instance()->container->get('language')
                        ->get($l_arr[$p_type])
                ]);
        } else {
            $l_exception = isys_application::instance()->container->get('language')
                ->get('LC__AUTH__SYSTEM_EXCEPTION__MISSING_RIGHT_FOR_TEMPLATES');
        }

        return $this->check_module_rights($p_right, 'templates', $p_type, new isys_exception_auth($l_exception));
    }

    /**
     * Determines the rights for the template module (mass changes).
     *
     * @param   integer $p_right
     * @param   mixed   $p_type
     *
     * @return  boolean
     * @throws  isys_exception_auth
     * @author  Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function mass_changes($p_right, $p_type)
    {
        if (!defined('C__MODULE__TEMPLATES')) {
            return false;
        }
        if (!$this->is_auth_active()) {
            return true;
        }

        $l_arr = [
            C__MODULE__TEMPLATES . TPL_PID__EXISTING    => 'LC__MASS_CHANGE__EXISTING_TEMPLATES',
            C__MODULE__TEMPLATES . TPL_PID__MASS_CHANGE => 'LC__MASS_CHANGE',
            C__MODULE__TEMPLATES . TPL_PID__NEW         => 'LC__MASS_CHANGE__CREATE_NEW_TEMPLATE'
        ];

        if (array_key_exists($p_type, $l_arr)) {
            $l_exception = isys_application::instance()->container->get('language')
                ->get('LC__AUTH__EXCEPTION__MISSING_ACTION_RIGHT', [
                    isys_application::instance()->container->get('language')
                        ->get($l_arr[$p_type])
                ]);
        } else {
            $l_exception = isys_application::instance()->container->get('language')
                ->get('LC__AUTH__SYSTEM_EXCEPTION__MISSING_RIGHT_FOR_MASS_CHANGES');
        }

        return $this->check_module_rights($p_right, 'mass_changes', $p_type, new isys_exception_auth($l_exception));
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
        if (!defined('C__MODULE__TEMPLATES')) {
            return false;
        }
        $l_return = [
            'html'    => '',
            'method'  => $p_method,
            'param'   => $p_param,
            'counter' => $p_counter
        ];

        $l_dialog_data = null;

        switch ($p_method) {
            case 'templates':
                $l_dialog_data = [
                    C__MODULE__TEMPLATES . TPL_PID__EXISTING  => 'LC__TEMPLATES__EXISTING_TEMPLATES',
                    C__MODULE__TEMPLATES . TPL_PID__NEW       => 'LC__TEMPLATES__NEW_TEMPLATE',
                    C__MODULE__TEMPLATES . TPL_PID__NEW_OBJET => 'LC__TEMPLATES__CREATE_OBJECTS'
                ];
                break;

            case 'mass_changes':
                $l_dialog_data = [
                    C__MODULE__TEMPLATES . TPL_PID__EXISTING    => 'LC__MASS_CHANGE__EXISTING_TEMPLATES',
                    C__MODULE__TEMPLATES . TPL_PID__MASS_CHANGE => 'LC__MASS_CHANGE',
                    C__MODULE__TEMPLATES . TPL_PID__NEW         => 'LC__MASS_CHANGE__CREATE_NEW_TEMPLATE'
                ];
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

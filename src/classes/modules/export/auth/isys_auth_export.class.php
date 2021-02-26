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
class isys_auth_export extends isys_auth implements isys_auth_interface
{
    /**
     * Container for singleton instance.
     *
     * @var  isys_auth_export
     */
    private static $m_instance;

    /**
     * Retrieve singleton instance of authorization class.
     *
     * @return  isys_auth_export
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
            'export' => [
                'title' => 'LC__AUTH_GUI__EXPORT_CONDITION',
                'type'  => 'export'
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
        return defined_or_default('C__MODULE__EXPORT');
    }

    /**
     * Get title of related module.
     *
     * @return  string
     */
    public function get_module_title()
    {
        return 'LC__MODULE__EXPORT';
    }

    /**
     * Determines the rights for the export module.
     *
     * @param   integer $p_right
     * @param   mixed   $p_type
     *
     * @return  boolean
     * @throws  isys_exception_auth
     * @author  Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function export($p_right, $p_type)
    {
        if (!$this->is_auth_active()) {
            return true;
        }
        if (!defined('C__MODULE__EXPORT')) {
            return true;
        }

        switch ($p_type) {
            case C__MODULE__EXPORT . '1':
                $l_exception = isys_application::instance()->container->get('language')
                    ->get('LC__AUTH__EXCEPTION__MISSING_ACTION_RIGHT', [
                        isys_application::instance()->container->get('language')
                            ->get('LC__MODULE__EXPORT__EXPORT_WIZARD')
                    ]);
                break;

            default:
                $l_exception = isys_application::instance()->container->get('language')
                    ->get('LC__AUTH__SYSTEM_EXCEPTION__MISSING_RIGHT_FOR_EXPORT');
                break;
        }

        return $this->check_module_rights($p_right, 'export', $p_type, new isys_exception_auth($l_exception));
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
        if (!defined('C__MODULE__EXPORT')) {
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
            case 'export':
                $l_dialog_data = [
                    C__MODULE__EXPORT . '1' => 'LC__MODULE__EXPORT__EXPORT_WIZARD',
                    C__MODULE__EXPORT . '2' => 'LC__MODULE__EXPORT__EXPORT_DRAFT',
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

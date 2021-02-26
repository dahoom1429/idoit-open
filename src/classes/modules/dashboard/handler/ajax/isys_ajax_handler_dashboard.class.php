<?php

/**
 * AJAX
 *
 * @package     i-doit
 * @subpackage  General
 * @author      Leonard Fischer <lfischer@i-doit.org>
 * @version     1.0
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 * @since       1.2.0
 */
class isys_ajax_handler_dashboard extends isys_ajax_handler
{
    /**
     * Init method, which gets called from the framework.
     *
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function init()
    {
        // We set the header information because we don't accept anything than JSON.
        header('Content-Type: application/json');

        $l_return = [
            'success' => true,
            'message' => null,
            'data'    => null
        ];

        try {
            switch ($_GET['func']) {
                case 'load_widget':
                    $l_return['data'] = $this->load_widget($_POST['identifier'], $_POST['unique_id'], $_POST['id'], !!$_POST['default'], $_POST['config']);
                    break;

                case 'update_widget':
                    $l_return['data'] = $this->update_widget($_POST[C__GET__ID], $_POST['config'], $_POST['unique_id']);
                    break;

                case 'remove_widget':
                    $l_return['data'] = $this->remove_widget($_POST['data_id']);
                    break;

                case 'save_dashboard_config':
                    $l_return['data'] = $this->save_dashboard_config($_POST['widgets'], $_POST['deletions']);
                    break;

                case 'define_dashboard_default':
                    $l_return['data'] = $this->define_dashboard_default($_POST['widgets']);
                    break;

                case 'overwrite_user_dashboard':
                    $l_return['data'] = $this->overwrite_user_dashboard($_POST['widgets'], $_POST['users']);
                    break;
            }
        } catch (Exception $e) {
            $l_return['success'] = false;
            $l_return['message'] = $e->getMessage();
        }

        echo isys_format_json::encode($l_return);

        $this->_die();
    }

    /**
     * This method defines, if the hypergate needs to be included for this request.
     *
     * @static
     * @return  boolean
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public static function needs_hypergate()
    {
        return true;
    }

    /**
     * Method for retrieving the selected widget.
     *
     * @param   integer $p_id
     *
     * @return  boolean
     * @throws  isys_exception_database
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function remove_widget($p_id)
    {
        if (isys_dashboard_dao::instance($this->m_database_component)
            ->remove_widget_from_dashboard($p_id)) {
            return true;
        } else {
            throw new isys_exception_database($this->m_database_component->get_last_error_as_string());
        }
    }

    /**
     * Method for saving the current dashboard configuration (sorting).
     *
     * @param   string $p_widgets
     * @param   string $p_deletions
     *
     * @return  boolean
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function save_dashboard_config($p_widgets, $p_deletions)
    {
        global $g_comp_session;

        $l_dao = isys_dashboard_dao::instance($this->m_database_component);
        $l_widgets = isys_format_json::decode($p_widgets, true);
        $l_deletions = isys_format_json::decode($p_deletions, true);
        $l_user_id = $g_comp_session->get_user_id();

        foreach ($l_widgets as $l_sorting => $l_widget) {
            if (!is_numeric($l_widget)) {
                // We got a new widget.
                $l_widget = $l_dao->get_data(null, null, $l_widget)
                    ->get_row();

                $l_dao->add_widget_to_dashboard($l_user_id, $l_widget['isys_widgets__id'], $l_widget['isys_widgets__default_config'], $l_sorting);
            } else {
                // We update an existing widget.
                $l_dao->update_user_widget($l_widget, ['isys_widgets_config__sorting' => $l_sorting]);
            }
        }

        foreach ($l_deletions as $l_deletion) {
            if (is_numeric($l_deletion)) {
                $l_dao->remove_widget_from_dashboard($l_deletion);
            }
        }
    }

    /**
     * Method for saving the current dashboard configuration (sorting).
     *
     * @param   string $p_widgets
     *
     * @return  boolean
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function define_dashboard_default($p_widgets)
    {
        $l_dao = isys_dashboard_dao::instance($this->m_database_component);
        $l_widgets = isys_format_json::decode($p_widgets, true);

        $l_dao->reset_default_widgets();

        foreach ($l_widgets as $l_sorting => $l_widget) {
            $l_options = [
                'isys_widgets__default' => 1,
                'isys_widgets__sorting' => $l_sorting
            ];

            if (!is_numeric($l_widget)) {
                $l_widget = $l_dao->get_data(null, null, $l_widget)
                    ->get_row();

                $l_options['isys_widgets__default_config'] = $l_widget['isys_widgets__default_config'];
            } else {
                $l_widget = $l_dao->get_data_by_user_widget_id($l_widget)
                    ->get_row();

                $l_options['isys_widgets__default_config'] = $l_widget['isys_widgets_config__configuration'];
            }

            $l_dao->update_module_widget($l_widget['isys_widgets__id'], $l_options);
        }
    }

    /**
     * Method for saving the current dashboard configuration (sorting).
     *
     * @param   string $p_widgets
     * @param   array  $p_users
     *
     * @return  boolean
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function overwrite_user_dashboard($p_widgets, $p_users = [])
    {
        global $g_comp_session;

        $l_dao = isys_dashboard_dao::instance($this->m_database_component);
        $l_widgets = isys_format_json::decode($p_widgets, true);
        $l_users = isys_format_json::decode($p_users, true);
        $l_current_user = $g_comp_session->get_user_id();

        if (!is_array($l_users) || !count($l_users)) {
            return null;
        }

        foreach ($l_users as $l_user_id) {
            if ($l_user_id == $l_current_user) {
                continue;
            }

            // At first, we reset the users dashboard.
            $l_dao->reset_user_dashboard($l_user_id);

            if (!is_array($l_widgets) || !count($l_widgets)) {
                continue;
            }

            // Then, we add the widgets to the dashboard.
            foreach ($l_widgets as $l_sorting => $l_widget) {
                if (!is_numeric($l_widget)) {
                    $l_widget = $l_dao->get_data(null, null, $l_widget)
                        ->get_row();

                    $l_config = $l_widget['isys_widgets__default_config'];
                } else {
                    $l_widget = $l_dao->get_data_by_user_widget_id($l_widget)
                        ->get_row();

                    $l_config = $l_widget['isys_widgets_config__configuration'];
                }

                $l_dao->add_widget_to_dashboard($l_user_id, $l_widget['isys_widgets__id'], $l_config, $l_sorting);
            }
        }
    }

    /**
     * Method for retrieving the selected widget.
     *
     * @param   string  $p_identifier
     * @param   string  $p_unique_id
     * @param   integer $p_widget_id
     * @param   boolean $p_default
     * @param   string  $p_config
     *
     * @throws  isys_exception_general
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    protected function load_widget($p_identifier, $p_unique_id, $p_widget_id, $p_default = true, $p_config = null)
    {
        // Enable cache lifetime of 30 minutes
        isys_core::expire(isys_convert::HOUR / 2);

        $l_dao = new isys_dashboard_dao($this->m_database_component);

        $l_row = $l_dao->get_data_by_user_widget_id($p_widget_id)
            ->get_row();
        $l_config = $l_row['isys_widgets_config__configuration'];

        // If the user has defined no widgets, we display the defaults.
        if ($p_default || !is_array($l_row) || $p_identifier != $l_row['isys_widgets__identifier']) {
            $l_row = $l_dao->get_data($p_widget_id)
                ->get_row();
            $l_config = $l_row['isys_widgets__default_config'];
        }

        try {
            $l_config = isys_format_json::decode($l_config, true);
        } catch (\idoit\Exception\JsonException $e) {
            $l_config = [];
        }

        // Fixing the "iframe" widget for OPEN users.
        if (isys_format_json::is_json_array($p_config)) {
            $l_config = isys_format_json::decode($p_config);
        }

        $l_classname = 'isys_dashboard_widgets_' . $p_identifier;

        if (!class_exists($l_classname)) {
            $l_classname = isys_register::factory('widget-register')
                ->get($p_identifier);
        }

        if (class_exists($l_classname)) {
            return isys_dashboard_widgets::factory($l_classname)
                ->init($l_config)
                ->render($p_unique_id);
        }

        throw new isys_exception_general(isys_application::instance()->container->get('language')
            ->get('LC__MODULE__DASHBOARD__EXCEPTION__WIDGET_CLASS_NOT_FOUND', 'isys_dashboard_widgets_' . $p_identifier));
    }

    /**
     * Method for retrieving the selected widget.
     *
     * @param   string $p_id
     * @param   string $p_config
     * @param   string $p_unique_id
     *
     * @return  array
     * @throws  isys_exception_database
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    protected function update_widget($p_id, $p_config, $p_unique_id)
    {
        if (isys_dashboard_dao::instance($this->m_database_component)
            ->update_user_widget($p_id, ['isys_widgets_config__configuration' => $p_config])) {
            // We need the identifier - It's inside the "unique_id", we just need to remove the last underscore and the number.
            $l_identifier = implode('_', explode('_', $p_unique_id, -1));

            return $this->load_widget($l_identifier, $p_unique_id, $p_id, false);
        } else {
            throw new isys_exception_database($this->m_database_component->get_last_error_as_string());
        }
    }
}

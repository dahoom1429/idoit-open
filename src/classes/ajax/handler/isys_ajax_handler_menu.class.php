<?php

/**
 * Menu AJAX handler for several things: Dragbar, Visibility, Breadcrumb, ...
 *
 * @package     i-doit
 * @subpackage  General
 * @author      Leonard Fischer <lfischer@i-doit.com>
 * @version     1.0
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 * @since       1.0
 */
class isys_ajax_handler_menu extends isys_ajax_handler
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
                case 'save_menu_width':
                    $l_return['data'] = $this->save_menu_width($_POST['menu_width']);
                    break;

                case 'save_tree_visibility':
                    $l_return['data'] = $this->save_tree_visibility($_POST['objtype'], $_POST['categories']);
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
     * Method for saving the menu width.
     *
     * @param   integer $p_width
     *
     * @return  boolean
     */
    protected function save_menu_width($p_width = 235)
    {
        // Initialize, set and regenerate the cache
        isys_usersettings::set('gui.leftcontent.width', $p_width);

        return true;
    }

    /**
     * Method for saving the menu visibility (hide empty items).
     *
     * @param   boolean $p_obj_type
     * @param   boolean $p_category
     *
     * @return  boolean
     * @throws  Exception
     * @throws  isys_exception_general
     */
    protected function save_tree_visibility($p_obj_type = null, $p_category = null)
    {
        global $g_comp_database;

        $l_dao = isys_component_dao_user::instance($g_comp_database);

        $l_settings = $l_dao->get_user_settings();

        if (!$l_settings) {
            try {
                // Some configuration is missing (entry in isys_user_ui)... Try to create it.
                $l_settings = $l_dao->prepare_user_setting()
                    ->get_user_settings();
            } catch (Exception $e) {
                isys_notify::error($e->getMessage(), ['sticky' => true]);

                return false;
            }
        }

        $l_visibility = (int)$l_settings['isys_user_ui__tree_visible'];

        // 1 = object types.
        if ($p_obj_type !== null) {
            if ($p_obj_type) {
                $l_visibility = ($l_visibility & 1) ? $l_visibility - 1 : $l_visibility;
            } else {
                $l_visibility = $l_visibility | 1;
            }
        }

        // 2 = categories.
        if ($p_category !== null) {
            if ($p_category) {
                $l_visibility = ($l_visibility & 2) ? $l_visibility - 2 : $l_visibility;
            } else {
                $l_visibility = $l_visibility | 2;
            }
        }

        $l_dao->save_settings(C__SETTINGS_PAGE__THEME, ['menu_visibility' => $l_visibility], false);

        return true;

        throw new isys_exception_general('Could not find out which user is logged in!');
    }
}
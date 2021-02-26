<?php

/**
 * AJAX
 *
 * @package     i-doit
 * @subpackage  General
 * @author      Dennis Stücken <dstuecken@synetics.de>
 * @version     1.0
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_ajax_handler_fetch_mandators extends isys_ajax_handler
{
    /**
     * Initialization.
     */
    public function init()
    {
        global $g_config;

        try {
            $session = isys_application::instance()->container->get('session');
        } catch (Exception $e) {
            global $g_comp_session;

            $session = $g_comp_session;
        }

        try {
            $database = isys_application::instance()->container->get('database');
        } catch (Exception $e) {
            global $g_comp_database;

            $database = $g_comp_database;
        }

        if ($session->is_logged_in()) {
            if (isset($_POST['mandator_id']) && $_POST['mandator_id'] > 0) {
                try {
                    $session->change_mandator($_POST['mandator_id']);
                    $this->_die();
                } catch (Exception $e) {
                    // Nothing to do here.
                }
            }

            $md5Password = isys_cmdb_dao_category_s_person_master::instance($database)
                ->get_person_by_username($session->get_current_username())
                ->get_row_value('isys_cats_person_list__user_pass');

            $tenantList = $session->fetch_mandators($session->get_current_username(), $md5Password, true);

            if (is_countable($tenantList) && count($tenantList) > 1) {
                /**
                 * We cannot always use the referer here, because the module we are in may not exist or has
                 * got another id for the new mandator so this feature is user configurable now.
                 */
                $url = $g_config['www_dir'];
                if (isset($_SERVER['HTTP_REFERER']) && isys_tenantsettings::get('gui.mandator-switch.keep-url', false) && strpos($_SERVER['HTTP_REFERER'], $g_config['www_dir']) !== false) {
                    $url = $_SERVER['HTTP_REFERER'];
                }

                $jsOnChange = 'new Ajax.Call(\'?call=fetch_mandators\',{parameters:{mandator_id:$F(this)},onComplete:function(){document.location = \'' . $url . '\';}});';

                echo '<select name="mandator_id" id="mandator_id" class="input input-block" onchange="' . $jsOnChange . '">';

                $currentTenantId = $session->get_mandator_id();

                foreach ($tenantList as $tenantId => $tenantData) {
                    $l_options = '';

                    if ($currentTenantId == $tenantId) {
                        $l_options = ' selected="selected" style="font-weight:bold;"';
                    }

                    echo '<option value="' . $tenantId . '" ' . $l_options . '>' . $tenantData['title'] . '</option>';
                }

                echo '</select>';
            } else {
                $l_mandator_info = array_pop($tenantList);
                echo '<strong>' . $l_mandator_info['title'] . '</strong>';
            }
        }

        $this->_die();
    }
}

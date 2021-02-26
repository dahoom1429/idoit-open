<?php
/**
 * i-doit
 *
 * Trouble-Ticket-System Module
 *
 * @package    i-doit
 * @subpackage Modules
 *
 * @author     Dennis Stücken <dstuecken@synetics.de>
 * @copyright  synetics GmbH
 * @license    http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
define("C__GET__TTS__PAGE", "npID");

class isys_module_tts extends isys_module implements isys_module_interface
{

    const DISPLAY_IN_MAIN_MENU = false;

    // Define, if this module shall be displayed in the named menus.
    const DISPLAY_IN_SYSTEM_MENU = true;

    /**
     * @var bool
     */
    protected static $m_licenced = true;

    /**
     * This method builds the tree for the menu.
     *
     * @param   isys_component_tree $p_tree
     * @param   boolean             $p_system_module
     * @param   integer             $p_parent
     *
     * @author  Leonard Fischer <lfischer@i-doit.org>
     * @since   0.9.9-7
     * @see     isys_module::build_tree()
     */
    public function build_tree(isys_component_tree $p_tree, $p_system_module = true, $p_parent = null)
    {
        if (!defined('C__MODULE__TTS')) {
            return;
        }
        $l_parent = -1;
        $l_submodule = '';

        if ($p_system_module) {
            $l_parent = $p_tree->find_id_by_title('Modules');
            $l_submodule = '&' . C__GET__MODULE_SUB_ID . '=' . C__MODULE__TTS;
        }

        if (null !== $p_parent && is_int($p_parent)) {
            $l_root = $p_parent;
        } else {
            $l_root = $p_tree->add_node(C__MODULE__TTS . '0', $l_parent, isys_application::instance()->container->get('language')
                ->get('LC__MODULE__TTS'));
        }

        $p_tree->add_node(C__MODULE__TTS . '1', $l_root, isys_application::instance()->container->get('language')
            ->get('LC__TTS__CONFIGURATION'),
            '?' . C__GET__MODULE_ID . '=' . $_GET[C__GET__MODULE_ID] . $l_submodule . '&' . C__GET__TREE_NODE . '=' . C__MODULE__TTS . '1' . '&' . C__GET__TTS__PAGE . '=' . 1,
            '', 'images/icons/silk/comments.png', 0, '', '', isys_auth_system::instance()
                ->is_allowed_to(isys_auth::SUPERVISOR, 'TTS/CONFIG'));

        if (!$p_system_module) {

        }
    }

    /**
     * Start module
     */
    public function start()
    {
        isys_auth_system::instance()
            ->check(isys_auth::VIEW, 'TTS/CONFIG');

        $l_dao_tts = new isys_tts_dao(isys_application::instance()->database);

        $l_gets = isys_module_request::get_instance()
            ->get_gets();
        $l_posts = isys_module_request::get_instance()
            ->get_posts();

        if (empty($l_gets[C__GET__TTS__PAGE])) {
            $l_gets[C__GET__TTS__PAGE] = 1;
        }

        if ($l_gets[C__GET__MODULE_ID] != defined_or_default('C__MODULE__SYSTEM')) {
            $l_tree = isys_module_request::get_instance()
                ->get_menutree();

            $this->build_tree($l_tree, false);
            $l_tree->select_node_by_id($l_gets[C__GET__TREE_NODE]);
            isys_application::instance()->template->assign("menu_tree", $l_tree->process($l_gets[C__GET__TREE_NODE]));
        }

        switch ($l_posts[C__GET__NAVMODE]) {
            case C__NAVMODE__SAVE:
                if (strpos($l_posts["C__MODULE__REQUEST_TRACKER_CONFIG__LINK"], "://") !== false) {
                    $l_password = $l_posts["C__MODULE__REQUEST_TRACKER_CONFIG__PASS"];

                    if ($l_posts['C__MODULE__REQUEST_TRACKER_CONFIG__PASS__action'] == isys_smarty_plugin_f_password::PASSWORD_UNCHANGED) {
                        $l_password = null;
                    }

                    $l_dao_tts->save($l_posts["C__MODULE__REQUEST_TRACKER_CONFIG__DB_ACTIVE"], $l_posts["C__TTS__TYPE"], $l_posts["C__MODULE__REQUEST_TRACKER_CONFIG__LINK"],
                        $l_posts["C__MODULE__REQUEST_TRACKER_CONFIG__USER"], $l_password);
                } else {
                    // @todo  Check if "p_strInfoIconError" can be removed.
                    $l_rules["C__MODULE__REQUEST_TRACKER_CONFIG__LINK"]["p_strInfoIconError"] = isys_application::instance()->container->get('language')
                        ->get("LC__UNIVERSAL__FIELD_VALUE_IS_INVALID");
                    $l_rules["C__MODULE__REQUEST_TRACKER_CONFIG__LINK"]["message"] = isys_application::instance()->container->get('language')
                        ->get("LC__UNIVERSAL__FIELD_VALUE_IS_INVALID");
                }
                break;
        }

        $l_edit_right = isys_auth_system::instance()
            ->is_allowed_to(isys_auth::EDIT, 'TTS/CONFIG');

        switch ($l_gets[C__GET__TTS__PAGE]) {
            case 1:
                $l_navbar = isys_component_template_navbar::getInstance();
                $l_navbar->set_active($l_edit_right, C__NAVBAR_BUTTON__EDIT)
                    ->set_active(false, C__NAVBAR_BUTTON__NEW)
                    ->set_active(false, C__NAVBAR_BUTTON__PURGE)
                    ->set_visible(true, C__NAVBAR_BUTTON__EDIT);

                if ($l_posts[C__GET__NAVMODE] == C__NAVMODE__EDIT) {
                    $l_navbar->set_active(true, C__NAVBAR_BUTTON__SAVE)
                        ->set_active(true, C__NAVBAR_BUTTON__CANCEL)
                        ->set_active(false, C__NAVBAR_BUTTON__EDIT)
                        ->set_visible(false, C__NAVBAR_BUTTON__EDIT);
                }

                $l_settings = $l_dao_tts->get_data()
                    ->get_row();

                $l_rules["C__MODULE__REQUEST_TRACKER_CONFIG__DB_ACTIVE"] = [
                    "p_arData"        => get_smarty_arr_YES_NO(),
                    "p_strSelectedID" => $l_settings["isys_tts_config__active"],
                    "p_strClass"      => 'input input-mini'
                ];
                $l_rules["C__MODULE__REQUEST_TRACKER_CONFIG__LINK"] = [
                    "p_strValue" => $l_settings["isys_tts_config__service_url"],
                    'p_strClass' => 'input-small'
                ];
                $l_rules["C__MODULE__REQUEST_TRACKER_CONFIG__USER"] = [
                    "p_strValue" => $l_settings["isys_tts_config__user"],
                    'p_strClass' => 'input-small'
                ];
                $l_rules["C__MODULE__REQUEST_TRACKER_CONFIG__PASS"] = [
                    "p_strValue" => $l_settings["isys_tts_config__pass"],
                    'p_strClass' => 'input-small'
                ];
                $l_rules['C__TTS__TYPE'] = [
                    'p_strSelectedID' => $l_settings['isys_tts_config__isys_tts_type__id'],
                    'p_strClass'      => 'input input-mini'
                ];

                isys_application::instance()->template->smarty_tom_add_rules("tom.content.bottom.content", $l_rules)
                    ->include_template('contentbottomcontent', 'modules/ticketing/tts_config.tpl');
        }
    }

    /**
     *
     * @param   isys_module_request &$p_req
     *
     * @return  isys_module_tts
     */
    public function init(isys_module_request $p_req)
    {
        return $this;
    }

    /**
     * Method for adding links to the "sticky" category bar.
     *
     * @param  isys_component_template $p_tpl
     * @param  string                  $p_tpl_var
     * @param  integer                 $p_obj_id
     * @param  integer                 $p_obj_type_id
     */
    public static function process_menu_tree_links($p_tpl, $p_tpl_var, $p_obj_id, $p_obj_type_id)
    {
        global $g_dirs, $g_comp_database;

        if (isys_auth_cmdb::instance()
            ->has_rights_in_obj_and_category(isys_auth::VIEW, $p_obj_id, 'C__CATG__VIRTUAL_TICKETS')) {
            $l_tts = new isys_tts_dao($g_comp_database);

            // Seems like we need this to prevent all the exceptions, when no connector is defined.
            if (count($l_tts->get_data()) > 0) {
                try {
                    if ($l_tts->get_config()) {
                        $l_link_data = [
                            'title' => isys_application::instance()->container->get('language')
                                ->get('LC__CMDB__CATG__VIRTUAL_TICKETS'),
                            'icon'  => $g_dirs['images'] . 'icons/silk/comments.png',
                            'link'  => "javascript:get_content_by_object('" . $p_obj_id . "', '" . C__CMDB__VIEW__LIST_CATEGORY . "', '" . defined_or_default('C__CATG__VIRTUAL_TICKETS') . "', '" .
                                C__CMDB__GET__CATG . "');"
                        ];

                        $p_tpl->append($p_tpl_var, ['ticket' => $l_link_data], true);

                    }
                } catch (isys_exception_general $e) {
                    ;
                }
            }
        }
    }
}

<?php

use idoit\Component\Helper\Purify;

/**
 * i-doit
 *
 * Builds the default navigation bar.
 *
 * @package     i-doit
 * @subpackage  Components_Template
 * @author      Dennis Stücken <dstuecken@i-doit.de>
 * @author      Leonard Fischer <lfischer@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_component_template_navbar
{
    /**
     * @var null
     */
    private static $m_instance = null;

    /**
     * @var bool
     */
    private static $m_locked = false;

    /**
     * Defined access keys.
     *
     * @var  array  Associative array
     */
    private $m_accesskey = [];

    /**
     * @var array
     */
    private $m_active = [];

    /**
     * @var array
     */
    private $m_add_onclick = [];

    /**
     * @var array
     */
    private $m_add_onclick_prepend = [];

    /**
     * Array with additional buttons, filled by signal-slot method.
     *
     * @var  array
     */
    private $m_additional_buttons = [];

    /**
     * Defined AJAX return.
     *
     * @var  string
     */
    private $m_ajax_return;

    /**
     * @var array
     */
    private $m_content = [];

    /**
     * @var string
     */
    private $m_hidden_field = '';

    /**
     * @var array
     */
    private $m_icon = [];

    /**
     * @var array
     */
    private $m_icon_inactive = [];

    /**
     * @var array
     */
    private $m_js_function = [];

    /**
     * @var array
     */
    private $m_js_onclick = [];

    /**
     * @var
     */
    private $m_nav_page_count;

    /**
     * @var array
     */
    private $m_navmode = [];

    /**
     * @var array
     */
    private $m_overlay = [];

    /**
     * @var int
     */
    private $m_page_results = 0;

    /**
     * Defined save mode ('ajax', 'quick', 'log').
     *
     * @var  string
     */
    private $m_save_mode = 'log';

    /**
     * @var array
     */
    private $m_selected = [];

    /**
     * @var int
     */
    private $m_strPageCount = 0;

    /**
     * @var array
     */
    private $m_title = [];

    /**
     * @var array
     */
    private $m_tooltip = [];

    /**
     * @var array
     */
    private $m_url = [];

    /**
     * @var array
     */
    private $m_visible = [];

    /**
     * @var array
     */
    private static $urlAdditions = [];

    /**
     * Sets lock for navbar.
     *
     * @param  boolean $p_value
     */
    public static function set_locked($p_value = false)
    {
        self::$m_locked = $p_value;
    }

    /**
     * Get instance method, for providing the singleton pattern.
     *
     * @static
     * @return  isys_component_template_navbar
     */
    public static function getInstance()
    {
        if (self::$m_instance === null) {
            self::$m_instance = new self;
        }

        return self::$m_instance;
    }

    /**
     * Appends an additional onclick event to the nav-button.
     *
     * @param   string $p_navButton
     * @param   string $p_js_onclick
     *
     * @return  isys_component_template_navbar
     */
    public function add_onclick($p_navButton, $p_js_onclick)
    {
        $this->m_add_onclick[$p_navButton] = $p_js_onclick;

        return $this;
    }

    /**
     * Prepends an additional onclick event to the nav-button.
     *
     * @param   string $p_navButton
     * @param   string $p_js_onclick
     *
     * @return  isys_component_template_navbar
     */
    public function add_onclick_prepend($p_navButton, $p_js_onclick)
    {
        $this->m_add_onclick_prepend[$p_navButton] = $p_js_onclick;

        return $this;
    }

    /**
     * Sets the number of rows of the table that could be shown in the content view.
     *
     * @global  integer $g_page_limit
     *
     * @param   integer $p_entries
     *
     * @return  isys_component_template_navbar
     */
    public function set_nav_page_count($p_entries = null)
    {
        $this->m_strPageCount = intval(ceil($p_entries / isys_glob_get_pagelimit()));
        $this->m_nav_page_count = $p_entries;

        return $this;
    }

    /**
     * Sets the title of the specified component.
     *
     * @param   string  $p_title Should be a language constant.
     * @param   integer $p_num
     *
     * @return  isys_component_template_navbar
     */
    public function set_title($p_title, $p_num)
    {
        if ($p_num > 0 && strlen($p_title) > 0 && isset($this->m_title[$p_num])) {
            $this->m_title[$p_num] = $p_title;
        }

        return $this;
    }

    /**
     * Sets the value of the specified component.
     *
     * @param   string  $p_value
     * @param   integer $p_num
     *
     * @return  isys_component_template_navbar
     */
    public function set_value($p_value, $p_num)
    {
        if ($p_num > 0 && strlen($p_value) > 0 && isset($this->m_navmode[$p_num])) {
            $this->m_navmode[$p_num] = $p_value;
        }

        return $this;
    }

    /**
     * Sets num_rows of the current page.
     *
     * @param   integer $p_results
     *
     * @return  isys_component_template_navbar
     */
    public function set_page_results($p_results)
    {
        $this->m_page_results = $p_results;

        return $this;
    }

    /**
     * Store a js-function to a specified navbar item.
     *
     * @param   string  $p_js
     * @param   integer $p_num
     *
     * @return  isys_component_template_navbar
     */
    public function set_js_function($p_js, $p_num)
    {
        $this->m_js_function[$p_num] = $p_js;

        return $this;
    }

    /**
     * Check if js function is set for specified navbar item.
     *
     * @param $p_num
     *
     * @return bool
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function is_js_function($p_num)
    {
        return isset($this->m_js_function[$p_num]);
    }

    /**
     * Define an additional JS-Onclick-Handler for a navbar control.
     *
     * @param   string  $p_js
     * @param   integer $p_num
     *
     * @return  isys_component_template_navbar
     */
    public function set_js_onclick($p_js, $p_num)
    {
        $this->m_js_onclick[$p_num] = $p_js;

        return $this;
    }

    /**
     * Check if js onclick is set for specified navbar item.
     *
     * @param $p_num
     *
     * @return bool
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function is_js_onclick($p_num)
    {
        return isset($this->m_js_onclick[$p_num]);
    }

    /**
     * Sets the tooltip of the specified component.
     *
     * @param   string  $p_tooltip
     * @param   integer $p_num
     *
     * @return  isys_component_template_navbar
     */
    public function set_tooltip($p_tooltip, $p_num)
    {
        if ($p_num > 0 && strlen($p_tooltip) > 0 && isset($this->m_tooltip[$p_num])) {
            $this->m_tooltip[$p_num] = $p_tooltip;
        }

        return $this;
    }

    /**
     * Method for setting an URL.
     *
     * @param   string $p_url
     * @param   string $p_navbar_item
     *
     * @return  isys_component_template_navbar
     */
    public function set_url($p_url, $p_navbar_item)
    {
        if (!empty($p_url) && $p_navbar_item) {
            $this->m_url[$p_navbar_item] = $p_url;
        }

        return $this;
    }

    /**
     * Method for setting content.
     *
     * @param   string $p_content
     * @param   string $p_navbar_item
     *
     * @return  isys_component_template_navbar
     */
    public function set_content($p_content, $p_navbar_item)
    {
        if (!empty($p_content) && $p_navbar_item) {
            $this->m_content[$p_navbar_item] = $p_content;
        }

        return $this;
    }

    /**
     * Sets 'accesskey' element for a component.
     *
     * @param string $p_accesskey    One char
     * @param int    $p_component_id Component identifier
     *
     * @return isys_component_template_navbar Returns itself.
     */
    public function set_accesskey($p_accesskey, $p_component_id)
    {
        $this->m_accesskey[$p_component_id] = $p_accesskey;

        return $this;
    }

    /**
     * Sets safe mode.
     *
     * @param string $p_save_mode 'ajax', 'quick' or 'log'
     *
     * @return isys_component_template_navbar Returns itself.
     */
    public function set_save_mode($p_save_mode)
    {
        $this->m_save_mode = $p_save_mode;

        return $this;
    }

    /**
     * Gets save mode
     * @return string
     */
    public function get_save_mode()
    {
        return $this->m_save_mode;
    }

    /**
     * Sets AJAX return.
     *
     * @param   string $p_ajax_return
     *
     * @return  isys_component_template_navbar Returns itself.
     */
    public function set_ajax_return($p_ajax_return)
    {
        $this->m_ajax_return = $p_ajax_return;

        return $this;
    }

    /**
     * Sets the specified component active or inactive.
     *
     * @param   boolean $p_active The new state of the component.
     * @param   integer $p_num    Component identifier.
     *
     * @return  isys_component_template_navbar
     */
    public function set_active($p_active, $p_num)
    {
        if ($p_num > 0 && is_bool($p_active) && isset($this->m_active[$p_num])) {
            $this->m_active[$p_num] = $p_active;

            if ($p_active) {
                $this->m_visible[$p_num] = true;
            }
        }

        return $this;
    }

    /**
     * Sets the specified component visible or invisible.
     *
     * @param   boolean $p_visible
     * @param   integer $p_num
     *
     * @return  isys_component_template_navbar
     */
    public function set_visible($p_visible, $p_num)
    {
        if ($p_num > 0 && is_bool($p_visible) && isset($this->m_visible[$p_num])) {
            $this->m_visible[$p_num] = $p_visible;
        }

        return $this;
    }

    /**
     * Sets the specified component on selected or not selected.
     *
     * @param   boolean $p_selected
     * @param   integer $p_num
     *
     * @return  isys_component_template_navbar
     */
    public function set_selected($p_selected, $p_num)
    {
        if ($p_num > 0 && is_bool($p_selected) && isset($this->m_selected[$p_num])) {
            $this->m_selected[$p_num] = $p_selected;
        }

        return $this;
    }

    /**
     * Sets the name of the icon of the specified component.
     *
     * @param   string  $p_icon
     * @param   integer $p_num
     *
     * @return  isys_component_template_navbar
     */
    public function set_icon($p_icon, $p_num)
    {
        if ($p_num > 0 && !empty($p_icon) && isset($this->m_icon[$p_num])) {
            $this->m_icon[$p_num] = $p_icon;
        }

        return $this;
    }

    /**
     * Sets the name of the inactive icon of the specified component.
     *
     * @param   string  $p_icon
     * @param   integer $p_num
     *
     * @return  isys_component_template_navbar
     */
    public function set_iconinactive($p_icon, $p_num)
    {
        if ($p_num > 0 && !empty($p_icon) && isset($this->m_icon_inactive[$p_num])) {
            $this->m_icon_inactive[$p_num] = $p_icon;
        }

        return $this;
    }

    /**
     * Sets the hidden field.
     *
     * @param   string $p_hidden_field
     *
     * @return  isys_component_template_navbar
     */
    public function set_hidden_field($p_hidden_field)
    {
        if (!empty($p_hidden_field)) {
            $this->m_hidden_field = $p_hidden_field;
        }

        return $this;
    }

    /**
     * Method for displaying the navbar.
     *
     * @global  integer $g_page_limit
     */
    public function show_navbar()
    {
        if (self::$m_locked === true) {
            return;
        }

        $l_pages = [];

        $this->assign_current_url();

        $l_pagestart = intval(isys_glob_get_param("navPageStart"));
        if ($l_pagestart < $this->m_nav_page_count) {
            $l_current_page = intval(ceil($l_pagestart / isys_glob_get_pagelimit())) + 1;
        } else {
            $l_current_page = 1;
        }

        $l_arConstantString = [
            "C__NAVBAR_BUTTON__SAVE"          => "x",
            "C__NAVBAR_BUTTON__CANCEL"        => "y",
            "C__NAVBAR_BUTTON__NEW"           => defined_or_default('C__RS__RIGHT_NEW', 4),
            "C__NAVBAR_BUTTON__EDIT"          => defined_or_default('C__RS__RIGHT_EDIT', 8),
            "C__NAVBAR_BUTTON__DUPLICATE"     => defined_or_default('C__RS__RIGHT_NEW', 4),
            "C__NAVBAR_BUTTON__ARCHIVE"       => defined_or_default('C__RS__RIGHT_ARCHIVE', 16),
            "C__NAVBAR_BUTTON__DELETE"        => defined_or_default('C__RS__RIGHT_DELETE', 32),
            "C__NAVBAR_BUTTON__PURGE"         => defined_or_default('C__RS__RIGHT_PURGE', 64),
            "C__NAVBAR_BUTTON__RECYCLE"       => defined_or_default('C__RS__RIGHT_RECYCLE', 128),
            "C__NAVBAR_BUTTON__QUICK_PURGE"   => defined_or_default('C__RS__RIGHT_PURGE', 64),
            "C__NAVBAR_BUTTON__PRINT"         => "a",
            "C__NAVBAR_BUTTON__COMPLETE"      => "e",
            "C__NAVBAR_BUTTON__EXPORT_AS_CSV" => "?"
        ];

        $l_navbar_buttons = [];

        foreach ($l_arConstantString as $l_strConst => $l_right) {
            if (defined($l_strConst)) {
                $l_navbar_buttons[$l_strConst] = $this->build_component(constant($l_strConst), $l_right);
            }
        }

        // Emitting a signal.
        isys_component_signalcollection::get_instance()
            ->emit('system.navbar.beforeAssignment');

        foreach ($this->m_additional_buttons as $l_identifier => $l_options) {
            $l_navbar_buttons[$l_identifier] = $this->build_component($l_identifier, true);
        }

        isys_application::instance()->template->assign('navbar_buttons', $l_navbar_buttons);

        for ($i = 1;$i <= $this->m_strPageCount;$i++) {
            $l_pages[($i - 1) * isys_glob_get_pagelimit()] = $i;
        }

        if (!is_null($l_pages) && $this->m_strPageCount > 0) {
            isys_application::instance()->template->assign("pages", $l_pages)
                ->assign("page_results", $this->m_page_results)
                ->assign("page_current", $l_current_page)
                ->assign("page_max", $this->m_strPageCount)
                ->assign("page_start", $l_pagestart)
                ->assign("page_info", str_replace([
                    "[{var1}]",
                    "[{var2}]"
                ], [
                    '<span id="page_counter">' . $l_current_page . '</span>',
                    $this->m_strPageCount
                ], isys_application::instance()->container->get('language')
                    ->get("LC__UNIVERSAL__PAGECOUNT")));
        }
    }

    /**
     * Method for appending a new button to the navbar. Can be used by modules via signal-slot!
     *
     * @param   string $p_title
     * @param   string $p_identifier
     * @param   array  $p_options
     *
     * @return  isys_component_template_navbar
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function append_button($p_title, $p_identifier, array $p_options = [])
    {
        $l_defaults = [
            'active'              => true,
            'visible'             => true,
            'tooltip'             => isys_tenantsettings::get('gui.empty_value', '-'),
            'icon'                => 'icons/silk/page_white.png',
            'icon_inactive'       => 'icons/silk/page_white.png',
            'url'                 => '#',
            'content'             => 'Content?',
            'add_onclick'         => null,
            'add_onclick_prepend' => null,
            'js_onclick'          => null,
            'js_function'         => null,
            'accesskey'           => null,
            'navmode'             => null
        ];

        $l_options = array_merge($l_defaults, $p_options);

        $this->m_title[$p_identifier] = $p_title;
        $this->m_active[$p_identifier] = $l_options['active'];
        $this->m_visible[$p_identifier] = $l_options['visible'];
        $this->m_tooltip[$p_identifier] = $l_options['tooltip'];
        $this->m_icon[$p_identifier] = $l_options['icon'];
        $this->m_icon_inactive[$p_identifier] = $l_options['icon_inactive'];
        $this->m_js_onclick[$p_identifier] = $l_options['js_onclick'];
        $this->m_accesskey[$p_identifier] = $l_options['accesskey'];

        // This variables should have a "navmode" key, but we have no "generic" navmode, so we use the identifier.
        $this->m_url[$p_identifier] = $l_options['url'];
        $this->m_content[$p_identifier] = $l_options['content'];
        $this->m_add_onclick_prepend[$p_identifier] = $l_options['add_onclick_prepend'];
        $this->m_add_onclick[$p_identifier] = $l_options['add_onclick'];
        $this->m_js_function[$p_identifier] = $l_options['js_function'];
        $this->m_navmode[$p_identifier] = $p_options['navmode'];

        $this->m_additional_buttons[$p_identifier] = $l_options;

        return $this;
    }

    /**
     * Hides all buttons.
     *
     * @param   array $p_exclude
     *
     * @return  isys_component_template_navbar
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function hide_all_buttons(array $p_exclude = [])
    {
        foreach ($this->m_visible as $l_key => $l_value) {
            if (!in_array($l_key, $p_exclude)) {
                $this->m_visible[$l_key] = false;
            }
        }

        return $this;
    }

    /**
     * Deactivates all buttons.
     *
     * @param   array $p_exclude
     *
     * @return  isys_component_template_navbar
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function deactivate_all_buttons(array $p_exclude = [])
    {
        foreach ($this->m_active as $l_key => $l_value) {
            if (!in_array($l_key, $p_exclude)) {
                $this->m_active[$l_key] = false;
            }
        }

        return $this;
    }

    /**
     * Sets overlay for the specified navbar button.
     *
     * Example:
     * $var = array(
     *    array(
     *       'title' => 'option1',
     *       'icon' => 'icon1.png',
     *       'onclick' => 'submit()',
     *       'href' => 'javascript:'
     *    ),
     *    array(
     *       'title' => 'option2',
     *       'icon' => 'icon2.png',
     *       'onclick' => 'submit()',
     *       'href' => 'javascript:'
     *    )
     * );
     * $object->set_overlay($var, 'button_ident');
     *
     * @param   array   $p_arr
     * @param   integer $p_navbar_button
     *
     * @return  isys_component_template_navbar
     */
    public function set_overlay(array $p_arr, $p_navbar_button)
    {
        $this->m_overlay[$p_navbar_button] = $p_arr;

        return $this;
    }

    /**
     * Returns if specified button is active
     *
     * @param $p_num
     *
     * @return bool
     * @author Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function is_active($p_num)
    {
        return $this->m_active[$p_num];
    }

    /**
     * Returns if specified button is visible
     *
     * @param $p_num
     *
     * @return mixed
     * @author Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function is_visible($p_num)
    {
        return $this->m_visible[$p_num];
    }

    /**
     * Builds one table cell of the table.
     *
     * @global  array                   $g_dirs
     * @global  integer                 $g_page_limit
     * @global  isys_component_database $g_comp_database
     *
     * @param   string                  $p_cNavButton
     * @param   integer                 $p_right
     *
     * @return  string
     * @author  Niclas Potthast <npotthast@i-doit.de>
     */
    protected function build_component($p_cNavButton, $p_right)
    {
        global $g_dirs, $g_comp_database;

        $l_nModuleID = defined_or_default('C__MODULE__CMDB');
        $l_bRight = true;

        $l_strValNavPageStart = isys_glob_get_param("navPageStart");
        //$l_strValFilter = isys_glob_get_param("filter");

        $l_strImagesDir = $g_dirs["images"];

        $getParams = Purify::purifyParams($_GET);

        if (is_numeric($p_right)) {
            // This is for menu item "Login credentials"
            if (isset($getParams[C__GET__SETTINGS_PAGE]) && $getParams[C__GET__SETTINGS_PAGE] == 'login' && $getParams[C__GET__MODULE_ID] == defined_or_default('C__MODULE__SYSTEM') &&
                $getParams[C__GET__MODULE_SUB_ID] == defined_or_default('C__MODULE__USER_SETTINGS')) {
                $l_bRight = true;
            } else {
                $l_bRight = false;

                if ($l_auth_instance = isys_module_manager::instance()
                    ->get_module_auth($l_nModuleID)) {
                    $l_bRight = $l_auth_instance->has_any_rights_in_module();
                }
            }
        }

        if (!$l_bRight) {
            $this->m_active[$p_cNavButton] = $l_bRight;
        }

        $l_strOut = "";
        $l_add = "";

        if ($this->m_visible[$p_cNavButton]) {
            $l_strClassSPAN = "navBarLink";
            $l_strJS = "";
            $l_strTitle = $this->m_title[$p_cNavButton];
            $l_strIcon = $this->m_icon[$p_cNavButton];
            $l_strIconInactive = $this->m_icon_inactive[$p_cNavButton];
            $l_cNavMode = $this->m_navmode[$p_cNavButton];
            $l_strTooltip = $this->m_tooltip[$p_cNavButton];

            switch ($p_cNavButton) {
                case C__NAVBAR_BUTTON__BACK:
                    $l_strValNavPageStart = $l_strValNavPageStart - isys_glob_get_pagelimit();
                    if ($l_strValNavPageStart < 0) {
                        $this->m_active[$p_cNavButton] = false;
                    } else {
                        $this->m_active[$p_cNavButton] = true;
                    }
                    break;

                case C__NAVBAR_BUTTON__FORWARD:
                    $l_strValNavPageStart = $l_strValNavPageStart + isys_glob_get_pagelimit();
                    if ($l_strValNavPageStart >= $this->m_nav_page_count) {
                        $this->m_active[$p_cNavButton] = false;
                    } else {
                        $this->m_active[$p_cNavButton] = true;
                    }
                    break;
            }

            $l_element_class = 'navbar_item';
            $l_element_id = $l_element_class . '_';
            $clickEvent = '';

            if (!$this->m_active[$p_cNavButton]) {
                $l_strClassSPAN = 'navBarInactiveText';
                $l_element_class .= '_inactive';
                $l_strIcon = $l_strIconInactive;

                if (strpos($l_strIconInactive, '/') !== 0) {
                    $l_strIcon = $l_strImagesDir . $l_strIconInactive;
                }
            } else {
                if (strpos($l_strIcon, '/') !== 0) {
                    $l_strIcon = $l_strImagesDir . $l_strIcon;
                }

                switch ($l_cNavMode) {
                    // @todo  Check if this is still used. If not: remove in i-doit 1.12
                    case C__NAVMODE__FORWARD:
                        $l_element_id .= 'C__NAVMODE__FORWARD';

                    // @todo  Check if this is still used. If not: remove in i-doit 1.12
                    // no break
                    case C__NAVMODE__BACK:
                        if ($l_cNavMode == C__NAVMODE__BACK) {
                            $l_element_id .= 'C__NAVMODE__BACK';
                        }

                        $l_strJS = "onclick=\"" . $this->m_add_onclick_prepend[$p_cNavButton] .
                            "document.isys_form.navMode.value='$l_cNavMode';change_page('{$l_strValNavPageStart}'";

                        if (isset($this->m_url[$l_cNavMode])) {
                            $l_strJS .= ", '" . $this->m_url[$l_cNavMode] . "'";
                        } else {
                            $l_strJS .= ", false";
                        }

                        // $l_strJS .= ",'','ResponseContainer'";
                        if (isset($this->m_content[$l_cNavMode])) {
                            $l_strJS .= ", '" . $this->m_content[$l_cNavMode] . "'";
                        } else {
                            $l_strJS .= ", 'main_content'";
                        }

                        $l_strJS .= ", 'post');\"";

                        $l_strJS .= $this->m_add_onclick[$p_cNavButton];

                        break;
                    case C__NAVMODE__PRINT:
                        $l_element_id .= 'C__NAVMODE__PRINT';

                        $l_dao = new isys_cmdb_dao($g_comp_database);
                        $l_cat_spec = "";

                        if ($getParams[C__CMDB__GET__CATG]) {
                            $l_res = $l_dao->get_catg_by_const($getParams[C__CMDB__GET__CATG]);
                            $l_cat_spec = "g";
                        } else {
                            if ($getParams[C__CMDB__GET__CATS]) {
                                $l_res = $l_dao->get_cats_by_const($getParams[C__CMDB__GET__CATS]);
                                $l_cat_spec = "s";
                            } else {
                                if ($getParams[C__CMDB__GET__OBJECTTYPE]) {
                                } else {
                                    return;
                                }
                            }
                        }

                        if ($l_cat_spec != "" && $getParams[C__CMDB__GET__CATG] != defined_or_default('C__CATG__OVERVIEW')) {
                            $l_row = $l_res->get_row();

                            if (!class_exists($l_row['isysgui_cat' . $l_cat_spec . '__class_name'])) {
                                return;
                            }

                            $l_cat = new $l_row['isysgui_cat' . $l_cat_spec . '__class_name']($g_comp_database);

                            if (!empty($getParams[C__CMDB__GET__CATG_CUSTOM])) {
                                if (method_exists($l_cat, 'set_catg_custom_id')) {
                                    $l_cat->set_catg_custom_id($getParams[C__CMDB__GET__CATG_CUSTOM]);
                                }
                            }

                            $l_catDataInformation = $l_cat->get_properties();

                            unset($l_dao, $l_cat);
                            if (!is_array($l_catDataInformation) || count($l_catDataInformation) == 0) {
                                return '';
                            }
                        }

                        $l_strLoc = C__GET__MODULE_ID . "=" . defined_or_default('C__MODULE__EXPORT');
                        if ($getParams[C__CMDB__GET__OBJECTTYPE]) {
                            $l_strLoc .= "&" . C__CMDB__GET__OBJECTTYPE . "=" . $getParams[C__CMDB__GET__OBJECTTYPE];
                            $l_strLoc .= "&navPageStart='+get_current_page()+'";
                            $l_strLoc .= "&'+get_current_filter()+'";

                            if (!$getParams[C__CMDB__GET__CATG] && !$getParams[C__CMDB__GET__CATS]) {
                                $l_strLoc .= "&data='+list_selection()+'";
                            }
                        }

                        if ($getParams[C__CMDB__GET__OBJECT]) {
                            $l_strLoc .= "&" . C__CMDB__GET__OBJECT . "=" . $getParams[C__CMDB__GET__OBJECT];
                        }

                        if ($getParams[C__CMDB__GET__CATG]) {
                            $l_strLoc .= "&" . C__CMDB__GET__CATG . "=" . $getParams[C__CMDB__GET__CATG];
                        }

                        if ($getParams[C__CMDB__GET__CATS]) {
                            $l_strLoc .= "&" . C__CMDB__GET__CATS . "=" . $getParams[C__CMDB__GET__CATS];
                        }

                        if ($getParams[C__CMDB__GET__CATG_CUSTOM]) {
                            $l_strLoc .= "&" . C__CMDB__GET__CATG_CUSTOM . "=" . $getParams[C__CMDB__GET__CATG_CUSTOM];
                        }

                        $isBrowserFirefox = strpos(isys_application::instance()->container->request->headers->get('User-Agent'), 'Firefox') !== false;

                        $l_strJS = "onclick=\"isys_popup_open('" . isys_glob_build_url($l_strLoc) .
                            "&request=cmdb&ajax=1','isysPopup',1,{dependant:'yes',width:'950',height:'650',menubar:'yes',resizable:'yes',scrollbars:'yes',status:'no',toolbar:'no',location:'no'});" .
                            ($isBrowserFirefox ? "window.idoit.Notify.warning('" . isys_application::instance()->container->get('language')
                                    ->get('LC__NAVIGATION__NAVBAR__PRINTPREVIEW_FIREFOX_STRIPPED_HTML') . "');" : "") . "\"";

                        break;
                    case C__NAVMODE__SAVE:
                        $l_element_id .= 'C__NAVMODE__SAVE';
                        $l_onclick = null;
                        if (isset($this->m_js_onclick[$p_cNavButton])) {
                            $l_onclick = $this->m_js_onclick[$p_cNavButton];
                        } else {
                            switch ($this->m_save_mode) {
                                case 'ajax':
                                    $l_onclick = "document.isys_form.navMode.value='" . C__NAVMODE__SAVE . "'; save_via_ajax('" . $this->m_ajax_return . "');";
                                    break;
                                case 'quick':
                                    $l_onclick = "document.isys_form.navMode.value='" . C__NAVMODE__SAVE . "'; form_submit();";
                                    break;
                                case 'log':
                                    $l_onclick = "get_commentary(); return false;";
                                    break;
                                case 'formsubmit':
                                    $l_onclick = "document.isys_form.navMode.value='" . C__NAVMODE__SAVE . "'; $('isys_form').submit()";
                                    break;
                            }
                        }
                        $l_strJS = 'onclick="' . $l_onclick . '"';
                        break;
                    case C__NAVMODE__CANCEL:
                        $l_element_id .= 'C__NAVMODE__CANCEL';
                        $l_onclick = null;
                        if (isset($this->m_js_onclick[$p_cNavButton])) {
                            $l_onclick = $this->m_js_onclick[$p_cNavButton];
                        } else {
                            $l_onclick = "document.isys_form.navMode.value='" . C__NAVMODE__CANCEL . "'; form_submit();";
                        }
                        $l_strJS = 'onclick="' . $l_onclick . '"';
                        break;
                    case C__NAVMODE__NEW:
                        $l_element_id .= 'C__NAVMODE__NEW';

                        $l_strJS = "style=\"\" " . "onclick=\"" . (empty($this->m_js_onclick[$p_cNavButton]) ? $this->m_add_onclick_prepend[$p_cNavButton] .
                                "document.isys_form.navMode.value='$l_cNavMode'; document.isys_form.submit();" .
                                $this->m_add_onclick[$p_cNavButton] : $this->m_js_onclick[$p_cNavButton]) . "\"";

                        if (defined("C__MODULE__TEMPLATES") && isset($getParams[C__CMDB__GET__OBJECTTYPE]) && !isset($getParams[C__CMDB__GET__OBJECT])) {
                            $l_add .= "<a style=\"border-left:1px dotted #888;padding-left:3px;\" class=" . $l_element_class .
                                " href=\"javascript:\" onclick=\"new Effect.toggle(this.next('div'), 'slide', {duration:0.2});\">" . "<img src=\"" . $g_dirs['images'] .
                                "icons/silk/bullet_arrow_down.png\" />" . "</a>" . "<div id=\"new_overlay\" style=\"display:none;\">" . "<ul>" . "<li>" .
                                "<a href=\"javascript:\" onclick=\"" . "document.isys_form.navMode.value='$l_cNavMode'; document.isys_form.submit();" . "\">" . "<img src=\"" .
                                $l_strIcon . "\" width=\"15px\" height=\"15px\" alt=\"\" /> " . isys_application::instance()->container->get('language')
                                    ->get("LC__TEMPLATES__NEW_OBJECT") . "</a>" . "</li>" . "<li>" . "<a href=\"?" . C__GET__MODULE_ID . "=" . C__MODULE__TEMPLATES . "&" .
                                C__GET__SETTINGS_PAGE . "=3&" . C__CMDB__GET__OBJECTTYPE . "=" . $getParams[C__CMDB__GET__OBJECTTYPE] . "\">" . "<img src=\"" . $l_strImagesDir .
                                $this->m_icon[C__NAVBAR_BUTTON__EDIT] . "\" width=\"15px\" height=\"15px\" alt=\"\" /> " .
                                isys_application::instance()->container->get('language')
                                    ->get("LC__TEMPLATES__NEW_OBJECT_FROM_TEMPLATE") . "</a>" . "</li>" . "<li>" . "<a href=\"javascript:\" onclick=\"" .
                                "document.isys_form.template.value='1'; document.isys_form.navMode.value='$l_cNavMode'; document.isys_form.submit();" . "\">" . "<img src=\"" .
                                $l_strImagesDir . $this->m_icon[C__NAVBAR_BUTTON__DUPLICATE] . "\" width=\"15px\" height=\"15px\" alt=\"\" /> " .
                                isys_application::instance()->container->get('language')
                                    ->get("LC__TEMPLATES__NEW_TEMPLATE") . "</a>" . "</li>" . "</ul>" . "</div>";
                        }

                        if (!empty($this->m_hidden_field) && $this->m_hidden_field != C__POST__POPUP_RECEIVER) {
                            $l_add .= "<input type=\"hidden\" id=\"" . $this->m_hidden_field . "\" value=\"\" name=\"" . $this->m_hidden_field . "\">";
                        }

                        break;
                    case C__NAVMODE__EDIT:
                        $l_element_id .= 'C__NAVMODE__EDIT';

                        if (empty($this->m_js_onclick[$p_cNavButton])) {
                            $l_strJS = "onclick=\"" . $this->m_add_onclick_prepend[$p_cNavButton] .
                                "document.isys_form.sort.value=''; document.isys_form.navMode.value='$l_cNavMode'; form_submit(); " . $this->m_add_onclick[$p_cNavButton] .
                                "\"";
                        } else {
                            $l_strJS = "onclick=\"" . $this->m_js_onclick[$p_cNavButton] . "\"";
                        }

                        break;
                    case C__NAVMODE__ARCHIVE:
                        $l_element_id .= 'C__NAVMODE__ARCHIVE';
                        // no break
                    case C__NAVMODE__DELETE:
                        if ($l_cNavMode == C__NAVMODE__DELETE) {
                            $l_element_id .= 'C__NAVMODE__DELETE';
                        }
                        // no break
                    case C__NAVMODE__RECYCLE:
                        if ($l_cNavMode == C__NAVMODE__RECYCLE) {
                            $l_element_id .= 'C__NAVMODE__RECYCLE';
                        }
                        // no break
                    case C__NAVMODE__COMPLETE:
                        if ($l_cNavMode == C__NAVMODE__COMPLETE) {
                            $l_element_id .= 'C__NAVMODE__COMPLETE';
                        }
                        $clickEvent = (empty($this->m_js_onclick[$p_cNavButton]) ? $this->m_add_onclick_prepend[$p_cNavButton] .
                            "document.isys_form.navMode.value='$l_cNavMode'; form_submit(null, null, null, null, null, get_listSelection4Submit());" .
                            $this->m_add_onclick[$p_cNavButton] : $this->m_js_onclick[$p_cNavButton]);
                        break;
                    case C__NAVMODE__QUICK_PURGE:
                    case C__NAVMODE__PURGE:
                        if ($l_cNavMode == C__NAVMODE__QUICK_PURGE) {
                            $l_element_id .= 'C__NAVMODE__QUICK_PURGE';
                        } else {
                            $l_element_id .= 'C__NAVMODE__PURGE';
                        }

                        $l_submit = "form_submit(null, null, null, null, null, get_listSelection4Submit());";

                        // We only want the new "confirmation" for objects, so we look if catgID and catsID are empty.
                        if ($getParams[C__CMDB__GET__VIEWMODE] == C__CMDB__VIEW__LIST_OBJECT ||
                            empty($getParams[C__CMDB__GET__CATG]) && empty($getParams[C__CMDB__GET__CATS]) && empty($getParams[C__GET__MODULE_ID])) {
                            $clickEvent = (empty($this->m_js_onclick[$p_cNavButton]) ? $this->m_add_onclick_prepend[$p_cNavButton] . "purge_object('" . $l_cNavMode .
                                "', '1', '" . isys_application::instance()->container->get('language')
                                    ->get('LC_UNIVERSAL__PURGE_NOTIFICATION') . "');" . $this->m_add_onclick[$p_cNavButton] : $this->m_js_onclick[$p_cNavButton]);
                        } else {
                            $clickEvent = (empty($this->m_js_onclick[$p_cNavButton]) ? $this->m_add_onclick_prepend[$p_cNavButton] . "document.isys_form.navMode.value='" .
                                $l_cNavMode . "'; " . $l_submit . $this->m_add_onclick[$p_cNavButton] : $this->m_js_onclick[$p_cNavButton]);
                        }
                        break;

                    case C__NAVMODE__DUPLICATE:
                        $l_element_id .= 'C__NAVMODE__DUPLICATE';

                        $l_strJS = (!empty($this->m_js_onclick[$p_cNavButton])) ? $this->m_js_onclick[$p_cNavButton] : "onclick=\"if (list_selection().length === 0) {idoit.Notify.warning('" .
                            isys_application::instance()->container->get('language')
                                ->get('LC__DUPLICATE__NO_OBJECTS_SELECTED') . "', {life:5});} else {get_popup('duplicate', 'editMode=1&" . C__CMDB__GET__OBJECTTYPE . "=" .
                            $getParams[C__CMDB__GET__OBJECTTYPE] . "', '720', '480');}\"";
                        break;

                    default:
                        $l_element_id .= $l_cNavMode;
                        $l_strJS = "onclick=\"" . (empty($this->m_js_onclick[$p_cNavButton]) ? $this->m_add_onclick_prepend[$p_cNavButton] .
                                empty($this->m_url[$p_cNavButton])?
                                    "document.isys_form.navMode.value='$l_cNavMode'; document.isys_form.submit();" . $this->m_add_onclick[$p_cNavButton] :
                                    "window.location.href = '{$this->m_url[$p_cNavButton]}'"
                                : $this->m_js_onclick[$p_cNavButton]) . "\"";
                        break;
                }

                if ($this->m_js_function[$l_cNavMode] != null) {
                    $l_strJS = $this->m_js_function[$l_cNavMode];
                    $clickEvent = '';
                }

                if (isset($this->m_overlay[$p_cNavButton])) {
                    $l_add .= '<a style="border-left:1px dotted #888; margin-left:0; margin-right:5px;" class="' . $l_element_class . '" href="javascript:" onclick="new Effect.toggle(this.next(\'div.new_overlay\'), \'slide\', {duration:0.2});">
						<img src="' . $g_dirs['images'] . 'icons/silk/bullet_arrow_down.png" />
						</a>
						<div id="new_overlay_' . $p_cNavButton . '" class="new_overlay" style="display:none; position:absolute; margin-top:30px; z-index:900">
						<ul>';

                    foreach ($this->m_overlay[$p_cNavButton] as $l_li) {
                        $l_add .= '<li><a href="' . $l_li['href'] . '" id="' . $l_li['id'] . '" onclick="' . $l_li['onclick'] . '">
							<img class="vam" src="' . $l_strImagesDir . $l_li['icon'] . '" /> <span>' .
                            isys_application::instance()->container->get('language')
                                ->get($l_li['title']) . '
							</span></a></li>';
                    }

                    $l_add .= "</ul></div>" . "<script type=\"text/javascript\">" . "var new_left_" . $p_cNavButton . " = ($('new_overlay_" . $p_cNavButton .
                        "').previous().previous().offsetLeft);" . "$('new_overlay_" . $p_cNavButton . "').setStyle({left: new_left_" . $p_cNavButton . "+'px'});" .
                        "</script>";
                }
            }

            // Tooltip:
            $l_strTooltip = str_replace("\"", "'", isys_application::instance()->container->get('language')
                ->get($l_strTooltip));

            // Title:
            $l_strTitle = str_replace("\"", "'", isys_application::instance()->container->get('language')
                ->get($l_strTitle));

            // Access key:
            $l_access_key = null;
            if (isset($this->m_accesskey[$p_cNavButton])) {
                $l_access_key = ' accesskey="' . $this->m_accesskey[$p_cNavButton] . '"';

                // Append hint to tooltip:
                $l_strTooltip .= ' [' . strtoupper($this->m_accesskey[$p_cNavButton]) . ']';
            }

            $l_strOut .= "<div id=\"" . $l_element_id . "\" title=\"" . $l_strTooltip . "\" data-navmode=\"" . $l_cNavMode . "\" class=\"" . $l_element_class . "\" " .
                $l_strJS . $l_access_key . ">" . "<img src=\"" . $l_strIcon . "\" width=\"15px\" height=\"15px\" alt=\"\" />" . "<span class=\"" . $l_strClassSPAN .
                "\" style=\"vertical-align:middle\">&nbsp;" . $l_strTitle . "&nbsp;</span>" . "</div>" . $l_add;

            if ($clickEvent !== '') {
                $l_strOut .= "<script type=\"text/javascript\">$('" . $l_element_id . "').on('click', function(){
                    if(!this.hasClassName('navbar_item_inactive'))
                    {
                        if (this.down('img')) {
                            this.down('img').setAttribute('src', '" . $l_strImagesDir . "ajax-loading.gif');
                        }
                        this.toggleClassName('navbar_item_inactive', 'navbar_item');
                        " . $clickEvent . "
                    }
                });</script>";
            }
        }

        return $l_strOut;
    }

    /**
     * Private clone method, to prevent the singleton instance.
     *
     * @author  Niclas Potthast <npotthast@i-doit.de>
     */
    private function __clone()
    {
    }

    /**
     * Merge Get parameters with Server QUERY_STRING
     * @return string
     */
    private function getQueryString()
    {
        $serverQueryString = explode('&', $_SERVER['QUERY_STRING']);
        array_walk($serverQueryString, function (&$val) {
            [$key, $value] = explode('=', $val);
            $val = $key . '=' . Purify::purifyValue($value);
        });

        $get = array_merge(Purify::purifyParams($_GET), self::$urlAdditions);
        $get = array_map(function ($item, $key) {
            return $key . '=' . $item;
        }, $get, array_keys($get));

        return implode('&', array_filter(array_replace($serverQueryString, $get), function ($key) {
            return (strpos($key, 'ajax=') === false && strpos($key, 'call=') === false);
        }));
    }

    private function assign_current_url()
    {
        $currentUrl = $this->getQueryString();

        if (defined('C__MODULE__NAGIOS') && isys_module_manager::instance()
                ->is_active('nagios') &&
            is_value_in_constants($_GET[C__CMDB__GET__OBJECTTYPE], ['C__OBJTYPE__NAGIOS_SERVICE', 'C__OBJTYPE__NAGIOS_SERVICE_TPL', 'C__OBJTYPE__NAGIOS_HOST_TPL'])) {
            $currentUrl = C__GET__MODULE_ID . '=' . constant('C__MODULE__NAGIOS');
        }

        try {
            $session = isys_application::instance()->container->get('session');
        } catch (Exception $e) {
            global $g_comp_session;
            $session = $g_comp_session;
        }
        $currentTenantId = (int)($session->get_mandator_id());
        if ((strlen($currentUrl) !== 0) && (stripos($currentUrl, C__CMDB__GET__TENANT) === false)) {
            $currentUrl .= '&' . C__CMDB__GET__TENANT . '=' . $currentTenantId;
        } elseif (stripos($currentUrl, C__CMDB__GET__TENANT) === false) {
            $currentUrl .= '?' . C__CMDB__GET__TENANT . '=' . $currentTenantId;
        }

        isys_application::instance()->template->assign('current_link', $currentUrl);
    }

    /**
     * Adds additional parameters to url
     *
     * @param array $parameters
     */
    public static function addParametersToCurrentUrl(array $parameters)
    {
        self::$urlAdditions = $parameters;
    }

    /**
     * Private constructor, only callable by the getInstance() method.
     *
     * @global  boolean $g_bDefaultTooltips
     * @global  array   $g_config
     * @author  Niclas Potthast <npotthast@i-doit.de>
     */
    private function __construct()
    {
        global $g_bDefaultTooltips;

        // Default values.
        $this->m_title = [
            C__NAVBAR_BUTTON__SAVE          => "LC__NAVIGATION__NAVBAR__SAVE",
            C__NAVBAR_BUTTON__CANCEL        => "LC__NAVIGATION__NAVBAR__CANCEL",
            C__NAVBAR_BUTTON__NEW           => "LC__NAVIGATION__NAVBAR__NEW",
            C__NAVBAR_BUTTON__EDIT          => "LC__NAVIGATION__NAVBAR__EDIT",
            C__NAVBAR_BUTTON__DUPLICATE     => "LC__NAVIGATION__NAVBAR__DUPLICATE",
            C__NAVBAR_BUTTON__ARCHIVE       => "LC__NAVIGATION__NAVBAR__ARCHIVE",
            C__NAVBAR_BUTTON__DELETE        => "LC__NAVIGATION__NAVBAR__DELETE",
            C__NAVBAR_BUTTON__PURGE         => "LC__NAVIGATION__NAVBAR__PURGE",
            C__NAVBAR_BUTTON__RECYCLE       => "LC__NAVIGATION__NAVBAR__RECYCLE",
            C__NAVBAR_BUTTON__BACK          => "LC__NAVIGATION__NAVBAR__PREV",
            C__NAVBAR_BUTTON__UP            => "LC__NAVIGATION__NAVBAR__UP",
            C__NAVBAR_BUTTON__FORWARD       => "LC__NAVIGATION__NAVBAR__NEXT",
            C__NAVBAR_BUTTON__PRINT         => "LC__NAVIGATION__NAVBAR__PRINTPREVIEW",
            C__NAVBAR_BUTTON__COMPLETE      => "LC__NAVIGATION__NAVBAR__COMPLETE",
            C__NAVBAR_BUTTON__QUICK_PURGE   => "LC__NAVIGATION__NAVBAR__QUICK_PURGE",
            C__NAVBAR_BUTTON__EXPORT_AS_CSV => "LC__NAVIGATION__NAVBAR__EXPORT_AS_CSV"
        ];

        $this->m_navmode = [
            C__NAVBAR_BUTTON__SAVE          => C__NAVMODE__SAVE,
            C__NAVBAR_BUTTON__CANCEL        => C__NAVMODE__CANCEL,
            C__NAVBAR_BUTTON__NEW           => C__NAVMODE__NEW,
            C__NAVBAR_BUTTON__EDIT          => C__NAVMODE__EDIT,
            C__NAVBAR_BUTTON__DUPLICATE     => C__NAVMODE__DUPLICATE,
            C__NAVBAR_BUTTON__ARCHIVE       => C__NAVMODE__ARCHIVE,
            C__NAVBAR_BUTTON__DELETE        => C__NAVMODE__DELETE,
            C__NAVBAR_BUTTON__PURGE         => C__NAVMODE__PURGE,
            C__NAVBAR_BUTTON__RECYCLE       => C__NAVMODE__RECYCLE,
            C__NAVBAR_BUTTON__BACK          => C__NAVMODE__BACK,
            C__NAVBAR_BUTTON__UP            => C__NAVMODE__UP,
            C__NAVBAR_BUTTON__FORWARD       => C__NAVMODE__FORWARD,
            C__NAVBAR_BUTTON__PRINT         => C__NAVMODE__PRINT,
            C__NAVBAR_BUTTON__COMPLETE      => C__NAVMODE__COMPLETE,
            C__NAVBAR_BUTTON__QUICK_PURGE   => C__NAVMODE__QUICK_PURGE,
            C__NAVBAR_BUTTON__EXPORT_AS_CSV => C__NAVMODE__EXPORT_CSV
        ];

        if ($g_bDefaultTooltips) {
            $this->m_tooltip = [
                C__NAVBAR_BUTTON__SAVE          => "LC__NAVIGATION__NAVBAR__SAVE_TOOLTIP",
                C__NAVBAR_BUTTON__CANCEL        => "LC__NAVIGATION__NAVBAR__CANCEL_TOOLTIP",
                C__NAVBAR_BUTTON__NEW           => "LC__NAVIGATION__NAVBAR__NEW_TOOLTIP",
                C__NAVBAR_BUTTON__EDIT          => "LC__NAVIGATION__NAVBAR__EDIT_TOOLTIP",
                C__NAVBAR_BUTTON__DUPLICATE     => "LC__NAVIGATION__NAVBAR__DUPLICATE_TOOLTIP",
                C__NAVBAR_BUTTON__ARCHIVE       => "LC__NAVIGATION__NAVBAR__ARCHIVE_TOOLTIP",
                C__NAVBAR_BUTTON__DELETE        => "LC__NAVIGATION__NAVBAR__DELETE_TOOLTIP",
                C__NAVBAR_BUTTON__PURGE         => "LC__NAVIGATION__NAVBAR__PURGE_TOOLTIP",
                C__NAVBAR_BUTTON__RECYCLE       => "LC__NAVIGATION__NAVBAR__RECYCLE_TOOLTIP",
                C__NAVBAR_BUTTON__BACK          => "LC__NAVIGATION__NAVBAR__PREV_TOOLTIP",
                C__NAVBAR_BUTTON__UP            => "LC__NAVIGATION__NAVBAR__UP_TOOLTIP",
                C__NAVBAR_BUTTON__FORWARD       => "LC__NAVIGATION__NAVBAR__NEXT_TOOLTIP",
                C__NAVBAR_BUTTON__PRINT         => "LC__NAVIGATION__NAVBAR__PRINTPREVIEW_TOOLTIP",
                C__NAVBAR_BUTTON__COMPLETE      => "LC__NAVIGATION__NAVBAR__COMPLETE_TOOLTIP",
                C__NAVBAR_BUTTON__QUICK_PURGE   => "LC__NAVIGATION__NAVBAR__QUICK_PURGE_TOOLTIP",
                C__NAVBAR_BUTTON__EXPORT_AS_CSV => "LC__NAVIGATION__NAVBAR__EXPORT_AS_CSV_TOOLTIP"
            ];
        } else {
            $this->m_tooltip = [
                C__NAVBAR_BUTTON__SAVE          => "",
                C__NAVBAR_BUTTON__CANCEL        => "",
                C__NAVBAR_BUTTON__NEW           => "",
                C__NAVBAR_BUTTON__EDIT          => "",
                C__NAVBAR_BUTTON__DUPLICATE     => "",
                C__NAVBAR_BUTTON__ARCHIVE       => "",
                C__NAVBAR_BUTTON__DELETE        => "",
                C__NAVBAR_BUTTON__PURGE         => "",
                C__NAVBAR_BUTTON__RECYCLE       => "",
                C__NAVBAR_BUTTON__BACK          => "",
                C__NAVBAR_BUTTON__UP            => "",
                C__NAVBAR_BUTTON__FORWARD       => "",
                C__NAVBAR_BUTTON__PRINT         => "",
                C__NAVBAR_BUTTON__COMPLETE      => "",
                C__NAVBAR_BUTTON__QUICK_PURGE   => "",
                C__NAVBAR_BUTTON__EXPORT_AS_CSV => ""
            ];
        }

        $this->m_active = [
            C__NAVBAR_BUTTON__SAVE          => false,
            C__NAVBAR_BUTTON__CANCEL        => false,
            C__NAVBAR_BUTTON__NEW           => false,
            C__NAVBAR_BUTTON__EDIT          => false,
            C__NAVBAR_BUTTON__DUPLICATE     => false,
            C__NAVBAR_BUTTON__ARCHIVE       => false,
            C__NAVBAR_BUTTON__DELETE        => false,
            C__NAVBAR_BUTTON__PURGE         => false,
            C__NAVBAR_BUTTON__RECYCLE       => false,
            C__NAVBAR_BUTTON__BACK          => false,
            C__NAVBAR_BUTTON__UP            => false,
            C__NAVBAR_BUTTON__FORWARD       => false,
            C__NAVBAR_BUTTON__PRINT         => false,
            C__NAVBAR_BUTTON__COMPLETE      => false,
            C__NAVBAR_BUTTON__QUICK_PURGE   => false,
            C__NAVBAR_BUTTON__EXPORT_AS_CSV => false
        ];

        $this->m_selected = [
            C__NAVBAR_BUTTON__SAVE          => false,
            C__NAVBAR_BUTTON__CANCEL        => false,
            C__NAVBAR_BUTTON__NEW           => false,
            C__NAVBAR_BUTTON__EDIT          => false,
            C__NAVBAR_BUTTON__DUPLICATE     => false,
            C__NAVBAR_BUTTON__ARCHIVE       => false,
            C__NAVBAR_BUTTON__DELETE        => false,
            C__NAVBAR_BUTTON__PURGE         => false,
            C__NAVBAR_BUTTON__RECYCLE       => false,
            C__NAVBAR_BUTTON__BACK          => false,
            C__NAVBAR_BUTTON__UP            => false,
            C__NAVBAR_BUTTON__FORWARD       => false,
            C__NAVBAR_BUTTON__PRINT         => false,
            C__NAVBAR_BUTTON__COMPLETE      => false,
            C__NAVBAR_BUTTON__QUICK_PURGE   => false,
            C__NAVBAR_BUTTON__EXPORT_AS_CSV => false
        ];

        $this->m_icon = [
            C__NAVBAR_BUTTON__SAVE          => "icons/silk/page_save.png",
            C__NAVBAR_BUTTON__CANCEL        => "icons/silk/page_red.png",
            C__NAVBAR_BUTTON__NEW           => "icons/silk/page_add.png",
            C__NAVBAR_BUTTON__EDIT          => "icons/silk/page_edit.png",
            C__NAVBAR_BUTTON__DUPLICATE     => "icons/silk/page_copy.png",
            C__NAVBAR_BUTTON__ARCHIVE       => "icons/silk/page_delete.png",
            C__NAVBAR_BUTTON__DELETE        => "icons/silk/page_delete.png",
            C__NAVBAR_BUTTON__PURGE         => "icons/silk/page_delete.png",
            C__NAVBAR_BUTTON__RECYCLE       => "icons/silk/page_refresh.png",
            C__NAVBAR_BUTTON__BACK          => "icons/silk/resultset_previous.png",
            C__NAVBAR_BUTTON__UP            => "icons/navbar/up_icon.png",
            C__NAVBAR_BUTTON__FORWARD       => "icons/silk/resultset_next.png",
            C__NAVBAR_BUTTON__PRINT         => "icons/silk/printer.png",
            C__NAVBAR_BUTTON__COMPLETE      => "icons/silk/tick.png",
            C__NAVBAR_BUTTON__QUICK_PURGE   => "icons/silk/page_delete.png",
            C__NAVBAR_BUTTON__EXPORT_AS_CSV => "icons/silk/page_white_excel.png"
        ];

        $this->m_icon_inactive = [
            C__NAVBAR_BUTTON__SAVE          => "icons/silk/page_save.png",
            C__NAVBAR_BUTTON__CANCEL        => "icons/silk/page_red.png",
            C__NAVBAR_BUTTON__NEW           => "icons/silk/page_add.png",
            C__NAVBAR_BUTTON__EDIT          => "icons/silk/page_edit.png",
            C__NAVBAR_BUTTON__DUPLICATE     => "icons/silk/page_copy.png",
            C__NAVBAR_BUTTON__ARCHIVE       => "icons/silk/page_delete.png",
            C__NAVBAR_BUTTON__DELETE        => "icons/silk/page_delete.png",
            C__NAVBAR_BUTTON__PURGE         => "icons/silk/page_delete.png",
            C__NAVBAR_BUTTON__RECYCLE       => "icons/silk/page_refresh.png",
            C__NAVBAR_BUTTON__BACK          => "icons/silk/resultset_previous.png",
            C__NAVBAR_BUTTON__UP            => "icons/navbar/up_icon.png",
            C__NAVBAR_BUTTON__FORWARD       => "icons/silk/resultset_next.png",
            C__NAVBAR_BUTTON__PRINT         => "icons/silk/printer.png",
            C__NAVBAR_BUTTON__COMPLETE      => "icons/silk/tilk.png",
            C__NAVBAR_BUTTON__QUICK_PURGE   => "icons/silk/page_delete.png",
            C__NAVBAR_BUTTON__EXPORT_AS_CSV => "icons/silk/page_white_excel.png"
        ];

        $this->m_visible = [
            C__NAVBAR_BUTTON__SAVE          => false,
            C__NAVBAR_BUTTON__CANCEL        => false,
            C__NAVBAR_BUTTON__NEW           => false,
            C__NAVBAR_BUTTON__EDIT          => false,
            C__NAVBAR_BUTTON__DUPLICATE     => false,
            C__NAVBAR_BUTTON__ARCHIVE       => false,
            C__NAVBAR_BUTTON__DELETE        => false,
            C__NAVBAR_BUTTON__PURGE         => false,
            C__NAVBAR_BUTTON__RECYCLE       => false,
            C__NAVBAR_BUTTON__BACK          => false,
            C__NAVBAR_BUTTON__UP            => false,
            C__NAVBAR_BUTTON__FORWARD       => false,
            C__NAVBAR_BUTTON__PRINT         => false,
            C__NAVBAR_BUTTON__COMPLETE      => false,
            C__NAVBAR_BUTTON__QUICK_PURGE   => false,
            C__NAVBAR_BUTTON__EXPORT_AS_CSV => false
        ];

        $this->m_accesskey = [
            C__NAVBAR_BUTTON__SAVE        => 's',
            C__NAVBAR_BUTTON__CANCEL      => 'c',
            C__NAVBAR_BUTTON__NEW         => 'n',
            C__NAVBAR_BUTTON__EDIT        => 'e',
            //C__NAVBAR_BUTTON__DUPLICATE => '',
            C__NAVBAR_BUTTON__ARCHIVE     => 'a',
            C__NAVBAR_BUTTON__DELETE      => 'd',
            C__NAVBAR_BUTTON__PURGE       => 'd',
            C__NAVBAR_BUTTON__RECYCLE     => 'r',
            //C__NAVBAR_BUTTON__BACK      => '',
            //C__NAVBAR_BUTTON__UP        => '',
            //C__NAVBAR_BUTTON__FORWARD   => '',
            C__NAVBAR_BUTTON__PRINT       => 'p',
            //C__NAVBAR_BUTTON__COMPLETE  => '',
            C__NAVBAR_BUTTON__QUICK_PURGE => 'q',
            //C__NAVBAR_BUTTON__EXPORT_AS_CSV => ''
        ];

        $this->m_overlay = [];
    }
}

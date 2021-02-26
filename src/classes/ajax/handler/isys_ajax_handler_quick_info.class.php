<?php

use idoit\Component\Helper\Ip;
use idoit\Model\QuickInfo;

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
class isys_ajax_handler_quick_info extends isys_ajax_handler
{
    /**
     * Variable which holds an CSS class.
     *
     * @var  string
     */
    private $m_class;

    /**
     * Array, which holds the information we want to display.
     *
     * @var  array
     */
    private $m_info = [];

    /**
     * Variable which holds the CSS styles.
     *
     * @var  string
     */
    private $m_style;

    /**
     * @var self
     */
    protected static $instance;

    /**
     * isys_ajax_handler_quick_info constructor.
     *
     * @param array $p_get
     * @param array $p_post
     */
    public function __construct($p_get = null, $p_post = null)
    {
        if (!$p_get) {
            $p_get = $_GET;
        }

        if (!$p_post) {
            $p_post = $_POST;
        }

        parent::__construct($p_get, $p_post);
    }

    /**
     * @return isys_ajax_handler_quick_info
     */
    public static function instance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
            return self::$instance;
        }
        self::$instance->setDatabaseComponent(isys_application::instance()->container->get('database'));
        self::$instance->setGet($_GET);
        self::$instance->setPost($_POST);

        return self::$instance;
    }

    /**
     * Setter method for the CSS class.
     *
     * @param   string $p_class
     *
     * @return  isys_ajax_handler_quick_info
     */
    public function set_class($p_class)
    {
        $this->m_class = $p_class;

        return $this;
    }

    /**
     * Setter method for the CSS styling.
     *
     * @param   string $p_style
     *
     * @return  isys_ajax_handler_quick_info
     */
    public function set_style($p_style)
    {
        $this->m_style = $p_style;

        return $this;
    }

    /**
     * Method for returning the info-array.
     *
     * @return  array
     */
    public function get_info_array()
    {
        return $this->m_info;
    }

    /**
     * Get quick info replacement for table component
     *
     * @param $id
     * @param $title
     *
     * @return string
     */
    public function getQuickInfoReplacement($id, $title)
    {
        return $title . ' {' . $id . '}';
    }

    /**
     * Returns an html link and its corresponding quick info tooltip handler.
     *
     * @param   integer $p_object_id
     * @param   string  $p_title
     * @param   string  $p_link
     * @param   mixed   $p_str_stop
     * @param   array   $p_gets
     *
     * @return  string
     */
    public function get_quick_info($p_object_id, $p_title, $p_link = 'javascript:void(0);', $p_str_stop = false, $p_gets = [], $p_onclick = '')
    {
        $l_aid = 'lb_' . rand(10, 99) . '_' . $p_object_id;

        switch ($p_link) {
            case C__LINK__OBJECT:
                $p_link = isys_helper_link::create_url([C__CMDB__GET__OBJECT => $p_object_id], true);

                break;
            case C__LINK__CATG:
                $l_get = [
                    C__CMDB__GET__OBJECT   => $p_object_id,
                    C__CMDB__GET__VIEWMODE => C__CMDB__VIEW__CATEGORY_GLOBAL,
                    C__CMDB__GET__CATG     => $p_gets[C__CMDB__GET__CATG]
                ];

                if (isset($p_gets[C__CMDB__GET__TREEMODE])) {
                    $l_get[C__CMDB__GET__TREEMODE] = $p_gets[C__CMDB__GET__TREEMODE];
                } else {
                    $l_get[C__CMDB__GET__TREEMODE] = C__CMDB__VIEW__TREE_OBJECT;
                }

                if (isset($p_gets[C__SEARCH__GET__HIGHLIGHT])) {
                    $l_get[C__SEARCH__GET__HIGHLIGHT] = $p_gets[C__SEARCH__GET__HIGHLIGHT];
                }

                if (isset($p_gets[C__CMDB__GET__CATLEVEL])) {
                    $l_get[C__CMDB__GET__CATLEVEL] = $p_gets[C__CMDB__GET__CATLEVEL];
                }

                if (isset($p_gets[C__CMDB__GET__CATG_CUSTOM])) {
                    $l_get[C__CMDB__GET__CATG_CUSTOM] = $p_gets[C__CMDB__GET__CATG_CUSTOM];
                }

                $p_link = isys_helper_link::create_url($l_get, true);
                unset($l_get);

                // @todo  Find an easier way to handle contacts.
                if (isset($p_gets['C__CONTACT_PERSON_LINK'])) {
                    $p_link = '?' . $p_gets['C__CONTACT_PERSON_LINK'];
                }

                break;
            case C__LINK__CATS:
                $l_get = [
                    C__CMDB__GET__OBJECT   => $p_object_id,
                    C__CMDB__GET__VIEWMODE => C__CMDB__VIEW__CATEGORY_GLOBAL,
                    C__CMDB__GET__CATS     => $p_gets[C__CMDB__GET__CATS]
                ];

                if (isset($p_gets[C__CMDB__GET__TREEMODE])) {
                    $l_get[C__CMDB__GET__TREEMODE] = $p_gets[C__CMDB__GET__TREEMODE];
                } else {
                    $l_get[C__CMDB__GET__TREEMODE] = C__CMDB__VIEW__TREE_OBJECT;
                }

                $l_get[C__CMDB__GET__CATLEVEL] = $p_gets[C__CMDB__GET__CATLEVEL];

                $p_link = isys_helper_link::create_url($l_get, true);
                unset($l_get);

                break;
        }

        if (isys_usersettings::get('gui.quickinfo.active', 1)) {
            return $this->get_link($l_aid, $p_title, $p_link, $p_str_stop, $p_onclick) . $this->get_script($l_aid, $p_object_id);
        } else {
            return $this->get_link(false, $p_title, $p_link, $p_str_stop, $p_onclick);
        }
    }

    /**
     * Returns a quick-link conform link.
     *
     * @param   string $p_aid
     * @param   string $p_title
     * @param   string $p_link
     * @param   mixed  $p_str_stop Boolean false or integer for "allowed characters".
     *
     * @return  string
     */
    public function get_link($p_aid, $p_title, $p_link = 'javascript:', $p_str_stop = false, $p_onclick = '')
    {
        $l_id = $l_class = $l_onclick = $l_style = '';

        if ($p_aid) {
            $l_id = ' id="' . $p_aid . '"';
        }

        if ($this->m_class) {
            $l_class = ' class="' . $this->m_class . '"';
        }

        if ($this->m_style) {
            $l_style = ' style="' . $this->m_style . '"';
        }

        if ($p_str_stop) {
            $p_title = isys_glob_str_stop($p_title, $p_str_stop);
        }

        if ($p_onclick) {
            $l_onclick = ' onclick="' . $p_onclick . '"';
        }

        /**
         * @note "data-sort" is used for sorting in e.g. category lists (guest systems is one example)
         *   we only use the first 24 characters to still enable sorting but have a smaller string output
         *   this should also not interfere with the GET parameter "highlight" (see ID-4058)
         *   bin2hex($p_title) could be an option, too!
         */
        return '<a data-sort="' . substr(isys_helper_link::format_url_param($p_title), 0, 24) . '" href="' . $p_link . '"' . $l_id . $l_class . $l_style . $l_onclick . '>' .
            $p_title . '</a>';
    }

    /**
     *
     * @param integer $p_aid
     * @param integer $p_object_id
     * @param boolean $p_include_html_script
     *
     * @return string
     */
    public function get_script($p_aid, $p_object_id, $p_include_html_script = true)
    {
        if (isys_usersettings::get('gui.quickinfo.active', 1)) {
            $l_delay = isys_helper::filter_number(isys_usersettings::get('gui.quickinfo.delay', 0));

            if ($l_delay < 0 || !is_numeric($l_delay)) {
                $l_delay = 0.5;
            }

            $urlParams = [
                C__GET__AJAX         => 1,
                C__GET__AJAX_CALL    => 'quick_info',
                C__CMDB__GET__OBJECT => $p_object_id
            ];

            $options = [
                'ajax'      => [
                    'url' => isys_helper_link::create_url($urlParams, true)
                ],
                'delay'     => (float) $l_delay,
                'className' => 'objectinfo'
            ];

            $l_script = "if ($('" . $p_aid . "')) {new Tip('" . $p_aid . "', '', " . isys_format_json::encode($options) . ");}";

            if ($p_include_html_script) {
                $l_script = '<script type="text/javascript">' . $l_script . '</script>';
            }

            return $l_script;
        }

        return '';
    }

    /**
     * Get quick info's content.
     *
     * @param   integer $p_object_id
     * @param   array   $p_catg
     * @param   array   $p_cats
     *
     * @throws  InvalidArgumentException
     * @throws  Exception
     * @return  string
     */
    public function get_quick_info_content($p_object_id, array $p_catg = [], array $p_cats = [], $p_config = [])
    {
        global $g_dirs;

        $p_object_id = (int)$p_object_id;

        $l_cattitle = [];

        if ($p_object_id <= 0) {
            throw new Exception('Object ID missing!');
        }

        $language = isys_application::instance()->container->get('language');
        $database = isys_application::instance()->container->get('database');
        $l_info = $l_global_categories = $l_specific_categories = [];
        $l_empty = isys_tenantsettings::get('gui.empty_value', '-');
        $l_out = '';
        $l_cpu = 0;

        if (count($p_catg) == 0 && count($p_cats) == 0) {
            throw new InvalidArgumentException('You should specify at least one category to display!');
        }

        foreach ($p_catg as $l_tmp) {
            $l_global_categories[$l_tmp] = true;
        }

        foreach ($p_cats as $l_tmp) {
            $l_specific_categories[$l_tmp] = true;
        }

        $l_global_dist = new isys_cmdb_dao_distributor($database, $p_object_id, C__CMDB__CATEGORY__TYPE_GLOBAL, null, $l_global_categories);
        $l_specific_dist = new isys_cmdb_dao_distributor($database, $p_object_id, C__CMDB__CATEGORY__TYPE_SPECIFIC, null, $l_specific_categories);

        $l_object_data = $l_global_dist->get_object_by_id($p_object_id)
            ->get_row();

        $l_object_type_name = $language->get($l_object_data['isys_obj_type__title']);
        $l_object_type_image = $l_object_data['isys_obj_type__obj_img_name'];

        if (empty($l_object_type_image)) {
            $l_object_type_image = C__OBJTYPE_IMAGE__DEFAULT;
        }

        $l_object_type_image = $g_dirs['images'] . 'objecttypes/' . $l_object_type_image;

        $l_object_image = isys_cmdb_dao_category_g_image::instance($database)
            ->get_data(null, $p_object_id)
            ->get_row();

        if ($l_object_image !== false && !empty($l_object_image['isys_catg_image_list__image_link'])) {
            $l_object_type_image = isys_application::instance()->www_path . isys_smarty_plugin_object_image::get_user_defined_image_url_by_file($l_object_image['isys_catg_image_list__image_link']);
        }

        if ($l_global_dist->count() == 0 && $l_specific_dist->count() == 0) {
            throw new Exception('No assigned categories found!');
        }

        isys_auth_cmdb::instance()
            ->check(isys_auth::VIEW, 'OBJ_ID/' . $p_object_id);

        foreach ($p_catg as $l_category) {
            if (is_string($l_category) && defined($l_category)) {
                $l_category = constant($l_category);
            }

            $l_cat = $l_global_dist->get_category($l_category);
            $l_guidata = $l_global_dist->get_guidata($l_category);

            if (!$l_cat) {
                continue;
            }

            if ($l_category != defined_or_default('C__CATG__GLOBAL')) {
                $l_data = $l_cat->get_data(null, $p_object_id, '', null, C__RECORD_STATUS__NORMAL);
            } else {
                $l_data = $l_cat->get_data(null, $p_object_id);
            }

            $l_cattitle['g' . $l_category] = $language->get($l_guidata['isysgui_catg__title']);

            while ($l_row = $l_data->get_row()) {
                // Add your static category information here.

                if ($l_category == defined_or_default('C__CATG__GLOBAL')) {
                    $l_info['g' . $l_category]['LC__UNIVERSAL__TITLE'] = stripslashes($l_row['isys_obj__title']);
                    $l_info['g' . $l_category]['LC__UNIVERSAL__CMDB_STATUS'] = '<div class="cmdb-marker" style="background-color:#' . $l_row['isys_cmdb_status__color'] .
                        ';"></div>' . $language->get($l_row['isys_cmdb_status__title']);
                    $l_info['g' . $l_category]['LC__CMDB__CATG__GLOBAL_SYSID'] = $l_row['isys_obj__sysid'];

                    if (defined('C__OBJTYPE__RELATION') && $l_row['isys_obj__isys_obj_type__id'] == C__OBJTYPE__RELATION) {
                        $l_info['g' . $l_category]['LC__CATG__RELATION__RELATION_TYPE'] = $language->get(isys_cmdb_dao_category_g_relation::instance($database)
                            ->get_relation_type_by_object_id($l_row['isys_obj__id']));
                    }
                } elseif ($l_category == defined_or_default('C__CATG__CONTACT')) {
                    $l_primID = $l_primType = null;
                    $l_dao_row = $l_cat->contact_get_primary($l_primType, $l_primID);

                    if (is_countable($l_dao_row) && count($l_dao_row) > 0) {
                        $l_info['g' . $l_category]['LC__CMDB__CATG__CONTACT'] = $l_dao_row['isys_obj__title'];

                        if ($l_dao_row['isys_cats_person_list__title']) {
                            $l_info['g' . $l_category]['LC__CMDB__CATG__CONTACT'] .= ' (' . $l_dao_row['isys_cats_person_list__title'] . ')';
                        }

                        if ($l_dao_row['isys_cats_person_list__phone_company']) {
                            $l_info['g' . $l_category]['LC__CMDB__CATS_CP_CONTRACT__PHONE_NUMBER'] = $l_dao_row['isys_cats_person_list__phone_company'];
                        }
                    }
                } elseif ($l_category == defined_or_default('C__CATG__MODEL')) {
                    if (!empty($l_row['isys_model_manufacturer__title'])) {
                        $l_info['g' . $l_category]['LC__CMDB__CATG__MODEL_MANUFACTURE'] = $language->get($l_row['isys_model_manufacturer__title']);
                    }

                    if (!empty($l_row['isys_model_title__title'])) {
                        $l_info['g' . $l_category]['LC__CMDB__CATG__MODEL_TITLE'] = $l_row['isys_model_title__title'];
                    }

                    if (!empty($l_row['isys_catg_model_list__serial'])) {
                        $l_info['g' . $l_category]['LC__CMDB__CATG__MODEL_SERIAL'] = $l_row['isys_catg_model_list__serial'];
                    }
                } elseif ($l_category == defined_or_default('C__CATG__CPU')) {
                    $l_cpu++;
                    $l_cpu_title = $l_row['isys_catg_cpu_list__title'];

                    $l_info['g' . $l_category]['LC__CMDB__CATG__CPU_TITLE'] = '';

                    if (!empty($l_row['isys_catg_cpu_type__title'])) {
                        $l_info['g' . $l_category]['LC__CMDB__CATG__CPU_TYPE'] = $l_row['isys_catg_cpu_type__title'];
                    }
                } elseif ($l_category == defined_or_default('C__CATG__NETWORK')) {
                    if (!empty($l_row['isys_catg_netp_list__title'])) {
                        $l_info['g' . $l_category]['Interface'][] = $l_row['isys_catg_netp_list__title'];
                    } else {
                        if (!empty($l_row['isys_catg_hba_list__title'])) {
                            $l_info['g' . $l_category]['Interface'][] = $l_row['isys_catg_hba_list__title'];
                        }
                    }
                } elseif ($l_category == defined_or_default('C__CATG__CONNECTOR')) {
                    if (!empty($l_row['isys_catg_connector_list__title']) && $l_row['isys_catg_connector_list__isys_cable_connection__id'] > 0) {
                        $l_cable_dao = isys_cmdb_dao_cable_connection::instance($database);

                        $l_sql = 'SELECT isys_obj__title, isys_catg_connector_list__title 
                                FROM isys_catg_connector_list
                                INNER JOIN isys_obj ON isys_obj__id = isys_catg_connector_list__isys_obj__id
                                WHERE isys_catg_connector_list__isys_cable_connection__id = ' .
                            $l_cable_dao->convert_sql_id($l_row['isys_catg_connector_list__isys_cable_connection__id']) . '
                                AND isys_catg_connector_list__id != ' . $l_cable_dao->convert_sql_id($l_row['isys_catg_connector_list__id']) . ' 
                                LIMIT 1;';

                        $l_connection = $l_cable_dao->retrieve($l_sql)
                            ->get_row();

                        if (!empty($l_connection)) {
                            $l_info['g' . $l_category][$l_row['isys_catg_connector_list__title']] = $l_connection['isys_obj__title'] . ' (' .
                                $l_connection['isys_catg_connector_list__title'] . ')';
                        }
                    }
                } elseif ($l_category == defined_or_default('C__CATG__VERSION')) {
                    if (!empty($l_row['isys_catg_version_list__servicepack'])) {
                        $l_info['g' . $l_category]['LC__CATG__SERVICEPACK'] = $l_row['isys_catg_version_list__servicepack'];
                    }

                    if (!empty($l_row['isys_catg_version_list__hotfix'])) {
                        $l_info['g' . $l_category]['LC__CATG__PATCHES'] = $l_row['isys_catg_version_list__hotfix'];
                    }
                }
            }

            unset($l_row);
        }

        foreach ($p_cats as $l_category) {
            if (is_string($l_category) && defined($l_category)) {
                $l_category = constant($l_category);
            }

            $l_cat = $l_specific_dist->get_category($l_category);
            $l_guidata = $l_specific_dist->get_guidata($l_category);

            if (!$l_cat) {
                continue;
            }

            $l_data = $l_cat->get_data(null, $p_object_id, '', null, C__RECORD_STATUS__NORMAL);

            $l_cattitle['s' . $l_category] = $language->get($l_guidata['isysgui_cats__title']);

            while ($l_row = $l_data->get_row()) {
                if ($l_category == defined_or_default('C__CATS__NET')) {
                    // Display net address range and stuff.
                    if (!empty($l_row['isys_cats_net_list__address']) && !empty($l_row['isys_cats_net_list__cidr_suffix'])) {
                        if ($l_row['isys_cats_net_list__isys_net_type__id'] == defined_or_default('C__CATS_NET_TYPE__IPV6')) {
                            $l_row['isys_cats_net_list__address'] = Ip::validate_ipv6($l_row['isys_cats_net_list__address'], true);
                        }

                        $l_info['s' . $l_category]['LC__CMDB__CATS__NET_IP_ADDRESSES__NETADDRESS'] = $l_row['isys_cats_net_list__address'] . ' /' .
                            $l_row['isys_cats_net_list__cidr_suffix'];
                    }

                    if (!empty($l_row['isys_cats_net_list__address_range_from']) && !empty($l_row['isys_cats_net_list__address_range_to'])) {
                        if ($l_row['isys_cats_net_list__isys_net_type__id'] == defined_or_default('C__CATS_NET_TYPE__IPV6')) {
                            $l_row['isys_cats_net_list__address_range_from'] = Ip::validate_ipv6($l_row['isys_cats_net_list__address_range_from'], true);
                            $l_row['isys_cats_net_list__address_range_to'] = Ip::validate_ipv6($l_row['isys_cats_net_list__address_range_to'], true);
                        }

                        $l_info['s' . $l_category]['LC__CMDB__CATS__NET__ADDRESS_RANGE'] = $l_row['isys_cats_net_list__address_range_from'] . ' - ' .
                            $l_row['isys_cats_net_list__address_range_to'];
                    }
                } elseif ($l_category == defined_or_default('C__CATS__CONTRACT')) {
                    $l_locale = isys_locale::get_instance();

                    if (!empty($l_row['isys_cats_contract_list__contract_no'])) {
                        $l_info['s' . $l_category]['LC__CMDB__CATS__MAINTENANCE_CONTRACT_NUMBER'] = $l_row['isys_cats_contract_list__contract_no'];
                    }

                    if ((int)$l_row['isys_contract_type__id'] > 0) {
                        $l_info['s' . $l_category]['LC__CMDB__CATS__CONTRACT__TYPE'] = $language->get($l_row['isys_contract_type__title']);
                    }

                    if (strtotime($l_row['isys_cats_contract_list__start_date']) > 0) {
                        $l_info['s' . $l_category]['LC__CMDB__CATS__CONTRACT__START_DATE'] = $l_locale->fmt_date($l_row['isys_cats_contract_list__start_date']);
                    }

                    if (strtotime($l_row['isys_cats_contract_list__end_date']) > 0) {
                        $l_info['s' . $l_category]['LC__CMDB__CATS__CONTRACT__END_DATE'] = $l_locale->fmt_date($l_row['isys_cats_contract_list__end_date']);
                    }

                    if ($l_row['isys_cats_contract_list__runtime'] > 0 || $l_row['isys_cats_contract_list__runtime_unit'] > 0) {
                        $l_unit = isys_factory_cmdb_dialog_dao::get_instance('isys_guarantee_period_unit', $database)
                            ->get_data($l_row['isys_cats_contract_list__runtime_unit']);
                        $l_info['s' . $l_category]['LC__CMDB__CATG__LEASING__RUNTIME'] = $l_row['isys_cats_contract_list__runtime'] . ' ' .
                            $language->get($l_unit['isys_guarantee_period_unit__title']);
                    }
                }
            }

            unset($l_row);
        }

        if (defined('C__CATG__CPU')) {
            // CPU Amount
            if ($l_cpu > 0 && !empty($l_cpu_title) && $l_cpu_title != ' ') {
                $l_info['g' . constant('C__CATG__CPU')]['LC__CMDB__CATG__CPU_TITLE'] = $l_cpu . 'x ' . $l_cpu_title;
            } else {
                unset($l_info['g' . constant('C__CATG__CPU')]);
            }
        }

        if (defined('C__CATG__NETWORK')) {
            // IP-Addresses.
            $l_ip_dao = new isys_cmdb_dao_category_g_ip($database);
            $l_ips = $l_ip_dao->get_primary_ip($p_object_id);

            while ($l_ip = $l_ips->get_row()) {
                $l_info['g' . constant('C__CATG__NETWORK')]['LC__CATP__IP__ADDRESS'][] = $l_ip['isys_cats_net_ip_addresses_list__title'];
                $l_info['g' . constant('C__CATG__NETWORK')]['LC__CMDB__CATS__NET__MASK'][] = $l_ip['isys_cats_net_list__mask'];
            }
        }

        /* -------------------------------------------------------------------------------------------- */
        /* Prepare quick info content generation */
        /* -------------------------------------------------------------------------------------------- */
        $l_out .= '<div class="container">' .
            '<h3 class="ot">' .
            '<div class="cmdb-marker" style="background-color:#' . $l_object_data['isys_obj_type__color'] . ';"></div>' .
            '<img src="' . $l_object_type_image . '" />' .
            $l_object_type_name . ' (' . $l_global_dist->get_record_status_as_string($l_object_data['isys_obj__status']) . ')' .
            '</h3>';

        if (is_array($l_info)) {
            if (isset($p_config['maxLen'])) {
                $l_maxLen = $p_config['maxLen'];
            } else {
                $l_maxLen = 50;
            }

            $rowsPerCategory = isys_tenantsettings::get('cmdb.quickinfo.rows-per-category', 15);

            foreach ($l_info as $l_category => $l_data) {
                if (!is_countable($l_data)) {
                    continue;
                }
                $reducedOutput = false;
                $l_out .= '<h4 class="category text-bold">' . $l_cattitle[$l_category] . '</h4>' . '<table>' . '<colgroup><col width="85" /></colgroup>' . '<tbody>';

                // @see  ID-5188  Limit the output (if option is set).
                if (count($l_data) > $rowsPerCategory) {
                    $reducedOutput = true;
                    $l_data = array_slice($l_data, 0, $rowsPerCategory);
                }

                foreach ($l_data as $l_key => $l_value) {
                    if ($l_value == '' || is_null($l_value)) {
                        $l_value = $l_empty;
                    }

                    if (is_array($l_value)) {
                        $l_value = implode(', ', $l_value);
                    }

                    if ($l_key == 'LC__UNIVERSAL__TITLE') {
                        if (isset($p_config['createObjectLink']) && $p_config['createObjectLink']) {
                            $l_value = '<a href="?' . C__CMDB__GET__OBJECT . '=' . $p_object_id . '" class="text-red">' . $l_value . '</a>';
                        } else {
                            $l_value = '<u>' . $l_value . '</u>';
                        }
                    }

                    // The "max length" should not be used, when displaying HTML because this can break the output.
                    if (!in_array($l_key, ['LC__UNIVERSAL__CMDB_STATUS'])) {
                        $l_value = isys_glob_str_stop($l_value, $l_maxLen);
                    }

                    $l_out .= '<tr><td class="text-bold key">' . $language->get($l_key) . '</td><td>' . $l_value . '</td></tr>';
                }

                if ($reducedOutput) {
                    $l_out .= '<tr><td>...</td></tr>';
                }

                $l_out .= '</tbody></table>';
            }
        }

        $l_out .= '</div>';

        $this->m_info = $l_info;

        // Create cache.
        QuickInfo::instance($database)->setCache($p_object_id, $l_out);

        return $l_out;
    }

    /**
     * @param  int $objectId
     *
     * @return string
     */
    public function quickinfo($objectId)
    {
        // Check for existing cache.
        $cache = QuickInfo::instance($this->m_database_component)
            ->getCache($objectId)
            ->get_row_value('data');

        if ($cache !== null) {
            return $cache;
        }

        return $this->get_quick_info_content($objectId, filter_defined_constants([
            'C__CATG__GLOBAL',
            'C__CATG__CONTACT',
            'C__CATG__MODEL',
            'C__CATG__CPU',
            'C__CATG__NETWORK',
            'C__CATG__VERSION',
            'C__CATG__NETWORK_PORT',
            'C__CATG__CONNECTOR'
        ]), filter_defined_constants([
            'C__CATS__NET',
            'C__CATS__CONTRACT'
        ]));
    }

    /**
     * Initialization method.
     */
    public function init()
    {
        // @see  ID-6913  Set the cache expiration to the defined value (-5 seconds so that the following code will re-fresh the cache on time).
        isys_core::expire(isys_tenantsettings::get('cache.quickinfo.expiration', isys_convert::HOUR) - 5);

        isys_application::instance()->container->get('session')->write_close();

        try {
            echo $this->quickinfo((int) $this->m_get[C__CMDB__GET__OBJECT]);
        } catch (isys_exception_database $e) {
            echo 'Database error: ' . $e->getMessage();
        } catch (Exception $e) {
            echo 'General error: ' . $e->getMessage();
        }

        $this->_die();
    }
}

<?php

use idoit\Component\Helper\Purify;

/**
 * CMDB Category view
 *
 * @package     i-doit
 * @subpackage  CMDB_Views
 * @author      Andre Wösten <awoesten@i-doit.de>
 * @version     0.9
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_view_contenttop extends isys_cmdb_view
{
    /**
     * Returns the ID for the category view.
     *
     * @return  integer
     */
    public function get_id()
    {
        return C__CMDB__VIEW__CATEGORY;
    }

    /**
     * Returns the mandatory parameters.
     *
     * @param  array &$l_gets
     */
    public function get_mandatory_parameters(&$l_gets)
    {
        $l_gets[C__CMDB__GET__OBJECT] = true;
    }

    /**
     * Returns the name of the view.
     *
     * @return  string
     */
    public function get_name()
    {
        return "";
    }

    /**
     * Method for setting the optional parameters.
     *
     * @param  array &$l_gets
     */
    public function get_optional_parameters(&$l_gets)
    {
    }

    /**
     * Returns the filepath of the "bottom" template.
     *
     * @return  null
     */
    public function get_template_bottom()
    {
        return null;
    }

    /**
     * Returns the filepath of the "top" template.
     *
     * @return  string
     */
    public function get_template_top()
    {
        return "content/top/main_objectdetail.tpl";
    }

    /**
     * Process category view and handles the "authorization-fail" view.
     *
     * @throws  isys_exception_cmdb
     * @throws  Exception|isys_exception_cmdb
     * @return  null
     */
    public function process()
    {
        try {
            $this->overview_process();
        } catch (isys_exception_auth $e) {
            throw new Exception($e->getMessage());
        } catch (isys_exception_cmdb $e) {
            throw $e;
        } catch (Exception $e) {
            throw $e;
        }

        return null;
    }

    /**
     * Function processing the object overview in  a category view.
     * Used from object browser from now on!
     *
     * @param   string $p_tomdest
     *
     * @return  boolean
     * @throws  isys_exception_cmdb
     */
    public function overview_process($p_tomdest = "tom.content.top")
    {
        $l_db = $this->get_module_request()
            ->get_database();
        $l_gets = $this->get_module_request()
            ->get_gets();

        $l_gets = Purify::castIntValues($l_gets);

        $l_tpl = $this->get_module_request()
            ->get_template();

        $l_gets = Purify::purifyParams($l_gets);

        isys_application::instance()->template->assign('editMode', isys_application::instance()->template->editmode());

        /**
         * --------------------------------------------------------------------------
         * GLOBAL
         * --------------------------------------------------------------------------
         */
        $l_dao_global = new isys_cmdb_dao_category_g_global($l_db);
        $l_cat_data = $l_dao_global->get_data(null, $l_gets[C__CMDB__GET__OBJECT])
            ->__to_array();
        $l_str_access = isys_tenantsettings::get('gui.empty_value', '-');
        $l_rules = [];

        $l_str_purpose = isys_application::instance()->container->get('language')
            ->get($l_cat_data["isys_purpose__title"]);

        /**
         * --------------------------------------------------------------------------
         * Relations
         * --------------------------------------------------------------------------
         */
        $l_dao_relations = new isys_cmdb_dao_category_g_relation($l_db);
        $l_str_relations = sprintf(
            isys_application::instance()->container->get('language')
            ->get("LC__CMDB__RELATION_HEADER"),
            $l_dao_relations->count_relations($l_gets[C__CMDB__GET__OBJECT], C__RELATION__IMPLICIT),
            $l_dao_relations->count_relations($l_gets[C__CMDB__GET__OBJECT], C__RELATION__EXPLICIT)
        );

        /**
         * --------------------------------------------------------------------------
         * ACCESS
         * --------------------------------------------------------------------------
         */
        $l_dao_access = new isys_cmdb_dao_category_g_access($l_db);
        $l_temp = isys_helper_link::handle_url_variables($l_dao_access->get_url($l_gets[C__CMDB__GET__OBJECT]), $l_gets[C__CMDB__GET__OBJECT]);
        if (!empty($l_temp)) {
            $l_str_access = '<a target="_blank" href="' . $l_temp . '">' . $l_temp . '</a>';
        }

        /**
         * --------------------------------------------------------------------------
         * CONTACT
         * --------------------------------------------------------------------------
         */
        if ($_GET[C__CMDB__GET__OBJECT] > 0) {
            $l_cc_dao = new isys_cmdb_dao_category_g_contact($l_db);
            $l_cc_dao->set_object_id($_GET[C__CMDB__GET__OBJECT]);

            $l_primID = null;
            $l_primType = null;
            $l_contact_data = $l_cc_dao->contact_get_primary($l_primType, $l_primID);

            $l_str_contact = '<a href="' . isys_helper_link::create_url([C__CMDB__GET__OBJECT => $l_primID]) . '">' . $l_contact_data['isys_obj__title'] . '</a>';
        } else {
            $l_str_contact = '';
        }

        /**
         * --------------------------------------------------------------------------
         * LOCATION
         * --------------------------------------------------------------------------
         */
        $l_loc_popup = new isys_popup_browser_location();
        $l_loc_dao = new isys_cmdb_dao_category_g_location($l_db);
        $l_str_location = $l_loc_popup->format_selection($l_loc_dao->get_parent_id_by_object($_GET[C__CMDB__GET__OBJECT]));

        /**
         * --------------------------------------------------------------------------
         * RULE Assignments
         * --------------------------------------------------------------------------
         */
        $l_rules["C__CATG__TITLE"]["p_strValue"] = str_replace('\\', '&#92;', $l_cat_data["isys_obj__title"]); // Fix for ID-602 (Backslashes in Object Title)
        $l_rules["C__CATG__SYSID"]["p_strValue"] = isys_glob_str_stop($l_cat_data["isys_obj__sysid"], 50);
        $l_rules["C__CATG__PURPOSE"]["p_strValue"] = isys_glob_str_stop($l_str_purpose, 50);
        $l_rules["C__CATG__RELATIONS"]["p_strValue"] = $l_str_relations;
        $l_rules["C__CATG__LOCATION"]["p_strValue"] = $l_str_location;
        $l_rules["C__CATG__CONTACT"]["p_strValue"] = $l_str_contact;
        $l_rules["C__CATG__ACCESS"]["p_strValue"] = $l_str_access;
        $l_qrcode_data = [];

        if (class_exists('isys_module_qrcode')) {
            $l_qrcode_data = isys_factory::get_instance('isys_module_qrcode')
                ->init(isys_module_request::get_instance())
                ->load_qr_code($l_gets[C__CMDB__GET__OBJECT]);
        }

        if (!empty($l_qrcode_data['url'])) {
            global $g_config;

            $l_tpl->assign('qr_code_link', $l_qrcode_data['link'])
                ->assign('qr_code_iqr_url', $l_qrcode_data['iqr'])
                ->assign('qr_code_src_popup', 'src/tools/php/qr/qr.php?url=' . isys_helper_link::get_base() . '&objID=' . $l_gets[C__CMDB__GET__OBJECT])
                ->assign('qr_code_src_img', $g_config['www_dir'] . 'src/tools/php/qr/qr_img.php?&s=2&d=' . $l_qrcode_data['url'])
                ->assign('show_qr_code', true);
        } else {
            $l_tpl->assign('show_qr_code', false);
        }

        // Send rules via TOM to template.
        $l_tpl->smarty_tom_add_rules($p_tomdest, $l_rules);

        /**
         * --------------------------------------------------------------------------
         * Emit processContentTop (this is used by the monitoring modules to extend the header with a live host status information)
         * --------------------------------------------------------------------------
         */
        isys_component_signalcollection::get_instance()
            ->emit("mod.cmdb.processContentTop", $l_cat_data);

        return true;
    }
}

<?php
/**
 * i-doit
 *
 * Smarty plugin for object images. Returns a string with the source of the image.
 *
 * @package     i-doit
 * @subpackage  Smarty_Plugins
 * @author      Niclas Potthast <npotthast@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */

define("C__IMAGE_SIZE__THUMB", "28px");
define("C__IMAGE_SIZE__NORMAL", "100px");

class isys_smarty_plugin_object_image extends isys_smarty_plugin_f implements isys_smarty_plugin
{
    /**
     * @param      $p_filename
     * @param null $p_objectType
     *
     * @return string
     */
    public static function get_user_defined_image_url_by_file($p_filename, $p_objectType = null)
    {
        global $g_dirs;

        $l_dlgets[C__GET__MODULE_ID] = defined_or_default('C__MODULE__CMDB');
        $l_dlgets[C__GET__FILE_MANAGER] = "image";

        if (!empty($p_filename) && file_exists(isys_application::instance()->getUploadImagePath($p_filename))) {
            $l_dlgets["file"] = $p_filename;

            return '?' . isys_glob_http_build_query($l_dlgets);
        }

        return "images/objecttypes/" . self::getImageNameByObjTypeID($p_objectType);
    }

    /**
     * @param   integer $p_objID
     *
     * @return  string
     * @author  Dennis Stuecken <dstuecken@synetics.de>
     */
    public static function get_user_defined_image_url_by_id($p_objID, $p_objTypeID = null)
    {
        global $g_comp_database;

        $l_image_dao = new isys_cmdb_dao_category_g_image($g_comp_database);

        return self::get_user_defined_image_url_by_file($l_image_dao->get_image_name_by_object_id($p_objID), $p_objTypeID);
    }

    /**
     * Return image name in objecttype image directory.
     *
     * @global  isys_component_database $g_comp_database
     *
     * @param   integer                 $p_objTypeID
     *
     * @return  string (empty if nothing is found)
     * @author  Niclas Potthast <npotthat@i-doit.org>
     */
    private static function getImageNameByObjTypeID($p_objTypeID)
    {
        global $g_comp_database;

        if ($p_objTypeID > 0) {
            $l_strName = isys_cmdb_dao::instance($g_comp_database)
                ->get_objtype_img_by_id_as_string($p_objTypeID);

            if (!empty($l_strName)) {
                return $l_strName;
            }
        }

        return C__OBJTYPE_IMAGE__DEFAULT;
    }

    /**
     * Defines wheather the sm2 meta map is enabled or not.
     *
     * @return  boolean
     */
    public function enable_meta_map()
    {
        return false;
    }

    /**
     * Return the image to an objecttype (or object - todo).
     *
     * @param    isys_component_template &$p_tplclass
     * @param    array                   $p_params
     *
     * @return   string
     * @author   Niclas Potthast <npotthast@i-doit.org>
     */
    public function navigation_view(isys_component_template $p_tplclass, $p_params = null)
    {
        global $g_config;

        if ($p_params === null) {
            $p_params = $this->m_parameter;
        }

        $this->m_strPluginClass = "object_image";
        $this->m_strPluginName = $p_params["name"];

        if (empty($p_params["objType"])) {
            $l_nObjTypeID = $_GET["objTypeID"];
        } else {
            $l_nObjTypeID = $p_params["objType"];
            $l_nObjID = null;
        }

        if (empty($p_params[C__CMDB__GET__OBJECT])) {
            $l_nObjID = $_GET[C__CMDB__GET__OBJECT];
        } else {
            $l_nObjID = $p_params[C__CMDB__GET__OBJECT];
        }

        // Get user image.
        $l_strSrc = $g_config['www_dir'] . $this->get_user_defined_image_url_by_id($l_nObjID, $l_nObjTypeID);

        if ($p_params["p_bThumb"] == "1" || $_SESSION["viewMode"]["contentTop"] == "off") {
            $l_width = C__IMAGE_SIZE__THUMB;
            $l_height = C__IMAGE_SIZE__THUMB;
        } else {
            $l_width = C__IMAGE_SIZE__NORMAL;
            $l_height = C__IMAGE_SIZE__NORMAL;
        }

        if ($p_params["width"]) {
            $l_width = $p_params["width"];
        }

        if ($p_params["height"]) {
            $l_height = $p_params["height"];
        }

        $l_class = $l_style = $l_align = "";

        if (!empty($p_params["class"])) {
            $l_class = ' class="' . $p_params["class"] . '"';
        }

        if (!empty($p_params["style"])) {
            $l_style = ' style="' . $p_params["style"] . '"';
        }

        if (!empty($p_params["align"])) {
            $l_align = ' align="' . $p_params["align"] . '"';
        }

        return '<img id="object_image_header" ' . $l_class . $l_style . $l_align . ' width="' . $l_width . '" height="' . $l_height . '" src="' . $l_strSrc . '" alt="' .
            $p_params["alt"] . '" />';
    }

    /**
     * @param   isys_component_template &$p_tplclass
     * @param   array                   $p_params
     *
     * @return  string
     */
    public function navigation_edit(isys_component_template $p_tplclass, $p_params = null)
    {
        return $this->navigation_view($p_tplclass, $p_params);
    }
}
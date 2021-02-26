<?php

use idoit\Component\Helper\Purify;

/**
 * i-doit
 *
 * Breadcrumb Navigation: Hierarchical View of Links in the Banner
 *
 * @package     i-doit
 * @subpackage  Components_Template
 * @author      Niclas Potthast <npotthast@i-doit.de>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_component_template_breadcrumb extends isys_component_template
{
    protected $m_includeHome = false;

    protected $m_nAlertLevel;

    protected $m_nMessageID;

    protected $m_strMessage;

    protected $m_strMessageType;

    /**
     * Sets the option to include the "home" segment to the breadcrumb.
     *
     * @param   boolean $p_set
     *
     * @return  isys_component_template_breadcrumb
     */
    public function include_home($p_set = true)
    {
        $this->m_includeHome = $p_set;

        return $this;
    }

    /**
     * Process method.
     *
     * @param   boolean $p_plain
     * @param   string  $p_append
     * @param   integer $l_module_id
     * @param   string  $p_prepend
     * @param   string  $p_linkClass
     *
     * @return  string
     */
    public function process($p_plain = false, $p_append = null, $l_module_id = null, $p_prepend = null, $p_linkClass = null)
    {
        global $g_dirs;

        $l_out = '';

        if ($this->m_includeHome) {
            $l_out = '<li class="home"><a href="' . isys_application::instance()->www_path . '" title="Home"><img src="' . $g_dirs['images'] .
                'home.png"  height="12" alt="i-doit" /></a></li>';
        }

        // Determine module manager as "first level".
        $l_modman = isys_module_manager::instance();

        // Retrieve the current module.
        if (!empty($l_module_id)) {
            $l_actmod = $l_module_id;
        } else {
            if (!$l_modman) {
                $l_actmod = defined_or_default('C__MODULE__CMDB');
            } else {
                $l_actmod = $l_modman->get_active_module();
            }
        }

        if ($l_actmod) {
            // Return active module register entry.
            $l_modreg = $l_modman->get_by_id($l_actmod);

            if (is_object($l_modreg) && $l_modreg->is_initialized()) {
                // Asking module for its breadcrumb navigation.
                $l_modobj = $l_modreg->get_object();

                // The module data includes the module title
                $l_moddata = $l_modreg->get_data();
                $l_strTitle = $l_moddata["isys_module__title"];

                // Build first entry of breadcrumb.
                $l_req_gets = isys_module_request::get_instance()
                    ->get_gets();
                $l_req_gets = Purify::castIntValues($l_req_gets);

                $l_gets = [];
                $l_gets[C__GET__MODULE_ID] = $l_actmod;
                // This case is for example Templates and Mass changes.
                // Because both functions are using the same module class.
                if (isset($l_req_gets[C__GET__MODULE])) {
                    $l_gets[C__GET__MODULE] = $l_req_gets[C__GET__MODULE];

                    // @todo  Check if this is still necessary and remove it
                    if (method_exists($l_modobj, 'get_module_title')) {
                        $l_strTitle = $l_modobj::get_module_title($l_req_gets[C__GET__MODULE]);
                    }
                }

                // Build URL for GET-Parameters of module.
                $l_url = isys_glob_build_url(isys_glob_http_build_query($l_gets));

                // Append URL
                if ($p_plain) {
                    $l_out .= isys_application::instance()->container->get('language')
                            ->get($l_strTitle) . ' > ';
                } else {
                    if ($p_prepend) {
                        $l_out .= $p_prepend;
                    }

                    $l_out .= $this->build_link($l_url, $l_strTitle);
                }

                if (method_exists($l_modobj, "breadcrumb_get")) {
                    /**
                     * breadcrumb_get has to return following data structure:
                     *
                     * [
                     *    [
                     *       "MeinObjekt" => [
                     *          "moduleID" => 2,
                     *          "objID"    => 3,
                     *          "viewMode" => C__CMDB__VIEW__CATEGORY_GLOBAL,
                     *          "treeMode" => C__CMDB__VIEW__TREE_OBJECT
                     *       ]
                     *    ],
                     *    ...
                     * ];
                     */

                    $l_bc_data = $l_modobj->breadcrumb_get($l_gets);

                    if ($l_bc_data && is_array($l_bc_data)) {
                        // Iterating through breadcrumb entries.
                        foreach ($l_bc_data as $l_bc_no => $l_bc_info) {
                            $l_bc_title = key($l_bc_info);
                            $l_bc_gets = current($l_bc_info);

                            // Build URL.
                            $l_bc_url = isys_glob_build_url(isys_glob_http_build_query($l_bc_gets));

                            if ($p_plain) {
                                $l_out .= $p_prepend . $l_bc_title . $p_append;
                            } else {
                                if ($p_prepend) {
                                    $l_out .= $p_prepend;
                                }

                                if (($l_bc_no < count($l_bc_data) - 1)) {
                                    $l_out .= $this->build_link($l_bc_url, $l_bc_title, null, $p_linkClass);

                                    /* .. and append URL for this entry */
                                    if ($p_append == null) {
                                        $l_out .= " > ";
                                    } else {
                                        $l_out .= $p_append;
                                    }
                                } else {
                                    if ($p_prepend) {
                                        $l_out .= $l_bc_title;
                                    } else {
                                        $l_current_url = str_replace("&ajax=1", "", $_SERVER["QUERY_STRING"]);
                                        $l_current_url = str_replace("&request=breadcrumb", "", $l_current_url);

                                        $l_out .= $this->build_link(isys_glob_build_url($l_current_url), $l_bc_title);
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        return $l_out . ((isset($p_append) && !$p_plain) ? $p_append : '');
    }

    /**
     * @return  string
     * @author  Niclas Potthast <npotthast@i-doit.de>
     */
    public function get_html()
    {
        global $g_config, $g_strMandatorName, $g_db_system;

        $languageManager = isys_application::instance()->container->get('language');

        $l_strOut = "";
        $l_arNavi = [];

        $l_strNaviLink = $this->build_link(
            $g_config["www_dir"],
            $languageManager->get($g_strMandatorName),
            $languageManager->get('LC__NAVIGATION__BREADCRUMB__BACK_TO_MAIN_VIEW')
        );

        array_push($l_arNavi, $l_strNaviLink);

        // Reverse the array and build navigation as a string.
        $l_arNavi = array_reverse($l_arNavi);
        $l_nArrayCount = count($l_arNavi);
        $l_nCount = 1;

        foreach ($l_arNavi as $l_value) {
            $l_strOut .= $l_value;

            if ($l_nCount < $l_nArrayCount) {
                $l_strOut .= " > ";
            }

            $l_nCount++;
        }

        $l_strDBVer = $g_db_system["name"];

        return $l_strOut . " - ($l_strDBVer)";
    }

    /**
     *
     * @param   string $p_strURL
     * @param   string $p_strTitle
     * @param   string $p_strTooltip
     * @param   string $p_linkClass
     *
     * @return  string
     * @author  Niclas Potthast <npotthast@i-doit.de>
     */
    private function build_link($p_strURL, $p_strTitle, $p_strTooltip = '', $p_linkClass = '')
    {
        $languageManager = isys_application::instance()->container->get('language');

        if (!empty($p_strTitle)) {
            $p_strTitle = $languageManager->get(html_entity_decode(stripslashes($p_strTitle), null, $GLOBALS['g_config']['html-encoding']));
        }

        if (empty($p_strTitle)) {
            $p_strTitle = $languageManager->get('LC__NAVIGATION__BREADCRUMB__NO_TITLE');
        }

        if (!empty($p_linkClass)) {
            $p_linkClass = 'class="' . $p_linkClass . '"';
        }

        // It might be quite rare, that object names are this long... But better this, than a "broken" GUI.
        $p_strTitle = isys_glob_cut_string($p_strTitle, 50, ' ...');

        if (!empty($p_strURL)) {
            if (strpos($p_strURL, '/') !== 0) {
                $p_strURL = isys_application::instance()->www_path . $p_strURL;
            }

            return "<a href=\"$p_strURL\" $p_linkClass $p_strTooltip>$p_strTitle</a>";
        } else {
            return $p_strTitle;
        }
    }

    /**
     * Constructor.
     */
    public function __construct()
    {
        ;
    }
}

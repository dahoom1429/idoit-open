<?php
/**
 * i-doit
 *
 * Template library.
 *
 * @package     i-doit
 * @subpackage  Components Template
 * @author      Andre Woesten <awoesten@i-doit.de>
 * @author      Niclas Potthast <npotthast@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */

/**
 * Template class. Has following place in Object model:
 *     Base:               class Smarty
 *     Abstraction:        - class isys_library_smarty
 *     Concrete&Inherited: - class isys_component_template
 */
class isys_component_template extends isys_library_smarty
{
    /**
     * @var isys_component_template
     */
    private static $m_instance = null;

    /**
     * Static, instance-global array with SM2 formular elements.
     * Is also providing as a backup of the formular data, which are registered as the global variable $g_SM2_FORM.
     *
     * @var array
     */
    private static $m_sm2_submitted;

    /**
     * TOM-ruleset.
     *
     * @var array
     */
    private $m_ruleset;

    /**
     * Singleton instance function
     *
     * @param array $p_options
     *
     * @return isys_component_template
     */
    public static function instance($p_options = [])
    {
        if (!self::$m_instance) {
            self::$m_instance = new self($p_options);
        }

        return self::$m_instance;
    }

    /**
     * SM2 - sm2_process_request_data
     *
     * Processes the request data and extracts an array into the global
     * symbol list with all SM2-parameters
     */
    public static function sm2_process_request_data()
    {
        global $g_SM2_FORM; // Register $g_SM2_FORM
        global $GLOBALS; // Fetch all global symbols

        // Prefix for SM2 variables.
        define("SM2_PREFIX", "SM2__");

        // Create global formular storage.
        $g_SM2_FORM = [];

        foreach ($GLOBALS["_POST"] as $l_key => $l_val) {
            // Does the $_POST-Field start with SM2__ and does it include an array with the data.
            if ((substr($l_key, 0, strlen(SM2_PREFIX)) == SM2_PREFIX) && is_array($l_val)) {
                $l_field = substr($l_key, strlen(SM2_PREFIX));

                // If yes, append entry with real TOM name to formular storage.
                self::$m_sm2_submitted[$l_field] = $l_val;

                // Remove formular field from $_POST array.
                unset($GLOBALS["_POST"][$l_key]);
            }
        }

        // Transport formular storage into global variable, $g_SM2_FORM, which YOU can use :-).
        $g_SM2_FORM = self::$m_sm2_submitted;
    }

    /**
     * Returns SM2 array
     *
     * @return array
     */
    public static function sm2_get_processed_form()
    {
        return self::$m_sm2_submitted;
    }

    /**
     * Checks a formular field and returns a boolean result.
     *
     * @param   string  $p_field
     * @param   integer $p_method
     *
     * @return  boolean
     */
    public static function sm2_check($p_field, $p_method)
    {
        if (isset(self::$m_sm2_submitted["$p_field"])) {
            $l_field = self::$m_sm2_submitted["$p_field"];
            $l_result = false;

            switch ($p_method) {
                case 'SM2_METHOD_IS_EMPTY':
                    $l_result = empty($l_field);
                    break;

                case 'SM2_METHOD_IS_NUMERIC':
                    $l_result = is_numeric($l_field);
                    break;

                case 'SM2_METHOD_IS_VALID_SQL_DATE':
                    $l_result = preg_match("/^\d\d\d\d-\d\d-\d\d$/", $l_field);
                    break;

                case 'SM2_METHOD_IS_VALID_SQL_DATE_TIME':
                    $l_result = preg_match("/^\d\d\d\d-\d\d-\d\d \d\d:\d\d:\d\d$/", trim($l_field));
                    break;
            }

            return $l_result;
        }

        return false;
    }

    /**
     * Check, if the edit-mode is set.
     *
     * @return  Integer  If the edit-mode can be found, it is returned. If not, the parameter from the URI is taken.
     */
    public static function editmode()
    {
        global $g_navmode;
        $l_editmode = isys_glob_get_param(C__CMDB__GET__EDITMODE);

        if ($_POST[C__GET__NAVMODE] != C__NAVMODE__SAVE || (isset($g_navmode) && $g_navmode == C__NAVMODE__EDIT)) {
            if ($_POST[C__GET__NAVMODE] == C__NAVMODE__EDIT && $l_editmode == C__EDITMODE__OFF) {
                return !!C__EDITMODE__ON;
            }
        } else {
            return !!C__EDITMODE__OFF;
        }

        if ($_POST[C__GET__NAVMODE] == C__NAVMODE__NEW) {
            return !!C__EDITMODE__ON;
        }

        return $l_editmode;
    }

    /**
     * Append Javascript file to main <head>
     *
     * @param string $p_www_path_to_file Physical path to Javascript File
     *
     * @return isys_component_template
     */
    public function appendJavascript($p_www_path_to_file)
    {
        $this->append('jsFiles', $p_www_path_to_file);

        return $this;
    }

    /**
     * Append Javascript file to main <head>
     *
     * @param string $p_inlineJS Javascript Code
     *
     * @return isys_component_template
     */
    public function appendInlineJavascript($p_inlineJS)
    {
        $this->append('additionalInlineJS', $p_inlineJS);

        return $this;
    }

    /**
     * Sets an include for $p_key to $p_path in global variable $index_includes
     *
     * @param $p_key
     * @param $p_path
     *
     * @return $this
     */
    public function include_template($p_key, $p_path)
    {
        global $index_includes;
        $index_includes[$p_key] = $p_path;

        return $this;
    }

    /**
     * Returns css class for table row.
     *
     * @param   integer $p_i Counter.
     *
     * @return  string
     */
    public function row_background_color($p_i)
    {
        return ($p_i % 2 == 1) ? "CMDBListElementsEven" : "CMDBListElementsOdd";
    }

    /**
     * Globally activate the template editmode.
     *
     * @return  isys_component_template
     */
    public function activate_editmode()
    {
        global $g_navmode;

        $g_navmode = C__NAVMODE__EDIT;
        $_POST[C__GET__NAVMODE] = C__NAVMODE__EDIT;
        $_GET[C__CMDB__GET__EDITMODE] = C__EDITMODE__ON;

        return $this;
    }

    /**
     * Global i-doit function callback. All i-doit functions start with "isys". The handling is here. TOM is here.
     *
     * Example:
     *       [{isys type="lang" ident="DIES_IST_EINE_KONSTANTE"}]
     *       [{isys type="main_navi" ...}]
     *
     * @param   array $p_param
     *
     * @return  string
     *
     */
    public function smarty_function_isys($p_param)
    {
        $l_navmode = isys_glob_get_param('navMode');
        $l_editmode = self::editmode();

        if (isset($l_navmode)) {
            $l_classname = 'isys_smarty_plugin_' . $p_param['type'];

            if (@class_exists($l_classname)) {
                /**
                 * @var $l_myclass isys_smarty_plugin
                 */
                $l_myclass = new $l_classname;

                // Are there any rules to apply?
                if (isset($p_param['name'])) {
                    $l_stack = '';

                    if ($this->_cache['_tag_stack']) {
                        foreach ($this->_tag_stack as $l_tmpstack) {
                            $l_stack .= $l_tmpstack[1]['name'] . '.';
                        }
                    }

                    if (isset($this->m_ruleset[$l_stack . $p_param['name']])) {
                        /**
                         * Merge ruleset
                         * array_replace_recursive because if a key exists is in both arrays than array_merge_recursive
                         * creates an array for the key with both values
                         */
                        $p_param = array_replace_recursive($p_param, $this->m_ruleset[$l_stack . $p_param['name']]);
                    }
                }

                // Call smarty plugin dependant from the selected edit mode.
                switch ($l_editmode) {
                    default:
                    case C__EDITMODE__OFF:
                        $l_out = $l_myclass->navigation_view($this, $p_param);

                        // Localization integration for template fields.
                        if (isset($p_param["p_loc"]) && !empty($p_param["p_loc"])) {
                            $l_locale = isys_locale::get_instance();

                            $l_text = strip_tags($l_out);
                            $l_old = $l_text;

                            switch ($p_param["p_loc"]) {
                                case "datetime":
                                    $l_text = $l_locale->fmt_datetime($l_text);
                                    break;

                                case "date":
                                    $l_text = $l_locale->fmt_date($l_text);
                                    break;

                                case "date_short":
                                    $l_text = $l_locale->fmt_date($l_text, true);
                                    break;

                                case "date_long":
                                    $l_text = $l_locale->fmt_date($l_text, false);
                                    break;

                                case "time":
                                    $l_text = $l_locale->fmt_time($l_text);
                                    break;

                                case "time_short":
                                    $l_text = $l_locale->fmt_time($l_text, true);
                                    break;

                                case "time_long":
                                    $l_text = $l_locale->fmt_time($l_text, false);
                                    break;

                                case "money":
                                    $l_text = $l_locale->fmt_monetary($l_text);
                                    break;

                                case "money_short":
                                    $l_text = $l_locale->fmt_monetary($l_text);
                                    break;

                                case "money_long":
                                    $l_text = $l_locale->fmt_monetary($l_text);
                                    break;

                                case "numeric":
                                    $l_text = $l_locale->fmt_numeric($l_text);
                                    break;

                                default:
                            }

                            $l_rc = 1;
                            $l_out = str_replace($l_old, $l_text, $l_out, $l_rc);
                        }
                        break;

                    case C__EDITMODE__ON:
                        $l_out = $l_myclass->navigation_edit($this, $p_param);
                        break;
                }

                // SM2 : Processing.
                if (method_exists($l_myclass, 'get_meta_map') && $l_myclass->enable_meta_map()) {
                    $l_plugin_map = $l_myclass->get_meta_map();
                    $l_plugin_map[] = "type";

                    if (is_array($l_plugin_map)) {
                        $l_plugin_name = $p_param["name"];

                        if (!empty($l_plugin_name)) {
                            if (!@$p_param["p_plain"]) {
                                foreach ($l_plugin_map as $l_plugin_field) {
                                    if (!is_array($p_param[$l_plugin_field])) {
                                        $l_out .= "<input " . "type=\"hidden\" " . "name=\"SM2__{$l_plugin_name}[{$l_plugin_field}]\" " . "value='" .
                                            isys_glob_htmlentities(isys_glob_unescape($p_param[$l_plugin_field]), null) . "' " . "/>\n";
                                    } else {
                                        $l_out .= "<input " . "type=\"hidden\" " . "name=\"SM2__{$l_plugin_name}[{$l_plugin_field}]\" " . "value='" .
                                            isys_glob_htmlentities(serialize(isys_glob_unescape($p_param[$l_plugin_field])), null) . "' " . "/>\n";
                                    }
                                }
                            }
                        }
                    }
                }

                if (@$p_param["modifier"]) {
                    if (function_exists($p_param["modifier"])) {
                        $l_out = call_user_func($p_param["modifier"], $l_out);
                    }
                }

                return $l_out;
            }
        }

        return "";
    }

    /**
     * This is just an experimental plugin callback to determine the template grouping more easily.
     *
     * @param   array  $p_param
     * @param   string $p_content
     *
     * @return  string
     * @throws  isys_exception_template
     */
    public function smarty_tom_grouping($p_param, $p_content)
    {
        if (!empty($p_param["name"])) {
            return $p_content;
        }

        return "INVALID ISYS GROUP! NO NAME SET!";
    }

    /**
     * Add a TOM rule to the ruleset - these rules will be applied, while plugin callbacks are executed.
     *
     * @param   string $p_command
     *
     * @return  isys_component_template
     */
    public function smarty_tom_add_rule($p_command)
    {
        if (preg_match("/^([a-zA-Z0-9*\[\]\?._-]+)\\.(.*?)=(.+)$/is", $p_command, $l_regex)) {
            if (isset($l_regex[2]) && $l_regex[2] != "") {
                $this->m_ruleset[$l_regex[1]][$l_regex[2]] = $l_regex[3];
            }
        }

        return $this;
    }

    /**
     * Applies rules defined in $p_array to fields relative to $p_tomref.
     *
     * Example:
     *    smarty_tom_add_rules("tom.content", array(
     *       "FIELDNAME" => array(
     *          "ATTRIBUTE1" => "CONTENT",
     *          "ATTRIBUTE2" => "CONTENT"
     *       ),
     *       "FIELD2" => array(
     *          "ATTR1" => "CONTENT")
     *       )
     *    );
     *
     * @param   string $p_tomref
     * @param   array  $p_array
     *
     * @return  isys_component_template
     */
    public function smarty_tom_add_rules($p_tomref, $p_array)
    {
        if (is_array($p_array) && !empty($p_tomref)) {
            foreach ($p_array as $l_field => $l_data) {
                if (is_array($l_data) && count($l_data) > 0) {
                    foreach ($l_data as $l_key => $l_val) {
                        //$this->smarty_tom_add_rule($p_tomref . "." . $l_field . "." . $l_key . "=" . $l_val);
                        $this->m_ruleset[$p_tomref . '.' . $l_field][$l_key] = $l_val;
                    }
                }
            }
        }

        return $this;
    }

    /**
     * Returns an array containing template variables
     *
     * @param  string $name
     *
     * @return array
     */
    public function get_template_vars($name = null)
    {
        return $this->getTemplateVars($name);
    }

    /**
     * assigns a Smarty variable
     *
     * @param  array|string $tpl_var the template variable name(s)
     * @param  mixed        $value   the value to assign
     * @param  boolean      $nocache if true any output of this variable will be not cached
     *
     * @return $this
     */
    public function assign($tpl_var, $value = null, $nocache = false)
    {
        parent::assign($tpl_var, $value, $nocache);

        return $this;
    }

    /**
     * assigns values to template variables by reference
     *
     * @param string   $tpl_var the template variable name
     * @param          $value
     * @param  boolean $nocache if true any output of this variable will be not cached
     *
     * @return $this
     */
    public function assignByRef($tpl_var, &$value, $nocache = false)
    {
        parent::assignByRef($tpl_var, $value, $nocache);

        return $this;
    }

    /**
     * This is a wrapper method for allowing a little fluent-interface.
     *
     * @param   string $template
     * @param   string $cache_id
     * @param   string $compile_id
     * @param   string $parent
     *
     * @return  $this
     * @author  Leonard Fischer <lfischer@i-doit.org>
     * @since   0.9.9-9
     */
    public function display($template = null, $cache_id = null, $compile_id = null, $parent = null)
    {
        /* Emit signal beforeRender */
        isys_component_signalcollection::get_instance()
            ->emit('system.gui.beforeRender');

        parent::display($template, $cache_id, $compile_id, $parent);

        return $this;
    }

    /**
     * Returns the current ruleset as array.
     *
     * Example:
     *    array(
     *       0 => array(
     *          "filter" => array("TOM", "LOGIN", "BUTTON", ...),
     *          "data" => "string",
     *       ),
     *       ...
     *    )
     *
     * @return  array
     */
    public function &smarty_tom_get_current_ruleset()
    {
        return $this->m_ruleset;
    }

    /**
     * Initialize and assign some default variables used by smarty
     */
    private function initialize_defaults()
    {
        global $g_config, $g_dirs;
        // Assign current query string for ajax-submits to the right URL.
        $l_gets = $_GET;
        unset($l_gets[C__GET__AJAX_CALL], $l_gets[C__GET__AJAX]);

        // Assign the default theme and the product info and some directories:
        $this->assign('theme', $g_config['theme'])
            ->assign('dir_tools', $g_config['www_dir'] . 'src/tools/')
            ->assign('dir_images', $g_dirs['images'])
            ->assign('dir_theme', $g_dirs['theme'])
            ->assign('dirs', $g_dirs)
            ->assign('query_string', isys_glob_build_url((isys_glob_http_build_query($l_gets))))
            ->assign('html_encoding', $g_config['html-encoding'])
            ->assign('C__NAVMODE__SAVE', C__NAVMODE__SAVE)
            ->assign('C__NAVMODE__CANCEL', C__NAVMODE__CANCEL)
            ->assign('treemode', C__CMDB__GET__TREEMODE)
            ->assign('viewmode', C__CMDB__GET__VIEWMODE)
            ->assign('object', C__CMDB__GET__OBJECT)
            ->assign('objtype', C__CMDB__GET__OBJECTTYPE)
            ->assign('objgroup', C__CMDB__GET__OBJECTGROUP)
            ->assign('config', $g_config);

        if (class_exists('isys_application')) {
            $this->assign('gProductInfo', isys_application::instance()->info)
                ->assign('gLang', isys_application::instance()->language)
                ->assignByRef('session', isys_application::instance()->session);
        }

        if (isset($_SERVER['QUERY_STRING']) && $_SERVER['QUERY_STRING']) {
            $this->assign('formAdditionalAction', 'action="?' . htmlentities($_SERVER['QUERY_STRING']) . '"');
        }

        unset($l_gets);
    }

    /**
     * Constructs the template component.
     *
     * @throws SmartyException
     */
    public function __construct()
    {
        global $g_dirs, $g_comp_session;

        // Construct Smarty library
        parent::__construct();

        // Set directories.
        $this->setTemplateDir($g_dirs['smarty'] . 'templates/')
            ->setConfigDir($g_dirs['smarty'] . 'configs/')
            ->setCompileDir($g_dirs['temp'] . 'smarty/compiled/')
            ->setCacheDir($g_dirs['temp'] . 'smarty/cache/');
        // We DO NOT use caching, because smarty caches via variable name... But we re-use variable names everywhere :(

        $this->m_ruleset = [];
        $this->default_template_handler_func = 'isys_glob_template_handler';
        $this->debugging = false;
        $this->compile_check = true;
        $this->force_compile = false;

        if (is_object($g_comp_session)) {
            $this->compile_id = $g_comp_session->get_language();
        } else {
            $this->compile_id = 'en';
        }

        $this->left_delimiter = '[{';
        $this->right_delimiter = '}]';

        // Registering plugins,
        $this->registerPlugin('function', 'isys', [&$this, 'smarty_function_isys']);

        // Initialize TOM.
        $this->registerPlugin('block', 'isys_group', [&$this, 'smarty_tom_grouping']);

        // We just need to initialize this one time, since this is static.
        if (!is_array(self::$m_sm2_submitted)) {
            self::sm2_process_request_data();
        }

        // Initialize defaults
        $this->initialize_defaults();
    }
}

<?php

use idoit\Component\Helper\Purify;

/**
 * i-doit
 *
 * Abstract base class for i-doit modules.
 *
 * @package     i-doit
 * @subpackage  Modules
 * @author      i-doit Team <dev@i-doit.de>
 * @version     $Version$
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
abstract class isys_module implements isys_module_interface
{
    const DISPLAY_IN_MAIN_MENU   = false;
    const DISPLAY_IN_SYSTEM_MENU = false;

    /**
     * Is this module fully licenced.
     *
     * @var  boolean
     */
    private static $m_licenced = false;

    /**
     * Holds an instance of the module dao.
     *
     * @var  isys_module_dao
     */
    protected $m_dao;

    /**
     * Will hold "isys_module" table data.
     *
     * @var  array
     */
    protected $m_data;

    /**
     * @var isys_component_template_language_manager
     */
    protected $language;

    /**
     * @todo Introduce this in i-doit 1.17 after checking for old add-on versions (possibly in update).
     * @var isys_component_template
     */
    // protected $template;

    /**
     * @todo Introduce this in i-doit 1.17 after checking for old add-on versions (possibly in update).
     * @var isys_component_database
     */
    // protected $database;

    /**
     * We need this constructor until the 1.0, so we don't break the core.
     */
    public function __construct()
    {
        $this->language = isys_application::instance()->container->get('language');
        // $this->template = isys_application::instance()->container->get('template');
        // $this->database = isys_application::instance()->container->get('database');
    }

    /**
     * Is this module licenced, or not?
     *
     * @return  boolean
     */
    final public static function is_licenced()
    {
        return isset(static::$m_licenced) ? static::$m_licenced : true;
    }

    /**
     * Set licence status.
     *
     * @param  boolean $p_status
     */
    final public static function set_licenced($p_status = false)
    {
        if (isset(static::$m_licenced)) {
            static::$m_licenced = $p_status;
        }
    }

    /**
     * Get the absolute Add-on path with trailing slash (used for backend assets).
     *
     * @static
     * @since  i-doit 1.10
     * @return string
     */
    public static function getPath()
    {
        return __DIR__ . '/' . str_replace('isys_module_', '', static::class) . '/';
    }

    /**
     * Get the Add-on www path with trailing slash (used for frontend assets).
     *
     * @static
     * @since  i-doit 1.10
     * @return string
     */
    public static function getWwwPath()
    {
        return isys_application::instance()->www_path . 'src/classes/modules/' . str_replace('isys_module_', '', static::class) . '/';
    }

    /**
     * Signal Slot initialization.
     */
    public function initslots()
    {
        return false;
    }

    /**
     * Default start method.
     *
     * @return $this
     * @throws Exception
     */
    public function start()
    {
        if (func_num_args() > 0) {
            // This is a legacy load, so we're emulating the new Controller handling.
            $l_request = func_get_arg(0);

            if (!$l_request->module) {
                $l_request->module = str_replace('isys_module_', '', get_class($this));

                unset($l_request->action);

                \idoit\Controller\CatchallController::factory(isys_application::instance()->container)
                    ->handle($l_request);

                return $this;
            }
        } else {
            throw new RuntimeException('Module not compatible with i-doit ' . isys_application::instance()->info->get('version'));
        }

        return $this;
    }

    /**
     * Dummy method for building the menu-tree.
     *
     * @param   isys_component_tree $p_tree
     *
     * @author  Leonard Fischer <lfischer@i-doit.org>
     * @since   Version 0.9.9-7
     */
    public function build_tree(isys_component_tree $p_tree, $p_system_module = true, $p_parent = null)
    {
        // We don't declare this method as abstract anymore, to prevent errors while installing or updating.
    }

    /**
     * Callback function for construction of my-doit area.
     *
     * @return  boolean
     */
    public function mydoit_get(&$p_text, &$p_link)
    {
        return false;
    }

    /**
     * Custom handler for handling trial versions of this module.
     *
     * @param  isys_module_register $p_module_register
     * @param  integer              $p_end_date end date as timestamp
     *
     * @return isys_module
     * @throws Exception
     */
    public function start_trial(isys_module_register $p_module_register, $p_end_date)
    {
        isys_application::instance()->container->get('template')->assign('trialInfo', [
            'title'   => $this->language->get($p_module_register->get_data('isys_module__title')),
            'message' => $this->language->get('LC__LICENCE__TRIAL_INFO', isys_application::instance()->container->get('locales')->fmt_date($p_end_date))
        ]);

        return $this;
    }

    /**
     * Build breadcrumb navigation. Override for custom handling.
     *
     * @param   &$p_gets
     *
     * @return  array|null
     */
    public function breadcrumb_get(&$p_gets)
    {
        $l_return = [];

        /**
         * @var $l_breadcrumb \idoit\Model\Breadcrumb[]
         */
        $l_breadcrumb = $this->get('breadcrumb');

        if (is_array($l_breadcrumb)) {
            foreach ($l_breadcrumb as $l_b) {
                if (is_a($l_b, 'idoit\Model\Breadcrumb')) {
                    $l_return[] = [$l_b->title => $l_b->parameters];
                }
            }
        }

        return $l_return;
    }

    /**
     * @param   array $p_data
     *
     * @return  $this
     */
    public function set_data($p_data)
    {
        $this->m_data = $p_data;

        return $this;
    }

    /**
     * @return      $this
     * @deprecated  Will be removed in i-doit 1.16
     */
    public function set()
    {
        return $this;
    }

    /**
     * @return      null
     * @deprecated  Will be removed in i-doit 1.16
     */
    public function get()
    {
        return null;
    }

    /**
     * Get module DAO.
     */
    public function get_dao()
    {
        return $this->m_dao;
    }

    /**
     * Checks if a module is installed.
     *
     * @param  string  $p_identifier
     * @param  boolean $p_and_active
     *
     * @return mixed
     * @throws isys_exception_database
     */
    public function is_installed($p_identifier = null, $p_and_active = false)
    {
        global $g_comp_database;

        if (is_object($g_comp_database)) {
            $l_dao = new isys_component_dao($g_comp_database);

            if (!$p_identifier) {
                $l_sql = 'SELECT isys_module__id FROM isys_module WHERE isys_module__class = ' . $l_dao->convert_sql_text(get_class($this));
            } else {
                $l_sql = 'SELECT isys_module__id FROM isys_module WHERE isys_module__identifier = ' . $l_dao->convert_sql_text($p_identifier);
            }

            if ($p_and_active) {
                $l_sql .= ' AND isys_module__status = ' . $l_dao->convert_sql_int(C__RECORD_STATUS__NORMAL);
            }

            $l_id = $l_dao->retrieve($l_sql . ';')
                ->get_row_value('isys_module__id');

            return $l_id ?: false;
        }

        return false;
    }

    /**
     * Prepares user data assignments to UI.
     *
     * @param   array $p_properties Properties
     * @param   array $p_data       (optional) Data. Defaults to null.
     * @param   array $p_result     (optional) Validation result. Defaults to null.
     *
     * @return  array Associative array
     * @author  Benjamin Heisig <bheisig@synetics.de>
     */
    protected function prepare_user_data_assignment($p_properties, $p_data = null, $p_result = null)
    {
        $l_content = [];
        $l_request = isys_request::factory();

        // Iterate through each property:
        foreach ($p_properties as $l_property_id => $l_property_info) {
            $l_value = null;
            $l_ui = [];

            if (!array_key_exists(C__PROPERTY__UI, $l_property_info)) {
                // There is no information about the UI. Skipping.
                continue;
            }

            if (is_array($p_data) && array_key_exists($l_property_id, $p_data)) {
                if (is_array($p_result) && array_key_exists($l_property_id, $p_result) && $p_result[$l_property_id] !== isys_module_dao::C__VALIDATION_RESULT__NOTHING) {
                    // Validation failed.

                    switch ($p_result[$l_property_id]) {
                        case isys_module_dao::C__VALIDATION_RESULT__MISSING:
                            // @todo  Check if "p_strInfoIconError" can be removed.
                            $l_ui['p_strInfoIconError'] = $this->language->get('LC__UNIVERSAL__MANDATORY_FIELD_IS_EMPTY');
                            $l_ui['message'] = $this->language->get('LC__UNIVERSAL__MANDATORY_FIELD_IS_EMPTY');
                            break;
                        case isys_module_dao::C__VALIDATION_RESULT__INVALID:
                            // @todo  Check if "p_strInfoIconError" can be removed.
                            $l_ui['p_strInfoIconError'] = $this->language->get('LC__UNIVERSAL__FIELD_VALUE_IS_INVALID');
                            $l_ui['message'] = $this->language->get('LC__UNIVERSAL__FIELD_VALUE_IS_INVALID');
                            break;
                    }
                }

                $l_value = $p_data[$l_property_id];
            }

            // Use default value, if nothing is given:
            if ($l_value === null && array_key_exists('default', $l_property_info[C__PROPERTY__DATA])) {
                $l_value = $l_property_info[C__PROPERTY__DATA]['default'];
            }

            // Assign value:
            switch ($l_property_info[C__PROPERTY__UI][C__PROPERTY__UI__TYPE]) {
                case C__PROPERTY__UI__TYPE__TEXT:
                case C__PROPERTY__UI__TYPE__TIME:
                case C__PROPERTY__UI__TYPE__TEXTAREA:
                    $l_ui['p_strValue'] = $l_value;
                    break;
                case C__PROPERTY__UI__TYPE__POPUP:
                    if ($l_property_info[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_strPopupType'] === 'calendar') {
                        $l_ui['p_strValue'] = $l_value;
                    } else {
                        $l_ui['p_strSelectedID'] = isys_format_json::encode($l_value);
                    }
                    break;
                case C__PROPERTY__UI__TYPE__DIALOG:
                    $l_ui['p_strSelectedID'] = $l_value;
                    break;
                case C__PROPERTY__UI__TYPE__DIALOG_LIST:
                    // @todo Assignment is currently done manually...
                    break;
                case C__PROPERTY__UI__TYPE__CHECKBOX:
                    if ($l_value) {
                        $l_ui['p_bChecked'] = '1';
                    }
                    break;
                case C__PROPERTY__UI__TYPE__PROPERTY_SELECTOR:
                    $l_ui['preselection'] = $l_value;
                    break;
            }

            // Assign mandatory attribute to lable with the same name attribute as the property form tag:
            if ($l_property_info[C__PROPERTY__CHECK][C__PROPERTY__CHECK__MANDATORY]) {
                $l_ui[C__PROPERTY__CHECK__MANDATORY] = true;
            }

            // Assign description attribute to lable with the same name attribute as the property form tag:
            if (isset($l_property_info[C__PROPERTY__INFO][C__PROPERTY__INFO__DESCRIPTION])) {
                $l_ui['description'] = $l_property_info[C__PROPERTY__INFO][C__PROPERTY__INFO__DESCRIPTION];
            }

            // Assign default value attribute:
            if (isset($l_property_info[C__PROPERTY__UI]['default'])) {
                // First, try to use the default value specified for the user interface:
                $l_default = $l_property_info[C__PROPERTY__UI]['default'];

                if ($l_default === null) {
                    $l_default = $this->language->get('LC__UNIVERSAL__EMPTY');
                }

                $l_ui['default'] = $l_default;
            } elseif (isset($l_property_info[C__PROPERTY__DATA]['default'])) {
                // Alternatively, try to use the default value from the data model:
                $l_default = $l_property_info[C__PROPERTY__DATA]['default'];

                if ($l_default === null) {
                    $l_default = $this->language->get('LC__UNIVERSAL__EMPTY');
                }

                $l_ui['default'] = $l_default;
            }

            // Assign all parameters for the smarty plugin:
            if (array_key_exists(C__PROPERTY__UI__PARAMS, $l_property_info[C__PROPERTY__UI])) {
                $l_ui = array_merge($l_ui, $l_property_info[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]);
            }

            if (isset($l_property_info[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_arData'])) {
                $l_arData = $l_property_info[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_arData'];

                switch (gettype($l_arData)) {
                    default:
                    case 'array':
                        $l_ui['p_arData'] = $l_arData;
                        break;
                    case 'object':
                        if (get_class($l_arData) == 'isys_callback') {
                            $l_ui['p_arData'] = $l_arData->execute($l_request);
                        }
                        break;
                }
            }

            // Assign content:
            $l_ui_id = $l_property_info[C__PROPERTY__UI][C__PROPERTY__UI__ID];
            $l_content[$l_ui_id] = $l_ui;
        }

        return $l_content;
    }

    /**
     * Parses user data from HTTP GET and POST.
     *
     * @param  array $p_properties Fetch these properties.
     *
     * @return array  Associative array of parsed property data
     * @throws \idoit\Exception\JsonException
     * @author Benjamin Heisig <bheisig@synetics.de>
     */
    protected function parse_user_data($p_properties)
    {
        return $this->get_dao()
            ->transformDataByProperties($p_properties, $this->m_userrequest->get_posts());
    }

    /**
     * Validates properties' data.
     *
     * @param   array   $p_properties
     * @param   array   $p_data
     * @param   boolean $p_ignore (optional) Ignore missing properties which could be mandatory. Defaults to false.
     *
     * @return  array Associative array of integers
     * @author  Benjamin Heisig <bheisig@synetics.de>
     */
    protected function validate_property_data($p_properties, $p_data, $p_ignore = false)
    {
        $l_result = [];

        foreach ($p_properties as $l_property_id => $l_property_info) {
            $l_result[$l_property_id] = isys_module_dao::C__VALIDATION_RESULT__NOTHING;

            // Field is missing, but it will be ignored:
            if (($p_ignore === true && !array_key_exists($l_property_id, $p_data)) || $l_property_info[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_bReadonly'] === 'true' ||
                $l_property_info[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_bReadonly'] > 0) {
                $l_result[$l_property_id] = isys_module_dao::C__VALIDATION_RESULT__IGNORED;
                continue;
            }

            // Mandatory field is missing:
            if ($p_ignore === false && (!array_key_exists($l_property_id, $p_data) || !isset($p_data[$l_property_id])) &&
                $l_property_info[C__PROPERTY__CHECK][C__PROPERTY__CHECK__MANDATORY] === true) {
                $l_result[$l_property_id] = isys_module_dao::C__VALIDATION_RESULT__MISSING;
                continue;
            }

            if (isset($p_data[$l_property_id]) && array_key_exists(C__PROPERTY__CHECK, $l_property_info) &&
                array_key_exists(C__PROPERTY__CHECK__VALIDATION, $l_property_info[C__PROPERTY__CHECK]) &&
                filter_var(
                    $p_data[$l_property_id],
                    $l_property_info[C__PROPERTY__CHECK][C__PROPERTY__CHECK__VALIDATION][0],
                    $l_property_info[C__PROPERTY__CHECK][C__PROPERTY__CHECK__VALIDATION][1]
                ) === false) {
                $l_result[$l_property_id] = isys_module_dao::C__VALIDATION_RESULT__INVALID;
            }
        }

        return $l_result;
    }

    /**
     * returns purified get parameters
     *
     * @return array
     */
    public static function getGetParameters()
    {
        return Purify::purifyParams($_GET);
    }

    /**
     * returns purified get parameter
     *
     * @param $key
     *
     * @return mixed
     */
    public static function getGetParameter($key)
    {
        return Purify::purifyParameter($key, $_GET);
    }

    /**
     * returns purified post parameters
     *
     * @return array
     */
    public static function getPostParameters()
    {
        return Purify::purifyParams($_POST);
    }

    /**
     * returns purified post parameter
     *
     * @param $key
     *
     * @return mixed
     */
    public static function getPostParameter($key)
    {
        return Purify::purifyParameter($key, $_POST);
    }

    /**
     * @param $value
     *
     * @return mixed|object|null
     * @throws Exception
     */
    public static function purifyValue($value)
    {
        return Purify::purifyValue($value);
    }
}

<?php

/**
 * i-doit
 *
 * Base class for popups. All popups are located in the directory "src/classes/popups".
 * The classes in it have to be inherited from this class.
 *
 * @package     i-doit
 * @subpackage  Components
 * @author      Andre Woesten <awoesten@i-doit.de>
 * @version     0.9
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
abstract class isys_component_popup extends isys_component
{
    /**
     * @var string
     */
    private $m_popupname;

    /**
     * @var isys_component_database
     */
    protected $database;

    /**
     * @var isys_component_template_language_manager
     */
    protected $language;

    /**
     * @var isys_component_template
     */
    protected $template;

    /**
     * @deprecated  Please use $this->database
     * @var isys_component_database
     */
    protected $db;

    /**
     * @deprecated  Please use $this->language
     * @var isys_component_template_language_manager
     */
    protected $lang;

    /**
     * Abstract method for handling module request.
     *
     * @param  isys_module_request $p_modreq
     *
     * @return isys_component_template
     */
    abstract public function &handle_module_request(isys_module_request $p_modreq);

    /**
     * Formats the popup selection.
     *
     * @param   integer $p_id
     * @param   boolean $p_plain
     *
     * @return  string
     */
    public function format_selection($p_id, $p_plain = false)
    {
        return '';
    }

    /**
     * Gets popupname.
     *
     * @return  string
     */
    public function get_popupname()
    {
        return $this->m_popupname;
    }

    /**
     * Process the overlay page.
     *
     * @param  string     $p_url
     * @param  int|string $width               Set exact pixel size via (int) 640 or a relative via "60%".
     * @param  int|string $height              Set exact pixel size via (int) 480 or a relative via "40%".
     * @param  array      $params
     * @param  string     $popupReceiver
     * @param  integer    $minWidth            This will only be used, if a relative size is applied.
     * @param  integer    $minHeight           This will only be used, if a relative size is applied.
     * @param  integer    $maxWidth            This will only be used, if a relative size is applied.
     * @param  integer    $maxHeight           This will only be used, if a relative size is applied.
     * @param  string     $configurationField  New parameter, implemented because of ID-4514 - will read the configuration "on-the-fly" so it is possible to change it.
     *
     * @return string
     */
    public function process_overlay($p_url, $width = 950, $height = 550, $params = [], $popupReceiver = null, $minWidth = null, $minHeight = null, $maxWidth = null, $maxHeight = null, $configurationField = null)
    {
        $l_popup = str_replace("isys_popup_", "", get_class($this));

        if ($configurationField !== null) {
            // @see  ID-4514  Here we'll read the configuration "on-the-fly" during runtime.
            $l_params = "{params:btoa(\$F('" . $configurationField . "'))}";
        } else {
            $l_params = "{params:'" . base64_encode(isys_format_json::encode($params)) . "'}";
        }

        return "get_popup('" . $l_popup . "', '" . $p_url . "', '" . $width . "', '" . $height . "', " . $l_params . ", " .
            ($popupReceiver ? "'" . $popupReceiver . "'" : 'null') . "," .
            ($minWidth ? "'" . $minWidth . "'" : 'null') . "," .
            ($minHeight ? "'" . $minHeight . "'" : 'null') . "," .
            ($maxWidth ? "'" . $maxWidth . "'" : 'null') . "," .
            ($maxHeight ? "'" . $maxHeight . "'" : 'null') . ");";
    }

    /**
     * Returns a javascript function to display the object browser.
     *
     * @param  array      $p_params
     * @param  int|string $width     Set exact pixel size via (int) 640 or a relative via "60%".
     * @param  int|string $height    Set exact pixel size via (int) 480 or a relative via "40%".
     * @param  integer    $minWidth  This will only be used, if a relative size is applied.
     * @param  integer    $minHeight This will only be used, if a relative size is applied.
     * @param  integer    $maxWidth  This will only be used, if a relative size is applied.
     * @param  integer    $maxHeight This will only be used, if a relative size is applied.
     *
     * @return string
     */
    public function get_js_handler($p_params, $width = 1100, $height = 650, $minWidth = null, $minHeight = null, $maxWidth = null, $maxHeight = null)
    {
        return $this->process_overlay('', $width, $height, $p_params, null, $minWidth, $minHeight, $maxWidth, $maxHeight);
    }

    /**
     * Popup constructor.
     *
     * @throws Exception
     */
    public function __construct()
    {
        $this->database = isys_application::instance()->container->get('database');
        $this->language = isys_application::instance()->container->get('language');
        $this->template = isys_application::instance()->container->get('template');

        // @todo  Keep these two as "alias" for compability reasons... Remove after 1.12!
        $this->db = $this->database;
        $this->lang = $this->language;

        $this->m_popupname = 'isysPopup' . rand(10, 50);
    }

    /**
     * Was used to set internal configuration - we don't use this anymore.
     *
     * @deprecated  Old, unsupported method.
     * @todo        Remove in i-doit 1.12
     * @return      null
     */
    public function set_config()
    {
        return null;
    }

    /**
     * Was used to get a certain configuration item - we don't use this anymore.
     *
     * @deprecated  Old, unsupported method.
     * @todo        Remove in i-doit 1.12
     * @return      null
     */
    public function get_config()
    {
        return null;
    }

    /**
     * Returned the internal configuration - we don't use this anymore.
     *
     * @deprecated  Old, unsupported method.
     * @todo        Remove in i-doit 1.12
     * @return      array
     */
    public function get_config_array()
    {
        return [];
    }

    /**
     * Created a "real" browser popup - we don't use this anymore.
     *
     * @deprecated  Old, unsupported method.
     * @todo        Remove in i-doit 1.12
     * @return      string
     */
    public function process()
    {
        return '';
    }
}

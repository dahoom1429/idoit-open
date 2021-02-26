<?php

use idoit\Component\Helper\Purify;

/**
 * i-doit
 *
 * Belong to the CMDB module and defines the structure for a view. All
 * views preserve an ID, by which they get identified. This ID must be
 * a field in the view bitfields in isys_module_cmdb
 *
 * @package     i-doit
 * @subpackage  CMDB_Views
 * @author      Andre Woesten <awoesten@i-doit.de>
 * @version     0.9
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
abstract class isys_cmdb_view
{
    /**
     * @var isys_cmdb_action_processor
     */
    protected $m_action_proc;

    /**
     * @var isys_cmdb_dao
     */
    protected $m_dao_cmdb;

    /**
     * @var isys_module_request
     */
    protected $m_modreq;

    /**
     * @var boolean
     */
    protected $m_require_module_reload;

    /**
     * Array with rights
     *
     * array(
     *      isys_auth::VIEW    => true
     *      isys_auth::ARCHIVE => true
     *      isys_auth::DELETE  => true
     *      isys_auth::EDIT    => true
     * )
     *
     * @var array
     */
    protected $m_rights = [];

    /**
     * Returns view ID / constant.
     *
     * @abstract
     * @return  integer
     */
    abstract public function get_id(); // function

    /**
     * Writes mandatory parameters to $l_gets.
     *
     * @abstract
     *
     * @param  array & $l_gets
     */
    abstract public function get_mandatory_parameters(&$l_gets); // function

    /**
     * Returns view name.
     *
     * @abstract
     * @return  string
     */
    abstract public function get_name(); // function

    /**
     * Write optional parameters to $l_gets.
     *
     * @abstract
     *
     * @param  array & $l_gets
     */
    abstract public function get_optional_parameters(&$l_gets); // function

    /**
     * Process view.
     *
     * @abstract
     */
    abstract public function process(); // function

    /**
     * Dummy navmode handler for child classes without one (like MISC Views).
     *
     * @param   integer $p_navmode
     *
     * @throws  isys_exception_cmdb
     */
    public function handle_navmode($p_navmode)
    {
        throw new isys_exception_cmdb("Could not handle navmode. We are in isys_cmdb_view.class.php. handle_navmode is just an abstract handler here.");
    }

    /**
     * Dummy navbar customizer for child classes.
     *
     * @param   isys_component_dao_result      $p_listres
     * @param   isys_component_template_navbar $p_navbar
     *
     * @throws  isys_exception_cmdb
     */
    public function customize_navbar(isys_component_dao_result &$p_listres, isys_component_template_navbar &$p_navbar)
    {
        throw new isys_exception_cmdb("Could not handle navbar. We are in isys_cmdb_view.class.php. Here handle_navmode is just an abstract handler.");
    }

    /**
     * Returns name of template placeholder, which is designated for the data returned by "process()".
     *
     * @return  null
     */
    public function get_template_destination()
    {
        return null;
    }

    /**
     * Returns name of template to show in the content top view.
     *
     * @return  null
     */
    public function get_template_top()
    {
        return null;
    }

    /**
     * Returns filename of template to show in the content bottom view.
     *
     * @return null
     */
    public function get_template_bottom()
    {
        return null;
    }

    /**
     * Returns the object with the module-specific request.
     *
     * @final
     * @return  isys_module_request
     */
    final public function &get_module_request()
    {
        return $this->m_modreq;
    }

    /**
     * Returns the DAO for the CMDB.
     *
     * @final
     * @return  isys_cmdb_dao
     */
    final public function get_dao_cmdb()
    {
        return $this->m_dao_cmdb;
    }

    /**
     * Triggers a module reload.
     *
     * @final
     */
    final public function trigger_module_reload()
    {
        $this->m_require_module_reload = !$this->m_require_module_reload;
    }

    /**
     * Checks if a module reload is required.
     *
     * @final
     * @return  boolean
     * @author  André Wösten <awoesten@i-doit.org>
     */
    final public function requires_module_reload()
    {
        return $this->m_require_module_reload;
    }

    /**
     * This routines rewrites the action-attribute of isys_form.
     *
     * @final
     */
    final public function readapt_form_action()
    {
        $l_gets = $this->get_module_request()
            ->get_gets();

        $l_gets = Purify::castIntValues($l_gets);

        // Unset unused variables.
        unset($l_gets[C__GET__AJAX_CALL], $l_gets[C__GET__AJAX], $l_gets[C__GET__NAVMODE]);

        if ($l_gets[C__CMDB__GET__CATLEVEL] == "-1") {
            $l_gets[C__CMDB__GET__CATLEVEL] = $_GET[C__CMDB__GET__CATLEVEL];

            if ($l_gets[C__CMDB__GET__CATLEVEL] == "-1") {
                unset($l_gets[C__CMDB__GET__CATLEVEL]);
            }
        }

        $l_url = isys_glob_build_url((isys_glob_http_build_query($l_gets)));

        isys_application::instance()->template->clearAssign("formAdditionalAction")
            ->assign("formAdditionalAction", "action=\"{$l_url}\"")
            ->assign("query_string", $l_url);
    }

    /**
     * Returns the object with the action processor for the CMDB.
     *
     * @final
     * @return  isys_cmdb_action_processor
     */
    final protected function &get_action_processor()
    {
        return $this->m_action_proc;
    }

    /**
     * Returns a category level from the GET-Parameters:
     *
     * - $_GET[cateID] has the most important priority.
     * - Then the routine is counting from cat1ID to catXID, where X is C__CMD__GET__CATLEVEL_MAX.
     * - The routine returns -1 if wrong data have been given.
     *
     * @final
     *
     * @param   integer $p_level
     *
     * @return  mixed  Integer on success, null on wrong parameters or failure.
     * @author  André Wösten <awoesten@i-doit.org>
     */
    final protected function get_category_level($p_level)
    {
        $l_gets = $this->get_module_request()
            ->get_gets();

        $l_gets = Purify::castIntValues($l_gets);

        // Check level.
        if ((!is_numeric($p_level)) || ($p_level < 1) || ($p_level > C__CMDB__GET__CATLEVEL_MAX)) {
            return null;
        }

        /*
         * Aus Gegebenheiten des alten CMDB Moduls musste ich eine Möglichkeit
         * einbauen, direkt cateID zurückzugeben. Manche Kategorien nutzen das noch
         */
        if (isset($l_gets[C__CMDB__GET__CATLEVEL])) {
            return $l_gets[C__CMDB__GET__CATLEVEL];
        }

        $l_p = constant("C__CMDB__GET__CATLEVEL_" . $p_level);

        if (isset($l_gets[$l_p])) {
            return $l_gets[$l_p];
        } else {
            return null;
        }
    }

    /**
     * Returns the "next" category level, means:
     *
     * - cat1ID to catXID if cateID is not set
     * - If cateID is set $p_level is 0 and returns $_GET[cateID]
     * - If none of them are set, -1 is returned ...
     *
     * @final
     *
     * @param   integer $p_level
     *
     * @return  mixed
     * @author  André Wösten <awoesten@i-doit.org>
     */
    final protected function get_next_category_level(&$p_level)
    {
        global $g_catlevel;

        $l_gets = $this->get_module_request()
            ->get_gets();

        $l_gets = Purify::castIntValues($l_gets);

        // @todo AW: Die Sache mit dem Catlevel fixen ...!!!
        if (isset($l_gets[C__CMDB__GET__CATLEVEL])) {
            $p_level = (is_null($g_catlevel)) ? 0 : $g_catlevel;

            return $l_gets[C__CMDB__GET__CATLEVEL];
        }

        for ($l_c = 1;$l_c <= C__CMDB__GET__CATLEVEL_MAX;$l_c++) {
            $l_catret = $this->get_category_level($l_c);

            if (is_numeric($l_catret) && $l_catret !== null) {
                $p_level = $l_c;

                return $l_catret;
            }
        }

        return null;
    }

    /**
     * Constructor, can only be instantiated from child classes.
     *
     * @param  isys_module_request $p_modreq
     */
    public function __construct(isys_module_request $p_modreq)
    {
        $this->m_modreq = $p_modreq;
        $this->m_dao_cmdb = new isys_cmdb_dao($this->m_modreq->get_database());
        $this->m_action_proc = new isys_cmdb_action_processor($this->m_dao_cmdb);
        $this->m_require_module_reload = false;
    }
}

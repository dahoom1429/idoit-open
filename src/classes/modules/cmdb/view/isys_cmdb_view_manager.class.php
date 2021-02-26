<?php

/**
 * CMDB VIEW MANAGER
 *
 * @package     i-doit
 * @subpackage  CMDB_Views
 * @author      Andre Woesten <awoesten@i-doit.de>
 * @version     Dennis Stücken <dstuecken@i-doit.org>
 * @version     1.0
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_view_manager extends isys_component
{
    /**
     * @var  isys_module_request
     */
    private $m_modreq;

    /**
     * @var  array
     */
    private $m_views = [];

    /**
     * Register a view in the view manager.
     *
     * @param isys_cmdb_view|string $p_view
     * @param null                  $p_view_id
     *
     * @throws isys_exception_cmdb
     * @return boolean
     */
    public function register($p_view, $p_view_id = null)
    {
        $l_viewid = null;
        if (is_object($p_view)) {
            $l_viewid = $p_view->get_id();
        } elseif ($p_view_id) {
            $l_viewid = $p_view_id;
        }

        if ($l_viewid) {
            $this->m_views[$l_viewid] = $p_view;

            return $l_viewid;
        }

        throw new isys_exception_cmdb("View manager: could not register $p_view");
    }

    /**
     * Unregisters a view with the specified ID.
     *
     * @param   integer $p_viewid
     *
     * @return  boolean
     */
    public function unregister($p_viewid)
    {
        if (array_key_exists($p_viewid, $this->m_views)) {
            unset($this->m_views[$p_viewid]);

            return true;
        }

        return false;
    }

    /**
     * Returns a view object.
     *
     * @param   integer $p_viewid
     *
     * @throws  Exception|isys_exception
     * @return  isys_cmdb_view_category
     */
    public function &get_view($p_viewid)
    {
        // Set viewid parameter to an object list if it is malformed to prevent error "Function get_view(undefined) is not available."
        if (!$p_viewid || !is_scalar($p_viewid) || !isset($this->m_views[$p_viewid])) {
            $p_viewid = C__CMDB__VIEW__LIST_OBJECT;
        }

        if (is_object($this->m_views[$p_viewid])) {
            return $this->m_views[$p_viewid];
        }

        try {
            $l_view = $this->m_views[$p_viewid];

            if (class_exists($l_view)) {
                return new $l_view($this->m_modreq);
            }

            throw new isys_exception_missing_function(ucfirst(str_replace('isys_cmdb_view_', '', $l_view)));
        } catch (isys_exception_general $e) {
            throw $e;
        }
    }

    /**
     * Constructor.
     */
    public function __construct(isys_module_request &$p_modreq)
    {
        $this->m_modreq = &$p_modreq;
    }
}

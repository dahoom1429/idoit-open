<?php

/**
 * CMDB Tree view for locations
 *
 * @package     i-doit
 * @subpackage  CMDB_Views
 * @author      Andre Woesten <awoesten@i-doit.de>
 * @version     1.0
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_view_tree_location extends isys_cmdb_view_tree
{
    protected $m_treeName = 'menuTreeJS';

    public function get_id()
    {
        return C__CMDB__VIEW__TREE_LOCATION;
    }

    public function get_mandatory_parameters(&$l_gets)
    {
        parent::get_mandatory_parameters($l_gets);
    }

    public function get_name()
    {
        return "Location tree";
    }

    public function get_optional_parameters(&$l_gets)
    {
        parent::get_optional_parameters($l_gets);

        $l_gets[C__CMDB__GET__OBJECTGROUP] = true;
    }

    /**
     * Build the tree.
     */
    public function tree_build()
    {
        global $g_dirs;

        // Prepare some variables.
        $l_gets = $this->get_module_request()
            ->get_gets();
        $l_db = $this->get_module_request()
            ->get_database();
        $l_user_locale = new isys_locale($l_db);
        $l_user_locale->init($_SESSION['session_data']['isys_user_session__isys_obj__id']);

        $this->remove_ajax_parameters($l_gets);

        $l_objid = $l_gets[C__CMDB__GET__OBJECT];

        if (isset($l_gets['treePath']) && !empty($l_gets['treePath'])) {
            $l_hierarchy = $l_gets['treePath'];
        } else {
            if (in_array($l_user_locale->get_setting('tree_type'), [
                C__CMDB__VIEW__TREE_LOCATION__LOGICAL_UNITS,
                C__CMDB__VIEW__TREE_LOCATION__LOCATION
            ])) {
                if (!empty($l_objid)) {
                    $l_dao_loc = ($l_user_locale->get_setting('tree_type') ==
                        C__CMDB__VIEW__TREE_LOCATION__LOGICAL_UNITS) ? new isys_cmdb_dao_category_g_logical_unit($l_db) : new isys_cmdb_dao_location($l_db);
                    $l_hierarchy = $l_dao_loc->get_node_hierarchy($l_objid, true);
                } else {
                    $l_hierarchy = "";
                }
            } else {
                // Special Handling for the combined View.
                $l_dao_location = new isys_cmdb_dao_location($l_db);
                $l_dao_logical = new isys_cmdb_dao_category_g_logical_unit($l_db);
                $l_hierarchy = $l_dao_location->get_node_hierarchy($l_objid, true);

                // Is there no physical path to the Object.
                if ($l_hierarchy == ',1') {
                    $l_logical_hierarchy = $l_dao_logical->get_node_hierarchy($l_objid);

                    // Is there a logical path to the Object.
                    if (count(explode(",", $l_logical_hierarchy)) > 1) {
                        $l_tmp = explode(",", $l_logical_hierarchy);
                        $l_tmp_1 = $l_dao_location->get_node_hierarchy($l_tmp[1], true);

                        // Have the Workstation a physical location.
                        if ($l_tmp_1 == ',1') {
                            // If not: Hierarchy is equal logical.
                            $l_hierarchy = $l_logical_hierarchy;
                        } else {
                            $l_hierarchy = $l_tmp[0] . "," . $l_tmp_1;
                        }
                    } else {
                        $l_hierarchy = $l_objid;
                    }
                }
            }
        }

        // Set ajax url for tree.
        $l_str_ajaxurl = "?" . C__GET__AJAX_CALL . "=tree_level&" . C__GET__AJAX . "=1&hide_root=1&id={0}";

        // Prepare tree.
        $this->m_tree = new isys_component_ajaxtree($this->m_treeName, $l_str_ajaxurl, $g_dirs['images'] . "icons/silk/house.png", 'Root', $l_hierarchy);

        // Emit signal.
        isys_component_signalcollection::get_instance()
            ->emit("mod.cmdb.extendLocationTree", $this->m_tree);
    }

    public function tree_process()
    {
        return $this->m_tree->process($this->m_select_node);
    }

    public function __construct(isys_module_request $p_modreq)
    {
        parent::__construct($p_modreq);

        $this->m_selected = 0;
    }
}
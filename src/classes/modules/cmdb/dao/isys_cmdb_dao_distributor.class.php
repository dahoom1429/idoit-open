<?php

/**
 * i-doit
 *
 * DAO: CMDB Distributor
 *
 * @package    i-doit
 * @subpackage CMDB_Low-Level_API
 * @author     Andre Woesten <awoesten@i-doit.de>
 * @version    Dennis Stuecken <dstuecken@i-doit.de>
 * @copyright  synetics GmbH
 * @license    http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_distributor extends isys_cmdb_dao
{
    /**
     * List of fetched categories.
     *
     * @var  array
     */
    private $m_categories = [];

    /**
     * Holds the isysguidata for each category.
     *
     * @var  array
     */
    private $m_guidata = [];

    /**
     * @return array
     */
    public static function make_category_filter()
    {
        $l_dstarr = [];
        foreach (func_get_args() as $p_catid) {
            $l_dstarr[$p_catid] = true;
        }

        return $l_dstarr;
    }

    /**
     * Returns the array with category DAO objects.
     *
     * @return  array
     */
    public function get_categories()
    {
        return $this->m_categories;
    }

    /**
     * Returns the guidata of a category
     *
     * @param int $p_catid
     *
     * @return mixed
     */
    public function get_guidata($p_catid)
    {
        return (isset($this->m_guidata[$p_catid])) ? $this->m_guidata[$p_catid] : null;
    }

    /**
     * Return the category DAO object specified by $p_index.
     *
     * @param   integer $p_index
     *
     * @return  isys_cmdb_dao_category  May be any specified category DAO instance.
     */
    public function get_category($p_index)
    {
        return (isset($this->m_categories[$p_index])) ? $this->m_categories[$p_index] : null;
    }

    /**
     * Return count of fetched categories through the distributor.
     *
     * @return  integer
     */
    public function count()
    {
        return count($this->m_categories);
    }

    /**
     *
     * @param   integer $p_disttype
     *
     * @return  string
     */
    public function resolve_disttype($p_disttype)
    {
        switch ($p_disttype) {
            default:
            case C__CMDB__CATEGORY__TYPE_GLOBAL:
                return "g";
            case C__CMDB__CATEGORY__TYPE_SPECIFIC:
                return "s";
            case C__CMDB__CATEGORY__TYPE_CUSTOM:
                return "g_custom";
        }
    }

    /**
     *  Resolves the categories for the specified Object-ID and distributor type. The results are stored into self::$m_categories.
     *
     * @param  isys_component_database $p_db
     * @param  integer                 $p_objid
     * @param  integer                 $p_disttype
     * @param  integer                 $p_list_id
     * @param  mixed                   $p_categories
     */
    public function __construct(isys_component_database &$p_db, $p_objid, $p_disttype = C__CMDB__CATEGORY__TYPE_GLOBAL, $p_list_id = null, $p_categories = null)
    {
        parent::__construct($p_db);

        // Category type (g-s-g_custom).
        $l_cattype = $this->resolve_disttype($p_disttype);
        $l_cattable = "isysgui_cat" . $l_cattype;

        // Get the almighty cmdb_dao.
        $l_dao_cmdb = new isys_cmdb_dao($p_db);

        // Get categories (isysgui_catx).
        $l_cats = $this->get_isysgui($l_cattable, $p_categories);

        if ($p_objid > 0 && $l_cattype) {
            $l_num_cats = $l_cats->num_rows();

            $l_list_id = $p_list_id;

            while ($l_row = $l_cats->get_row()) {
                $l_cat_id = $l_row[$l_cattable . "__id"];
                $l_spectable = $l_row[$l_cattable . "__source_table"];

                /**
                 * @author DS
                 * @desc   Retrieve category id if category is single value and $p_list_id was not given
                 */
                if ($l_row[$l_cattable . "__list_multi_value"] == "0" && (($l_num_cats == 1 && empty($l_list_id)) || $l_num_cats > 0)) {
                    if ($p_disttype == C__CMDB__CATEGORY__TYPE_SPECIFIC || $p_disttype == C__CMDB__CATEGORY__TYPE_CUSTOM) {
                        $l_table = $l_spectable;
                    } else {
                        $l_table = $l_spectable . "_list";
                    }

                    if ($p_disttype === C__CMDB__CATEGORY__TYPE_CUSTOM) {
                        $l_cat_entry_field = $l_table . '__data__id';
                        $l_query = "SELECT {$l_cat_entry_field}
                            FROM {$l_table}
                            WHERE {$l_table}__isys_obj__id = '" . $this->m_db->escape_string($p_objid) . "'
                            AND {$l_table}__isysgui_catg_custom__id = '" . $this->m_db->escape_string($l_cat_id) . "'
                            LIMIT 1;";
                    } else {
                        $l_cat_entry_field = $l_table . "__id";
                        $l_query = "SELECT {$l_cat_entry_field}
                            FROM {$l_table}
                            WHERE {$l_table}__isys_obj__id = '" . $this->m_db->escape_string($p_objid) . "'
                            LIMIT 1;";
                    }

                    $l_dao = $this->retrieve($l_query);

                    if ($l_dao->num_rows() === 1) {
                        $l_data = $l_dao->get_row();
                        $l_list_id = $l_data[$l_cat_entry_field];
                    }
                }

                if ($p_objid != null) {
                    // Build the category.
                    $l_catobj = isys_cmdb_dao_category::manufacture(
                        $l_dao_cmdb, // cmdb access object
                        $p_objid,     // object id
                        $p_disttype, // distributor type (C__CMDB__CATEGORY__TYPE_SPECIFIC, ...)
                        $l_cat_id,     // isysgui_cat*
                        $l_row,         // isysgui entry
                        $l_cattype,     // s,g,g_custom
                        $l_list_id     // category id
                    );

                    if ($l_catobj != null) {
                        if ($l_spectable == 'isys_catg_custom_fields_list' && method_exists($l_catobj, 'set_catg_custom_id')) {
                            $l_catobj->set_catg_custom_id($l_row[$l_spectable . '__id']);
                            $l_catobj->set_catgory_const($l_row[$l_spectable . '__const']);
                        }

                        $l_newcatid = $l_catobj->get_category_id();

                        if (isset($l_newcatid)) {
                            if ($l_newcatid != $l_cat_id) {
                                $this->m_categories[$l_cat_id] = $l_catobj;
                                $this->m_guidata[$l_cat_id] = $l_row;
                            } else {
                                $this->m_categories[$l_newcatid] = $l_catobj;
                                $this->m_guidata[$l_newcatid] = $l_row;
                            }
                        }
                    }
                }

                $l_list_id = null;
            }
        }
    }
}

<?php

/**
 * i-doit
 *
 * DAO for table list template
 *
 * @package    i-doit
 * @subpackage Components
 * @author     Niclas Potthast <npotthast@i-doit.de>
 * @version    1.0
 * @copyright  synetics GmbH
 * @license    http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_component_dao_object_table_list extends isys_component_dao
{
    /**
     * @var int
     */
    protected $m_cRecStatus = C__RECORD_STATUS__NORMAL;

    /**
     * @var isys_cmdb_dao_category
     */
    protected $m_cat_dao;

    /**
     * @var
     */
    protected $m_nLimit;

    /**
     * @var
     */
    protected $m_nStart;

    /**
     * @var array
     */
    protected $m_rec_counts = [];

    /**
     * @var
     */
    protected $m_strFilter;

    /**
     * @var
     */
    private $m_nNavPageCount;

    /**
     * @param isys_cmdb_dao_category $p_cmdb_dao_category
     */
    public function set_dao_category(isys_cmdb_dao_category $p_cmdb_dao_category)
    {
        $this->m_cat_dao = $p_cmdb_dao_category;
    }

    /**
     * @return isys_cmdb_dao|isys_cmdb_dao_category
     */
    public function get_dao_category()
    {
        return $this->m_cat_dao;
    }

    /**
     * @return string
     */
    public function get_order()
    {
        return isys_glob_get_order();
    }

    /**
     * General get_result.
     *
     * @param   string  $p_strTableName
     * @param   integer $p_object_id
     * @param   integer $p_cRecStatus
     *
     * @return  isys_component_dao_result
     * @throws  isys_exception_dao_cmdb
     */
    public function get_result($p_strTableName = null, $p_object_id, $p_cRecStatus = null)
    {
        if (is_object($this->m_cat_dao)) {
            if ($p_object_id <= 0) {
                $p_object_id = $_GET[C__CMDB__GET__OBJECT];
            }

            if (method_exists($this->m_cat_dao, "get_data")) {
                $p_cRecStatus = ((!empty($p_cRecStatus))) ? $p_cRecStatus : $this->get_rec_status();

                if (is_string($p_cRecStatus) && !is_numeric($p_cRecStatus)) {
                    $p_cRecStatus = constant($p_cRecStatus);
                }

                return $this->m_cat_dao->get_data(null, $p_object_id, null, null, $p_cRecStatus);
            } else {
                throw new isys_exception_dao_cmdb(get_class($this->m_cat_dao) . "::get_data not implemented, yet.");
            }
        } else {
            throw new isys_exception_dao_cmdb("isys_component_dao_object_table_list::\$m_cat_dao undefined.");
        }
    }

    /**
     * @param string $p_strFilter
     *
     * @return isys_component_dao_result
     */
    public function set_filter($p_strFilter)
    {
        $this->m_strFilter = $p_strFilter;
    }

    /**
     * @return string
     */
    public function get_filter()
    {
        return $this->m_strFilter;
    }

    /**
     *
     * @param string $p_cRecStatus
     *
     * @return $this
     * @version Niclas Potthast <npotthast@i-doit.org> - 2006-03-31
     * @desc    Sets the status for filtering the object lists possible values are:
     *        C__RECORD_STATUS__BIRTH
     *        C__RECORD_STATUS__NORMAL
     *        C__RECORD_STATUS__ARCHIVED
     *        C__RECORD_STATUS__DELETED
     */
    public function set_rec_status($p_cRecStatus)
    {
        if (!empty($p_cRecStatus)) {
            $this->m_cRecStatus = $p_cRecStatus;
        }

        //if someone needs the status, set it for the templates, too
        isys_application::instance()->template->smarty_tom_add_rule("tom.content.navbar.cRecStatus.p_bDisabled=0")
            ->assign('list_display', true)
            ->smarty_tom_add_rule("tom.content.navbar.cRecStatus.p_arData=" . serialize($this->get_rec_array()))
            ->smarty_tom_add_rule("tom.content.navbar.cRecStatus.p_strSelectedID=" . $this->m_cRecStatus);

        return $this;
    }

    /**
     * @param string $p_cRecStatus
     *
     * @return integer
     * @version Niclas Potthast <npotthast@i-doit.org> - 2006-03-31
     * @desc    gets the status for filtering the object lists
     */
    public function get_rec_status()
    {
        return $this->m_cRecStatus;
    }

    /**
     * @return array
     * @desc return array data for the select box
     */
    public function get_rec_array()
    {
        $languageManager = isys_application::instance()->container->get('language');

        $l_cRecCounts = $this->get_rec_counts();

        $l_arData[C__RECORD_STATUS__NORMAL] = $languageManager->get("LC__CMDB__RECORD_STATUS__NORMAL") .
            ($l_cRecCounts[C__RECORD_STATUS__NORMAL] !== null ? " (" . $l_cRecCounts[C__RECORD_STATUS__NORMAL] . ")" : '');

        $l_arData[C__RECORD_STATUS__ARCHIVED] = $languageManager->get("LC__CMDB__RECORD_STATUS__ARCHIVED") .
            ($l_cRecCounts[C__RECORD_STATUS__ARCHIVED] !== null ? " (" . $l_cRecCounts[C__RECORD_STATUS__ARCHIVED] . ")" : '');

        $l_arData[C__RECORD_STATUS__DELETED] = $languageManager->get("LC__CMDB__RECORD_STATUS__DELETED") .
            ($l_cRecCounts[C__RECORD_STATUS__DELETED] !== null ? " (" . $l_cRecCounts[C__RECORD_STATUS__DELETED] . ")" : '');

        if (defined("C__TEMPLATE__STATUS") && C__TEMPLATE__STATUS == 1) {
            $l_arData[C__RECORD_STATUS__TEMPLATE] = 'Template' .
                ($l_cRecCounts[C__RECORD_STATUS__TEMPLATE] !== null ? " (" . $l_cRecCounts[C__RECORD_STATUS__TEMPLATE] . ")" : '');
        }

        return $l_arData;
    }

    /**
     * @desc Overwrite this for special count Handling
     * @return array Counts of several Status
     */
    public function get_rec_counts()
    {
        if ($this->m_rec_counts) {
            return $this->m_rec_counts;
        } else {
            $l_normal = $this->get_result(null, $_GET[C__CMDB__GET__OBJECT], C__RECORD_STATUS__NORMAL);
            $l_archived = $this->get_result(null, $_GET[C__CMDB__GET__OBJECT], C__RECORD_STATUS__ARCHIVED);
            $l_deleted = $this->get_result(null, $_GET[C__CMDB__GET__OBJECT], C__RECORD_STATUS__DELETED);

            $this->m_rec_counts = [
                C__RECORD_STATUS__NORMAL   => ($l_normal) ? $l_normal->num_rows() : 0,
                C__RECORD_STATUS__ARCHIVED => ($l_archived) ? $l_archived->num_rows() : 0,
                C__RECORD_STATUS__DELETED  => ($l_deleted) ? $l_deleted->num_rows() : 0,
            ];

            if (defined("C__TEMPLATE__STATUS") && C__TEMPLATE__STATUS == 1) {
                $l_template = $this->get_result(null, $_GET[C__CMDB__GET__OBJECT], C__RECORD_STATUS__TEMPLATE);
                $this->m_rec_counts[C__RECORD_STATUS__TEMPLATE] = ($l_template) ? $l_template->num_rows() : 0;
            }

            return $this->m_rec_counts;
        }
    }

    /**
     * @global         $g_page_limit
     *
     * @param  integer $p_nStart
     * @param  integer $p_nLimit
     *
     * @return void
     */
    public function set_limit($p_nStart, $p_nLimit = null)
    {
        $this->m_nStart = $p_nStart;
        if ($p_nLimit) {
            $this->m_nLimit = $p_nLimit;
        } else {
            //default value
            $this->m_nLimit = isys_glob_get_pagelimit();
        }
    }

    /**
     * @return integer
     * @desc returns set limit for object lists
     */
    public function get_limit()
    {
        return $this->m_nLimit;
    }

    /**
     * @param  integer $p_nNavPageCount
     *
     * @return void
     * @desc   sets the number of rows in the table
     */
    public function set_page_count($p_nNavPageCount)
    {
        $this->m_nNavPageCount = $p_nNavPageCount;
    }

    /**
     * @return integer
     * @desc   return the number of rows in the table
     */
    public function get_page_count()
    {
        return $this->m_nNavPageCount;
    }

    /**
     * Modify row method will be called for each row to alter its content.
     *
     * @param  array &$p_row
     */
    public function modify_row(&$p_row)
    {
        ;
    }

    /**
     * Format row will be called for each row to format certain fields.
     *
     * @param  array &$p_row
     */
    public function format_row(&$p_row)
    {
        ;
    }

    /**
     * isys_component_dao_object_table_list constructor.
     *
     * @param isys_component_database $p_object
     */
    public function __construct($p_object)
    {
        if ($p_object instanceof isys_cmdb_dao_category) {
            parent::__construct($p_object->get_database_component());
            $this->m_cat_dao = $p_object;
        } else {
            if ($p_object instanceof isys_component_database) {
                parent::__construct($p_object);
                /* If no category DAO has been set, use the standard one */
                $this->m_cat_dao = new isys_cmdb_dao($p_object);
            } else {
                throw new isys_exception_general("\$p_object invalid. (" . get_class($p_object) . ") - Use isys_component_database or isys_cmdb_dao_category.  " . __FILE__ .
                    ":" . __LINE__);
            }
        }

        //set $this->cRecStatus from session
        $this->m_cRecStatus = $_SESSION["cRecStatusListView"];
    }
}

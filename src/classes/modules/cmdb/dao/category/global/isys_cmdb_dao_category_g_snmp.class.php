<?php

use idoit\Component\Property\Type\DialogProperty;

/**
 * i-doit
 *
 * DAO: global category for SNMP
 *
 * @package    i-doit
 * @subpackage CMDB_Categories
 * @author     Dennis Stuecken <dstuecken@synetics.de>
 * @copyright  synetics GmbH
 * @license    http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_category_g_snmp extends isys_cmdb_dao_category_global
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'snmp';

    /**
     * SNMP Community store.
     *
     * @var  string
     */
    private $m_community = null;

    /**
     * Return Category Data.
     *
     * @param   integer $p_catg_list_id
     * @param   mixed   $p_obj_id
     * @param   string  $p_condition
     * @param   mixed   $p_filter
     * @param   integer $p_status
     *
     * @return  isys_component_dao_result
     * @throws  isys_exception_database
     */
    public function get_data($p_catg_list_id = null, $p_obj_id = null, $p_condition = "", $p_filter = null, $p_status = null)
    {
        $l_sql = "SELECT * FROM isys_catg_snmp_list
			INNER JOIN isys_obj ON isys_catg_snmp_list__isys_obj__id = isys_obj__id
			LEFT JOIN isys_snmp_community ON isys_catg_snmp_list__isys_snmp_community__id = isys_snmp_community__id
			WHERE TRUE " . $p_condition . " " . $this->prepare_filter($p_filter);

        if ($p_obj_id !== null) {
            $l_sql .= $this->get_object_condition($p_obj_id);
        }

        if ($p_catg_list_id !== null) {
            $l_sql .= ' AND isys_catg_snmp_list__id = ' . $this->convert_sql_id($p_catg_list_id);
        }

        if ($p_status !== null) {
            $l_sql .= ' AND isys_catg_snmp_list__status = ' . $this->convert_sql_int($p_status);
        }

        return $this->retrieve($l_sql);
    }

    /**
     * Method for returning the properties.
     *
     * @return  array
     */
    protected function properties()
    {
        return [
            'title' => (new DialogProperty(
                'C__CATG__SNMP_COMMUNITY',
                'SNMP Community',
                'isys_catg_snmp_list__isys_snmp_community__id',
                'isys_catg_snmp_list',
                'isys_snmp_community'
            ))->setPropertyUiDefault(defined_or_default('C__SNMP_COMMUNITY__PUBLIC')),
            'description' => array_replace_recursive(isys_cmdb_dao_category_pattern::commentary(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__LOGBOOK__DESCRIPTION',
                    C__PROPERTY__INFO__DESCRIPTION => 'LC__CMDB__LOGBOOK__DESCRIPTION'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD => 'isys_catg_snmp_list__description'
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_GLOBAL . defined_or_default('C__CATG__SNMP', 'C__CATG__SNMP'),
                ]
            ])
        ];
    }

    /**
     * @param array $p_category_data
     * @param int   $p_object_id
     * @param int   $p_status
     *
     * @return bool|mixed
     */
    public function sync($p_category_data, $p_object_id, $p_status = 1 /* isys_import_handler_cmdb::C__CREATE */)
    {
        $l_indicator = false;

        if (is_array($p_category_data) && isset($p_category_data['properties'])) {
            $this->m_sync_catg_data = $p_category_data;

            switch ($p_status) {
                case isys_import_handler_cmdb::C__CREATE:
                    $p_category_data['data_id'] = $this->create(
                        $p_object_id,
                        $this->get_property('title'),
                        null,
                        $this->get_property('description'),
                        C__RECORD_STATUS__NORMAL
                    );

                    if ($p_category_data['data_id']) {
                        $l_indicator = true;
                    }
                    break;
                case isys_import_handler_cmdb::C__UPDATE:
                    $l_indicator = $this->save($p_category_data['data_id'], $this->get_property('title'), null, $this->get_property('description'), C__RECORD_STATUS__NORMAL);
                    break;
            }
        }

        return ($l_indicator === true) ? $p_category_data['data_id'] : false;
    }

    /**
     * Return configured snmp community.
     *
     * @param   integer $p_object_id
     *
     * @return  string
     */
    public function get_community($p_object_id)
    {
        if ($this->m_community == null) {
            if ($p_object_id > 0) {
                $this->m_community = $this->get_data_by_object($p_object_id)
                    ->get_row_value('isys_snmp_community__title');
            }
        }

        if (!$this->m_community) {
            $this->m_community = 'public';
        }

        return $this->m_community;
    }

    /**
     * Trigger save process of global category model
     *
     * @param $p_cat_level        level to save, default 0
     * @param &$p_intOldRecStatus __status of record before update
     *
     * @author Dennis Bluemer <dbluemer@i-doit.org>
     */
    public function save_element($p_cat_level, &$p_intOldRecStatus, $p_create = false)
    {
        if (isset($_GET[C__CMDB__GET__OBJECT])) {
            $l_arr = [];

            $l_catdata = $this->get_data(null, $_GET[C__CMDB__GET__OBJECT])
                ->__to_array();
            if (!$l_catdata) {
                $this->create_connector($this->m_table, $_GET[C__CMDB__GET__OBJECT]);
                $l_catdata = $this->get_data(null, $_GET[C__CMDB__GET__OBJECT])
                    ->__to_array();
            }

            $p_intOldRecStatus = $l_catdata["isys_catg_snmp_list__status"];

            if (is_array($_POST["C__CATG__SNMP_OID_TITLES"])) {
                foreach ($_POST["C__CATG__SNMP_OID_TITLES"] as $l_key => $l_val) {
                    $l_arr[$l_val] = $_POST["C__CATG__SNMP_OIDS"][$l_key];
                }
            }

            if ($l_catdata["isys_catg_snmp_list__id"] != "") {
                $l_bRet = $this->save(
                    $l_catdata["isys_catg_snmp_list__id"],
                    $_POST['C__CATG__SNMP_COMMUNITY'],
                    $l_arr,
                    $_POST["C__CMDB__CAT__COMMENTARY_" . $this->get_category_type() . $this->get_category_id()]
                );

                $this->m_strLogbookSQL = $this->get_last_query();
            }

            return true;
        } else {
            isys_notify::warning('Error saving SNMP category. Object ID was not found.');
        }

        return false;
    }

    /**
     * Executes the query to save the category entry given by its ID $p_cat_level.
     *
     * @param integer $p_id
     * @param integer $p_community
     * @param string  $p_oids
     * @param string  $p_description
     * @param integer $p_status
     *
     * @return  boolean
     * @throws  isys_exception_dao
     */
    public function save($p_id, $p_community, $p_oids = "", $p_description = "", $p_status = C__RECORD_STATUS__NORMAL)
    {
        $l_oids = [];

        if (is_array($p_oids) && count($p_oids)) {
            // @todo  Maybe just use "array_filter()" here?
            foreach ($p_oids as $l_key => $l_oid) {
                if ($l_oid != "") {
                    $l_oids[$l_key] = $l_oid;
                }
            }
        }

        $l_strSql = "UPDATE isys_catg_snmp_list SET
			isys_catg_snmp_list__description = " . $this->convert_sql_text($p_description) . ",
			isys_catg_snmp_list__isys_snmp_community__id  = " . $this->convert_sql_id($p_community) . ",
			isys_catg_snmp_list__oids  = '" . serialize($l_oids) . "',
			isys_catg_snmp_list__status = " . $this->convert_sql_int($p_status) . "
			WHERE isys_catg_snmp_list__id = " . $this->convert_sql_id($p_id);

        return $this->update($l_strSql) && $this->apply_update();
    }

    /**
     * Create global category model element.
     *
     * @param   $p_cat_level
     * @param   &$p_new_id
     *
     * @return  mixed
     */
    public function attachObjects(array $p_post)
    {
        $p_new_id = -1; // no success
        $l_intRetCode = 3;

        $l_id = $this->create($_GET[C__CMDB__GET__OBJECT], null, "");

        if ($l_id != false) {
            $this->m_strLogbookSQL = $this->get_last_query();
            $l_intRetCode = null;
            $p_new_id = $l_id;
        }

        return $l_intRetCode;
    }

    /**
     * Executes the query to create the category entry referenced by isys_catg_model__id $p_fk_id
     *
     * @param   integer $p_objID
     * @param   integer $p_community
     * @param   string  $p_oids
     * @param   string  $p_description
     * @param   integer $p_status
     *
     * @return  mixed
     * @throws  isys_exception_dao
     */
    public function create($p_objID, $p_community, $p_oids = "", $p_description = "", $p_status = C__RECORD_STATUS__NORMAL)
    {
        $l_id = $this->create_connector('isys_catg_snmp_list', $p_objID);
        if ($this->save($l_id, $p_community, $p_oids, $p_description, $p_status)) {
            return $l_id;
        }

        return false;
    }
}

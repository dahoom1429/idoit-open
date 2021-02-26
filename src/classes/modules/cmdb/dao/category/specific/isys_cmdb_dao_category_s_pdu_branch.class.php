<?php

/**
 * i-doit
 *
 * DAO: specific category PDU branches.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @version     Dennis Stuecken <dstuecken@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_category_s_pdu_branch extends isys_cmdb_dao_category_specific
{
    /**
     * @var string
     */
    const C__SNMP_COMMUNITY = 'public';

    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'pdu_branch';

    /**
     * @var string
     */
    protected $m_entry_identifier = 'pdu_id';

    /**
     * @var null
     */
    private $m_snmp_host = null;

    /**
     * Used snmp paths
     *
     * @var  array
     */
    private $m_snmp_paths = [];

    /**
     * Is category multi-valued or single-valued?
     *
     * @var  boolean
     */
    protected $m_multivalued = true;

    /**
     * Returns the SNMP host of the given object.
     *
     * @param   integer $p_object_id
     *
     * @return  string
     */
    public function get_snmp_host($p_object_id = null)
    {
        if ($this->m_snmp_host === null) {
            if ($p_object_id === null) {
                $p_object_id = $_GET[C__CMDB__GET__OBJECT];
            }

            if ($p_object_id > 0) {
                $l_dao_ip = new isys_cmdb_dao_category_g_ip($this->m_db);
                $l_ip = $l_dao_ip->get_primary_ip($p_object_id)
                    ->__to_array();

                $this->m_snmp_host = $l_ip["isys_cats_net_ip_addresses_list__title"];
            }
        }

        return $this->m_snmp_host;
    }

    /**
     * @param $p_identifier
     *
     * @return mixed
     */
    public function get_snmp_path($p_identifier)
    {
        return isset($this->m_snmp_paths[$p_identifier]) ? $this->m_snmp_paths[$p_identifier] : '';
    }

    /**
     * @return array
     */
    public function get_snmp_paths()
    {
        return $this->m_snmp_paths;
    }

    /**
     * Shifting decimal sign, because of unorthodox snmp response
     *
     * @param $p_string
     *
     * @return string
     */
    public function decimal_shift($p_string)
    {
        if ($p_string) {
            return substr($p_string, 0, -1) . "." . substr($p_string, strlen($p_string) - 1, 1);
        } else {
            return "";
        }
    }

    /**
     * @param $p_snmp_object_id
     * @param $p_pdu
     * @param $p_branch
     * @param $p_receptable
     *
     * @return mixed
     */
    public function format($p_snmp_object_id, $p_pdu, $p_branch, $p_receptable)
    {
        $l_from = [
            "PDU",
            "RB",
            "RCP",
            "PS"
        ];

        $l_to = [
            $p_pdu,
            $p_branch,
            $p_receptable,
            $p_branch
        ];

        return str_replace($l_from, $l_to, $p_snmp_object_id);
    }

    /**
     * Return Category Data
     *
     * @param [int $p_id]h
     * @param [int $p_obj_id]
     * @param [string $p_condition]
     *
     * @return isys_component_dao_result
     */
    public function get_data($p_cats_list_id = null, $p_obj_id = null, $p_condition = "", $p_filter = null, $p_status = null)
    {
        $p_condition .= $this->prepare_filter($p_filter);

        $l_sql = "SELECT * FROM isys_cats_pdu_branch_list " . "INNER JOIN isys_obj " . "ON " . "isys_obj__id = " . "isys_cats_pdu_branch_list__isys_obj__id " .
            "LEFT JOIN isys_cats_pdu_list " . "ON " . "isys_cats_pdu_list__isys_obj__id = isys_obj__id " . "WHERE TRUE ";

        $l_sql .= $p_condition;

        if (!empty($p_obj_id)) {
            $l_sql .= $this->get_object_condition($p_obj_id);
        }

        if (!empty($p_cats_list_id)) {
            $l_sql .= " AND (isys_cats_pdu_branch_list__id = '{$p_cats_list_id}')";
        }

        if (!empty($p_status)) {
            $l_sql .= " AND (isys_cats_pdu_branch_list__status = '{$p_status}')";
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
            'pdu_id'      => array_replace_recursive(isys_cmdb_dao_category_pattern::int(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'PDU',
                    C__PROPERTY__INFO__DESCRIPTION => 'ID'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD => 'isys_cats_pdu_branch_list__pdu_id'
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CATS__PDU__PDU_ID'
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH => false
                ]
            ]),
            'branch_id'   => array_replace_recursive(isys_cmdb_dao_category_pattern::int(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'Branch-ID',
                    C__PROPERTY__INFO__DESCRIPTION => 'Branch-ID'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD => 'isys_cats_pdu_branch_list__branch_id'
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CMDB__CATS__PDU__BRANCH_ID',
                    C__PROPERTY__UI__PARAMS => [
                        'p_strClass' => 'input-mini'
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH => false
                ]
            ]),
            'receptables' => array_replace_recursive(isys_cmdb_dao_category_pattern::int(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__PDU__RECEPTIBLES',
                    C__PROPERTY__INFO__DESCRIPTION => 'Receptables'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD => 'isys_cats_pdu_branch_list__receptables'
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CMDB__CATS__PDU__RECEPTABLES',
                    C__PROPERTY__UI__PARAMS => [
                        'p_strClass' => 'input-mini'
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH => false
                ]
            ]),
            'description' => array_replace_recursive(isys_cmdb_dao_category_pattern::commentary(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__LOGBOOK__DESCRIPTION',
                    C__PROPERTY__INFO__DESCRIPTION => 'Description'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD => 'isys_cats_pdu_branch_list__description'
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CAT__COMMENTARY_' . $this->get_category_type() . $this->get_category_id()
                ]
            ])
        ];
    }

    /**
     * Synchronizes properties from an import with the database.
     *
     * @param array $p_category_data Values of category data to be saved.
     * @param int   $p_object_id     Current object identifier (from database)
     * @param int   $p_status        Decision whether category data should be created or
     *                               just updated.
     *
     * @return mixed Returns category data identifier (int) on success, true
     * (bool) if nothing had to be done, otherwise false.
     */
    public function sync($p_category_data, $p_object_id, $p_status = 1 /* isys_import_handler_cmdb::C__CREATE */)
    {
        $l_indicator = false;
        if (is_array($p_category_data) && isset($p_category_data['properties'])) {
            // Create category data identifier if needed:
            if ($p_status === isys_import_handler_cmdb::C__CREATE) {
                $p_category_data['data_id'] = $this->create_connector('isys_cats_pdu_branch_list', $p_object_id);
            }
            if ($p_status === isys_import_handler_cmdb::C__CREATE || $p_status === isys_import_handler_cmdb::C__UPDATE) {
                // Save category data:
                $l_indicator = $this->save(
                    $p_category_data['data_id'],
                    $p_category_data['properties']['branch_id'][C__DATA__VALUE],
                    $p_category_data['properties']['receptables'][C__DATA__VALUE],
                    $p_category_data['properties']['description'][C__DATA__VALUE],
                    C__RECORD_STATUS__NORMAL
                );
            }
        }

        return ($l_indicator === true) ? $p_category_data['data_id'] : false;
    }

    /**
     *
     * @param   integer $p_cat_level
     * @param   integer &$p_status
     * @param   boolean $p_create
     *
     * @return  mixed
     */
    public function save_element($p_cat_level, &$p_status, $p_create = false)
    {
        $l_list_id = null;

        if ($p_create) {
            $l_bRet = $this->create(
                $_GET[C__CMDB__GET__OBJECT],
                $_POST["C__CMDB__CATS__PDU__BRANCH_ID"],
                $_POST["C__CMDB__CATS__PDU__RECEPTABLES"],
                $_POST["C__CMDB__CAT__COMMENTARY_" . $this->get_category_type() . $this->get_category_id()]
            );
        } else {
            $l_catdata = $this->get_general_data();

            $p_status = $l_catdata["isys_cats_pdu_branch_list__status"];
            $l_list_id = $l_catdata["isys_cats_pdu_branch_list__id"];

            $l_bRet = $this->save(
                $l_list_id,
                $_POST["C__CMDB__CATS__PDU__BRANCH_ID"],
                $_POST["C__CMDB__CATS__PDU__RECEPTABLES"],
                $_POST["C__CMDB__CAT__COMMENTARY_" . $this->get_category_type() . $this->get_category_id()],
                $p_status
            );
        }

        $this->m_strLogbookSQL = $this->get_last_query();

        return $l_bRet == true ? $l_list_id : -1;
    }

    /**
     * Executes the query to save the category entry given by its ID $p_cat_level.
     *
     * @param   integer $p_id
     * @param   integer $p_branch_id
     * @param   integer $p_receptables
     * @param   string  $p_description
     * @param   integer $p_status
     *
     * @return  boolean
     * @author  Dennis Stuecken <dstuecken@i-doit.org>
     */
    public function save($p_id, $p_branch_id, $p_receptables, $p_description, $p_status = C__RECORD_STATUS__NORMAL)
    {
        if (!$p_receptables || $p_receptables <= 0) {
            $l_receptables = 6;
        } else {
            $l_receptables = $p_receptables;
        }

        $l_strSql = "UPDATE isys_cats_pdu_branch_list SET
			isys_cats_pdu_branch_list__branch_id = " . $this->convert_sql_id($p_branch_id) . ",
			isys_cats_pdu_branch_list__receptables = " . $this->convert_sql_int($l_receptables) . ",
			isys_cats_pdu_branch_list__description = " . $this->convert_sql_text($p_description) . ",
			isys_cats_pdu_branch_list__status = " . $this->convert_sql_id($p_status) . "
			WHERE isys_cats_pdu_branch_list__id = " . $this->convert_sql_id($p_id);

        if ($this->update($l_strSql)) {
            return $this->apply_update();
        } else {
            return false;
        }
    }

    /**
     * Executes the query to create the category entry.
     *
     * @param   array   $p_object_id
     * @param   integer $p_branch_id
     * @param   integer $p_receptables
     * @param   string  $p_description
     * @param   integer $p_status
     *
     * @return  integer  the newly created ID or false
     * @author  Dennis Stuecken <dstuecken@i-doit.org>
     */
    public function create($p_object_id, $p_branch_id, $p_receptables, $p_description, $p_status = C__RECORD_STATUS__NORMAL)
    {
        if (!$p_receptables || $p_receptables <= 0) {
            $l_receptables = 6;
        } else {
            $l_receptables = $p_receptables;
        }

        $l_strSql = "INSERT INTO isys_cats_pdu_branch_list SET
			isys_cats_pdu_branch_list__isys_obj__id = " . $this->convert_sql_id($p_object_id) . ",
			isys_cats_pdu_branch_list__branch_id = " . $this->convert_sql_id($p_branch_id) . ",
			isys_cats_pdu_branch_list__receptables = " . $this->convert_sql_int($l_receptables) . ",
			isys_cats_pdu_branch_list__description = " . $this->convert_sql_text($p_description) . ",
			isys_cats_pdu_branch_list__status = " . $this->convert_sql_id($p_status) . ";";

        if ($this->update($l_strSql) && $this->apply_update()) {
            return $this->get_last_insert_id();
        } else {
            return false;
        }
    }

    /**
     * @param isys_component_database $p_db
     */
    public function __construct(isys_component_database $p_db)
    {
        parent::__construct($p_db);

        /**
         * Providing default SNMP paths
         *
         * These paths are compatible to Knuerr PDU products: <http://www.knuerr.com/web/zip-pdf/manuals/PDUwithModule-Remote.pdf>
         */
        $this->m_snmp_paths = [
            "lgpPduRcpEntryPwrOut"      => isys_tenantsettings::get('snmp.pdu.path.lgpPduRcpEntryPwrOut', "1.3.6.1.4.1.476.1.42.3.8.50.20.1.65.PDU.RB.RCP"),
            // pwr out per receptable (watt)
            "lgpPduPsEntryPwrTotal"     => isys_tenantsettings::get('snmp.pdu.path.lgpPduPsEntryPwrTotal', "1.3.6.1.4.1.476.1.42.3.8.30.20.1.65.PDU.PS"),
            // pwr out per pdu (total rack power)
            "lgpPduRcpEntryEnergyAccum" => isys_tenantsettings::get('snmp.pdu.path.lgpPduRcpEntryEnergyAccum', "1.3.6.1.4.1.476.1.42.3.8.50.20.1.85.PDU.RB.RCP"),
            // accum energy per receptable
            "lgpPduPsEntryEnergyAccum"  => isys_tenantsettings::get('snmp.pdu.path.lgpPduPsEntryEnergyAccum', "1.3.6.1.4.1.476.1.42.3.8.30.20.1.50.PDU.PS"),
            // accum energy per pdu
            "lgpPduRbEntryEnergyAccum"  => isys_tenantsettings::get('snmp.pdu.path.lgpPduRbEntryEnergyAccum', "1.3.6.1.4.1.476.1.42.3.8.40.20.1.85.PDU.RB"),
            // accum energy per branch
            "lgpPduRbEntryPwrTotal"     => isys_tenantsettings::get('snmp.pdu.path.lgpPduRbEntryPwrTotal', "1.3.6.1.4.1.476.1.42.3.8.40.20.1.115.PDU.RB"),
            // pwr out per branch
            "branchTag"                 => isys_tenantsettings::get('snmp.pdu.path.branchTag', "1.3.6.1.4.1.476.1.42.3.8.40.20.1.8.PDU.RB"),
            // branch tag
            "receptableName"            => isys_tenantsettings::get('snmp.pdu.path.receptableName', "1.3.6.1.4.1.476.1.42.3.8.50.20.1.10.PDU.RB.RCP"),
            // name of receptable (unit)
        ];
    }
}

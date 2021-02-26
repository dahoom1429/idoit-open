<?php

/**
 * i-doit
 *
 * DAO: specific category for crypto cards.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_category_s_krypto_card extends isys_cmdb_dao_category_specific
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'krypto_card';

    /**
     * Category entry is purgable
     *
     * @var bool
     */
    protected $m_is_purgable = true;

    /**
     * Return Category Data.
     *
     * @param   integer $p_cats_list_id
     * @param   mixed   $p_obj_id
     * @param   string  $p_condition
     * @param   mixed   $p_filter
     * @param   integer $p_status
     *
     * @return  isys_component_dao_result
     */
    public function get_data($p_cats_list_id = null, $p_obj_id = null, $p_condition = "", $p_filter = null, $p_status = null)
    {
        $l_sql = "SELECT *
			FROM isys_cats_krypto_card_list
			INNER JOIN isys_obj ON isys_obj__id = isys_cats_krypto_card_list__isys_obj__id
			LEFT JOIN isys_catg_assigned_cards_list ON isys_catg_assigned_cards_list__isys_obj__id__card = isys_cats_krypto_card_list__isys_obj__id
			WHERE TRUE " . $p_condition . $this->prepare_filter($p_filter);

        if ($p_cats_list_id !== null) {
            $l_sql .= " AND isys_cats_krypto_card_list__id = " . $this->convert_sql_id($p_cats_list_id);
        }

        if ($p_obj_id !== null) {
            $l_sql .= $this->get_object_condition($p_obj_id);
        }

        if ($p_status !== null) {
            $l_sql .= " AND isys_cats_krypto_card_list__status = " . $this->convert_sql_int($p_status);
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
            'certificate_number'    => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__KRYPTO_CARD__CERTIFICATE_NUMBER',
                    C__PROPERTY__INFO__DESCRIPTION => 'Certificate number'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD => 'isys_cats_krypto_card_list__certificate_number'
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CATS__KRYPTO_CARD__CERTIFICATE_NUMBER'
                ]
            ]),
            'certgate_card_number'  => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__KRYPTO_CARD__CERTGATE_CARD_NUMBER',
                    C__PROPERTY__INFO__DESCRIPTION => 'Certgate card number'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD => 'isys_cats_krypto_card_list__certgate_card_number'
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CATS__KRYPTO_CARD__CERTGATE_CARD_NUMBER'
                ]
            ]),
            'certificate_title'     => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__KRYPTO_CARD__CERTIFICATE_TITLE',
                    C__PROPERTY__INFO__DESCRIPTION => 'Certificate title'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD => 'isys_cats_krypto_card_list__certificate_title'
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CATS__KRYPTO_CARD__CERTIFICATE_TITLE'
                ]
            ]),
            'certificate_password'  => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__KRYPTO_CARD__CERTIFICATE_PASSWORD',
                    C__PROPERTY__INFO__DESCRIPTION => 'Certificate revocation password'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD => 'isys_cats_krypto_card_list__certificate_password'
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CATS__KRYPTO_CARD__CERTIFICATE_PASSWORD'
                ]
            ]),
            'certificate_procedure' => array_replace_recursive(isys_cmdb_dao_category_pattern::date(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__KRYPTO_CARD__CERTIFICATE_PROCEDURE',
                    C__PROPERTY__INFO__DESCRIPTION => 'Certificate procedure'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD => 'isys_cats_krypto_card_list__certificate_procedure'
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CATS__KRYPTO_CARD__CERTIFICATE_PROCEDURE'
                ]
            ]),
            'date_of_issue'         => array_replace_recursive(isys_cmdb_dao_category_pattern::date(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__KRYPTO_CARD__DATE_OF_ISSUE',
                    C__PROPERTY__INFO__DESCRIPTION => 'Date of issue'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD => 'isys_cats_krypto_card_list__date_of_issue'
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CATS__KRYPTO_CARD__DATE_OF_ISSUE'
                ]
            ]),
            'imei_number'           => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__KRYPTO_CARD__IMEI_NUMBER',
                    C__PROPERTY__INFO__DESCRIPTION => 'IMEI number'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD => 'isys_cats_krypto_card_list__imei_number'
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CATS__KRYPTO_CARD__IMEI_NUMBER'
                ]
            ]),
            'assigned_mobile'       => array_replace_recursive(isys_cmdb_dao_category_pattern::object_browser(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__KRYPTO_CARD__ASSIGNED_MOBILE_PHONE',
                    C__PROPERTY__INFO__DESCRIPTION => 'assigned mobile phone'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_assigned_cards_list__isys_obj__id',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory('SELECT  CONCAT(isys_obj__title, \' {\', isys_obj__id, \'}\')
                            FROM isys_cats_krypto_card_list
                            INNER JOIN isys_catg_assigned_cards_list ON isys_catg_assigned_cards_list__isys_obj__id__card = isys_cats_krypto_card_list__isys_obj__id
                            INNER JOIN isys_obj ON isys_obj__id = isys_catg_assigned_cards_list__isys_obj__id', 'isys_cats_krypto_card_list', 'isys_cats_krypto_card_list__id',
                        'isys_cats_krypto_card_list__isys_obj__id'),
                    C__PROPERTY__DATA__JOIN   => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_cats_krypto_card_list', 'LEFT', 'isys_cats_krypto_card_list__isys_obj__id',
                            'isys_obj__id'),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_catg_assigned_cards_list', 'LEFT', 'isys_cats_krypto_card_list__isys_obj__id',
                            'isys_catg_assigned_cards_list__isys_obj__id__card'),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_obj', 'LEFT', 'isys_catg_assigned_cards_list__isys_obj__id', 'isys_obj__id')
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CATS__KRYPTO_CARD__ASSIGNED_MOBILE_PHONE',
                    C__PROPERTY__UI__PARAMS => [
                        'catFilter'             => "C__CATS__CELL_PHONE_CONTRACT",
                        'p_bReadonly'            => "1",
                    ],
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'object'
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH => false
                ]
            ]),
            'description'           => array_replace_recursive(isys_cmdb_dao_category_pattern::commentary(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__LOGBOOK__DESCRIPTION',
                    C__PROPERTY__INFO__DESCRIPTION => 'Description'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD => 'isys_cats_krypto_card_list__description'
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_SPECIFIC . defined_or_default('C__CATS__KRYPTO_CARD', 'C__CATS__KRYPTO_CARD')
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
                $p_category_data['data_id'] = $this->create_connector('isys_cats_krypto_card_list', $p_object_id);
            }
            if ($p_status === isys_import_handler_cmdb::C__CREATE || $p_status === isys_import_handler_cmdb::C__UPDATE) {
                $l_indicator = $this->save($p_category_data['data_id'], C__RECORD_STATUS__NORMAL, $p_category_data['properties']['certificate_number'][C__DATA__VALUE],
                    $p_category_data['properties']['certgate_card_number'][C__DATA__VALUE], $p_category_data['properties']['certificate_title'][C__DATA__VALUE],
                    $p_category_data['properties']['certificate_password'][C__DATA__VALUE], $p_category_data['properties']['certificate_procedure'][C__DATA__VALUE],
                    $p_category_data['properties']['date_of_issue'][C__DATA__VALUE], $p_category_data['properties']['imei_number'][C__DATA__VALUE],
                    $p_category_data['properties']['description'][C__DATA__VALUE], $p_category_data['properties']['assigned_mobile'][C__DATA__VALUE]);
            }
        }

        return ($l_indicator === true) ? $p_category_data['data_id'] : false;
    }

    /**
     * Save specific category krypto card
     *
     * @param $p_cat_level        level to save, default 0
     * @param &$p_intOldRecStatus __status of record before update
     */
    public function save_element($p_cat_level, &$p_intOldRecStatus)
    {
        $l_catdata = $this->get_general_data();
        $l_list_id = null;
        if ($l_catdata) {
            $p_intOldRecStatus = $l_catdata["isys_cats_krypto_card_list__status"];
            $l_list_id = $l_catdata["isys_cats_krypto_card_list__id"];
        } else {
            $p_intOldRecStatus = C__RECORD_STATUS__NORMAL;
        }

        if (empty($l_list_id)) {
            $l_list_id = $this->create_connector("isys_cats_krypto_card_list", $_GET[C__CMDB__GET__OBJECT]);
        }

        $l_bRet = $this->save($l_list_id, C__RECORD_STATUS__NORMAL, $_POST['C__CATS__KRYPTO_CARD__CERTIFICATE_NUMBER'], $_POST['C__CATS__KRYPTO_CARD__CERTGATE_CARD_NUMBER'],
            $_POST['C__CATS__KRYPTO_CARD__CERTIFICATE_TITLE'], $_POST['C__CATS__KRYPTO_CARD__CERTIFICATE_PASSWORD'],
            $_POST['C__CATS__KRYPTO_CARD__CERTIFICATE_PROCEDURE__HIDDEN'], $_POST['C__CATS__KRYPTO_CARD__DATE_OF_ISSUE__HIDDEN'], $_POST['C__CATS__KRYPTO_CARD__IMEI_NUMBER'],
            $_POST["C__CMDB__CAT__COMMENTARY_" . $this->get_category_type() . $this->get_category_id()], $_POST['C__CATS__KRYPTO_CARD__ASSIGNED_MOBILE_PHONE__HIDDEN']);

        $this->m_strLogbookSQL = $this->get_last_query();

        return $l_bRet == true ? $l_list_id : -1;
    }

    /**
     * Updates category entry
     *
     * @param   integer $p_cat_level
     * @param array|int $p_newRecStatus
     * @param   string  $p_certicate_number
     * @param   string  $p_certgate_card_number
     * @param   string  $p_certificate_title
     * @param   string  $p_certificate_password
     * @param   string  $p_certificate_procedure
     * @param   string  $p_date_of_issue
     * @param   string  $p_imei_number
     * @param   string  $p_description
     * @param   null    $p_mobile_phone
     *
     * @return  boolean
     */
    public function save(
        $p_cat_level,
        $p_newRecStatus = C__RECORD_STATUS__NORMAL,
        $p_certicate_number = null,
        $p_certgate_card_number = null,
        $p_certificate_title = null,
        $p_certificate_password = null,
        $p_certificate_procedure = null,
        $p_date_of_issue = null,
        $p_imei_number = null,
        $p_description = null,
        $p_mobile_phone = null
    ) {
        $l_strSql = "UPDATE isys_cats_krypto_card_list SET
			isys_cats_krypto_card_list__certificate_number = " . $this->convert_sql_text($p_certicate_number) . ",
			isys_cats_krypto_card_list__certgate_card_number  = " . $this->convert_sql_text($p_certgate_card_number) . ",
			isys_cats_krypto_card_list__certificate_title  = " . $this->convert_sql_text($p_certificate_title) . ",
			isys_cats_krypto_card_list__certificate_password  = " . $this->convert_sql_text($p_certificate_password) . ",
			isys_cats_krypto_card_list__certificate_procedure  = " . $this->convert_sql_datetime($p_certificate_procedure) . ",
			isys_cats_krypto_card_list__date_of_issue  = " . $this->convert_sql_datetime($p_date_of_issue) . ",
			isys_cats_krypto_card_list__imei_number = " . $this->convert_sql_text($p_imei_number) . ",
			isys_cats_krypto_card_list__description = " . $this->convert_sql_text($p_description) . ",
			isys_cats_krypto_card_list__status = " . C__RECORD_STATUS__NORMAL . "
			WHERE isys_cats_krypto_card_list__id = " . $this->convert_sql_id($p_cat_level);

        if ($this->update($l_strSql) && $this->apply_update()) {
            $l_dao = new isys_cmdb_dao_category_g_assigned_cards($this->get_database_component());
            $l_card = $this->get_data($p_cat_level)
                ->get_row();

            if ($l_card['isys_cats_krypto_card_list__isys_obj__id'] > 0) {
                if ($p_mobile_phone != '' && $p_mobile_phone > 0) {
                    $l_dao->remove_component(null, $l_card['isys_cats_krypto_card_list__isys_obj__id']);
                    $l_dao->add_component($p_mobile_phone, $l_card['isys_cats_krypto_card_list__isys_obj__id']);
                } else {
                    $l_dao->remove_component(null, $l_card["isys_cats_krypto_card_list__isys_obj__id"]);
                }
            }

            return true;
        } else {
            return false;
        }
    }

    /**
     * Creates new category entry.
     *
     * @param   integer $p_objID
     * @param   integer $p_newRecStatus
     * @param   string  $p_certicate_number
     * @param   string  $p_certgate_card_number
     * @param   string  $p_certificate_title
     * @param   string  $p_certificate_password
     * @param   string  $p_certificate_procedure
     * @param   string  $p_date_of_issue
     * @param   string  $p_imei_number
     * @param   string  $p_description
     *
     * @return  boolean
     */
    public function create(
        $p_objID,
        $p_newRecStatus = C__RECORD_STATUS__NORMAL,
        $p_certicate_number = null,
        $p_certgate_card_number = null,
        $p_certificate_title = null,
        $p_certificate_password = null,
        $p_certificate_procedure = null,
        $p_date_of_issue = null,
        $p_imei_number = null,
        $p_description = null
    ) {
        $l_strSql = "INSERT IGNORE INTO isys_cats_krypto_card_list SET
			isys_cats_krypto_card_list__certificate_number = " . $this->convert_sql_text($p_certicate_number) . ",
			isys_cats_krypto_card_list__certgate_card_number  = " . $this->convert_sql_text($p_certgate_card_number) . ",
			isys_cats_krypto_card_list__certificate_title  = " . $this->convert_sql_text($p_certificate_title) . ",
			isys_cats_krypto_card_list__certificate_password  = " . $this->convert_sql_text($p_certificate_password) . ",
			isys_cats_krypto_card_list__certificate_procedure  = " . $this->convert_sql_datetime($p_certificate_procedure) . ",
			isys_cats_krypto_card_list__date_of_issue  = " . $this->convert_sql_datetime($p_date_of_issue) . ",
			isys_cats_krypto_card_list__imei_number = " . $this->convert_sql_text($p_imei_number) . ",
			isys_cats_krypto_card_list__description = " . $this->convert_sql_text($p_description) . ",
			isys_cats_krypto_card_list__status = " . C__RECORD_STATUS__NORMAL . ",
			isys_cats_krypto_card_list__isys_obj__id = " . $this->convert_sql_id($p_objID) . ";";

        if ($this->update($l_strSql) && $this->apply_update()) {
            return $this->get_last_insert_id();
        } else {
            return false;
        }
    }
}

<?php

use idoit\Component\Property\Type\CommentaryProperty;
use idoit\Component\Property\Type\ObjectBrowserConnectionProperty;
use idoit\Component\Property\Type\TextProperty;

/**
 * i-doit
 *
 * DAO: global category for SIM cards
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_category_g_cards extends isys_cmdb_dao_category_global
{
    /**
     * Category's name. Will be used for the identifier, constant, main table,
     * and many more.
     *
     * @var string
     */
    protected $m_category = 'cards';

    /**
     * Category entry is purgable
     *
     * @var bool
     */
    protected $m_is_purgable = true;

    /**
     * @var bool
     */
    protected $m_multivalued = true;

    /**
     * This variable holds the language constant of the current category.
     *
     * @var string
     */
    protected $categoryTitle = 'LC__CMDB__CATG__CARDS';

    /**
     * @var string
     */
    protected $m_connected_object_id_field = 'isys_connection__isys_obj__id';

    /**
     * @var string
     */
    protected $m_entry_identifier = 'assigned_mobile';

    /**
     * @var string
     */
    protected $m_object_id_field = 'isys_catg_cards_list__isys_obj__id';

    /**
     * Save global category backup element.
     *
     * @param integer  $entryId
     * @param integer &$oldRecStatus
     * @param boolean  $createEntry
     *
     * @return  mixed
     */
    public function save_element(&$entryId, &$oldRecStatus, $createEntry = false)
    {
        $errorCode = -1;
        $return = null;

        $categoryData = $this->get_general_data();

        $oldRecStatus = $categoryData["isys_catg_cards_list__status"];

        if ($createEntry) {
            $l_id = $this->create(
                $_GET[C__CMDB__GET__OBJECT],
                C__RECORD_STATUS__NORMAL,
                $_POST['C__CATG__CARDS__TITLE'],
                $_POST['C__CATG__CARDS__ASSIGNED_MOBILE_PHONE__HIDDEN'],
                $_POST['C__CATS__CP_CONTRACT__CARD_NUMBER'],
                $_POST['C__CATS__CP_CONTRACT__PIN'],
                $_POST['C__CATS__CP_CONTRACT__PIN2'],
                $_POST['C__CATS__CP_CONTRACT__PUK'],
                $_POST['C__CATS__CP_CONTRACT__PUK2'],
                $_POST['C__CATS__CP_CONTRACT__SERIAL_NUMBER'],
                $_POST['C__CMDB__CAT__COMMENTARY_' . $this->get_category_type() . $this->get_category_id()]
            );

            if ($l_id != false) {
                $this->m_strLogbookSQL = $this->get_last_query();
            }

            $entryId = null;

            return $l_id;
        } else {
            // This case can only happen if category is saved via overview category and on new objects
            if ($categoryData === null) {
                $l_query = 'SELECT isys_catg_cards_list__id, isys_catg_cards_list__status FROM isys_catg_cards_list';
                $l_query .= ' WHERE isys_catg_cards_list__isys_obj__id = ' . $this->convert_sql_id($_GET[C__CMDB__GET__OBJECT]) . ' LIMIT 1;';

                $categoryData = $this->retrieve($l_query)
                    ->get_row();
            }

            if ($categoryData['isys_catg_cards_list__id'] != "") {
                $return = $this->save(
                    $categoryData['isys_catg_cards_list__id'],
                    C__RECORD_STATUS__NORMAL,
                    $_POST['C__CATG__CARDS__TITLE'],
                    $_POST['C__CATG__CARDS__ASSIGNED_MOBILE_PHONE__HIDDEN'],
                    $_POST['C__CATS__CP_CONTRACT__CARD_NUMBER'],
                    $_POST['C__CATS__CP_CONTRACT__PIN'],
                    $_POST['C__CATS__CP_CONTRACT__PIN2'],
                    $_POST['C__CATS__CP_CONTRACT__PUK'],
                    $_POST['C__CATS__CP_CONTRACT__PUK2'],
                    $_POST['C__CATS__CP_CONTRACT__SERIAL_NUMBER'],
                    $_POST['C__CMDB__CAT__COMMENTARY_' . $this->get_category_type() . $this->get_category_id()]
                );

                $this->m_strLogbookSQL = $this->get_last_query();
            }

            return $return == true ? null : $errorCode;
        }
    }

    /**
     * @param      $objectId
     * @param int  $status
     * @param null $cardTitle
     * @param null $mobilePhone
     * @param null $cardNumber
     * @param null $pinOne
     * @param null $pinTwo
     * @param null $pukOne
     * @param null $pukTwo
     * @param null $serial
     * @param null $description
     *
     * @return int
     * @throws isys_exception_dao
     */
    public function create(
        $objectId,
        $status = C__RECORD_STATUS__NORMAL,
        $cardTitle = null,
        $mobilePhone = null,
        $cardNumber = null,
        $pinOne = null,
        $pinTwo = null,
        $pukOne = null,
        $pukTwo = null,
        $serial = null,
        $description = null
    ) {
        $connectionId = isys_factory::get_instance('isys_cmdb_dao_connection', $this->get_database_component())
            ->add_connection($mobilePhone);

        $insert = 'INSERT INTO isys_catg_cards_list SET
            isys_catg_cards_list__isys_obj__id = ' . $this->convert_sql_id($objectId) . ',
            isys_catg_cards_list__isys_connection__id = ' . $this->convert_sql_id($connectionId) . ',
            isys_catg_cards_list__title = ' . $this->convert_sql_text($cardTitle) . ',  
            isys_catg_cards_list__description = ' . $this->convert_sql_text($description) . ',  
            isys_catg_cards_list__serial_number = ' . $this->convert_sql_text($serial) . ',  
            isys_catg_cards_list__pin = ' . $this->convert_sql_text($pinOne) . ',  
            isys_catg_cards_list__pin2 = ' . $this->convert_sql_text($pinTwo) . ',  
            isys_catg_cards_list__puk = ' . $this->convert_sql_text($pukOne) . ',  
            isys_catg_cards_list__puk2 = ' . $this->convert_sql_text($pukTwo) . ',  
            isys_catg_cards_list__card_number = ' . $this->convert_sql_text($cardNumber) . ',
            isys_catg_cards_list__status = ' . $this->convert_sql_int($status);

        if ($this->update($insert) && $this->apply_update()) {
            $entryId = $this->get_last_insert_id();

            if ($mobilePhone !== null) {
                $relationDao = isys_cmdb_dao_category_g_relation::instance($this->get_database_component());
                $relationDao->handle_relation(
                    $entryId,
                    'isys_catg_cards_list',
                    'C__RELATION_TYPE__ASSIGNED_SIM_CARDS',
                    null,
                    $mobilePhone,
                    $objectId
                );
            }

            return $entryId;
        }
    }

    /**
     * @param      $entryId
     * @param int  $status
     * @param null $cardTitle
     * @param null $mobilePhone
     * @param null $cardNumber
     * @param null $pinOne
     * @param null $pinTwo
     * @param null $pukOne
     * @param null $pukTwo
     * @param null $serial
     * @param null $description
     *
     * @return bool
     * @throws isys_exception_dao
     * @throws isys_exception_database
     */
    public function save(
        $entryId,
        $status = C__RECORD_STATUS__NORMAL,
        $cardTitle = null,
        $mobilePhone = null,
        $cardNumber = null,
        $pinOne = null,
        $pinTwo = null,
        $pukOne = null,
        $pukTwo = null,
        $serial = null,
        $description = null
    ) {
        $oldData = $this->get_data($entryId)->get_row();

        if (empty($oldData['isys_catg_cards_list__isys_connection__id'])) {
            $connectionId = isys_factory::get_instance('isys_cmdb_dao_connection', $this->get_database_component())
                ->add_connection($mobilePhone);

            $update = 'UPDATE isys_catg_cards_list SET isys_catg_cards_list__isys_connection__id = ' . $this->convert_sql_id($connectionId) . ', ';
        } else {
            $update = 'UPDATE isys_catg_cards_list
				INNER JOIN isys_connection ON isys_connection__id = isys_catg_cards_list__isys_connection__id
				SET isys_connection__isys_obj__id  = ' . $this->convert_sql_id($mobilePhone) . ', ';
        }

        $update .= 'isys_catg_cards_list__title = ' . $this->convert_sql_text($cardTitle) . ',  
            isys_catg_cards_list__description = ' . $this->convert_sql_text($description) . ',  
            isys_catg_cards_list__serial_number = ' . $this->convert_sql_text($serial) . ',  
            isys_catg_cards_list__pin = ' . $this->convert_sql_text($pinOne) . ',  
            isys_catg_cards_list__pin2 = ' . $this->convert_sql_text($pinTwo) . ',  
            isys_catg_cards_list__puk = ' . $this->convert_sql_text($pukOne) . ',  
            isys_catg_cards_list__puk2 = ' . $this->convert_sql_text($pukTwo) . ',  
            isys_catg_cards_list__card_number = ' . $this->convert_sql_text($cardNumber) . ',
            isys_catg_cards_list__status = ' . $this->convert_sql_int($status);

        $condition = ' WHERE isys_catg_cards_list__id = ' . $this->convert_sql_id($entryId);

        $relationDao = isys_cmdb_dao_category_g_relation::instance($this->get_database_component());
        $relationDao->handle_relation(
            $entryId,
            "isys_catg_cards_list",
            'C__RELATION_TYPE__ASSIGNED_SIM_CARDS',
            $oldData['isys_catg_cards_list__isys_catg_relation_list__id'],
            $mobilePhone,
            $oldData['isys_catg_cards_list__isys_obj__id']
        );

        return $this->update($update . $condition) && $this->apply_update();
    }

    /**
     * Return Category Data.
     *
     * @param null   $p_catg_list_id
     * @param mixed  $p_obj_id
     * @param string $p_condition
     * @param mixed  $p_filter
     * @param null   $p_status
     *
     * @return isys_component_dao_result
     * @throws isys_exception_database
     */
    public function get_data($p_catg_list_id = null, $p_obj_id = null, $p_condition = '', $p_filter = null, $p_status = null)
    {
        $sql = 'SELECT * FROM isys_catg_cards_list 
            LEFT JOIN isys_connection ON isys_catg_cards_list__isys_connection__id = isys_connection__id 
            LEFT JOIN isys_obj ON isys_obj__id = isys_connection__isys_obj__id 
            WHERE TRUE ' . $p_condition . ' ' . $this->prepare_filter($p_filter);

        if ($p_obj_id !== null) {
            $sql .= ' AND isys_catg_cards_list__isys_obj__id = ' . $this->convert_sql_id($p_obj_id);
        }

        if ($p_catg_list_id !== null) {
            $sql .= ' AND isys_catg_cards_list__id = ' . $this->convert_sql_id($p_catg_list_id);
        }

        if ($p_status !== null) {
            $sql .= ' AND isys_catg_cards_list__status = ' . $this->convert_sql_int($p_status);
        }

        return $this->retrieve($sql . ';');
    }

    /**
     * Method for returning the properties.
     *
     * @return  array
     */
    protected function properties()
    {
        return [
            'assigned_mobile' => (new ObjectBrowserConnectionProperty(
                'C__CATG__CARDS__ASSIGNED_MOBILE_PHONE',
                'LC__CMDB__CATS__SIM_CARD__ASSIGNED_MOBILE_PHONE',
                'isys_catg_cards_list__isys_connection__id',
                'isys_catg_cards_list',
                [],
                'C__CATG__ASSIGNED_SIM_CARDS'
            ))->mergePropertyUi(
                [
                    C__PROPERTY__UI__DEFAULT => 0
                ]
            )->setPropertyDataRelationType(
                defined_or_default('C__RELATION_TYPE__ASSIGNED_SIM_CARDS')
            )->setPropertyDataRelationHandler(
                new isys_callback([
                    'isys_cmdb_dao_category_g_cards',
                    'callback_property_relation_handler'
                ], [
                    'isys_cmdb_dao_category_g_cards',
                    true
                ])
            ),
            'card_no' => new TextProperty(
                'C__CATS__CP_CONTRACT__CARD_NUMBER',
                'LC__CMDB__CATS_CP_CONTRACT__CARD_NUMBER',
                'isys_catg_cards_list__card_number',
                'isys_catg_cards_list'
            ),
            'title' => new TextProperty(
                'C__CATG__CARDS__TITLE',
                'LC__CMDB__CATG__CARDS__TITLE',
                'isys_catg_cards_list__title',
                'isys_catg_cards_list'
            ),
            'pin' => new TextProperty(
                'C__CATS__CP_CONTRACT__PIN',
                'LC__CMDB__CATS_CP_CONTRACT__PIN',
                'isys_catg_cards_list__pin',
                'isys_catg_cards_list'
            ),
            'pin2' => new TextProperty(
                'C__CATS__CP_CONTRACT__PIN2',
                'LC__CMDB__CATS_CP_CONTRACT__PIN2',
                'isys_catg_cards_list__pin2',
                'isys_catg_cards_list'
            ),
            'puk' => new TextProperty(
                'C__CATS__CP_CONTRACT__PUK',
                'LC__CMDB__CATS_CP_CONTRACT__PUK',
                'isys_catg_cards_list__puk',
                'isys_catg_cards_list'
            ),
            'puk2' => new TextProperty(
                'C__CATS__CP_CONTRACT__PUK2',
                'LC__CMDB__CATS_CP_CONTRACT__PUK2',
                'isys_catg_cards_list__puk2',
                'isys_catg_cards_list'
            ),
            'serial' => new TextProperty(
                'C__CATS__CP_CONTRACT__SERIAL_NUMBER',
                'LC__CMDB__CATG__SERIAL',
                'isys_catg_cards_list__serial_number',
                'isys_catg_cards_list'
            ),
            'description' => new CommentaryProperty(
                'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_GLOBAL . defined_or_default('C__CATG__CARDS', 'C__CATG__CARDS'),
                'isys_catg_cards_list__description',
                'isys_catg_cards_list'
            )
        ];
    }

    /**
     * @param array $categoryData
     * @param int   $objId
     * @param int   $status
     *
     * @return bool|mixed
     * @throws isys_exception_database
     * @throws isys_exception_validation
     */
    public function sync($categoryData, $objId, $status)
    {
        $dao = isys_cmdb_dao_connection::instance(isys_application::instance()->container->get('database'));
        if ($status === isys_import_handler_cmdb::C__CREATE) {
            $categoryData[isys_import_handler_cmdb::C__PROPERTIES]['assigned_mobile'][C__DATA__VALUE] =
                $dao->add_connection($categoryData[isys_import_handler_cmdb::C__PROPERTIES]['assigned_mobile'][C__DATA__VALUE]);
        } else {
            $connectionId = $this->get_data($categoryData['data_id'])->get_row_value('isys_connection__id');
            $dao->update_connection($connectionId, $categoryData[isys_import_handler_cmdb::C__PROPERTIES]['assigned_mobile'][C__DATA__VALUE]);
            unset($categoryData[isys_import_handler_cmdb::C__PROPERTIES]['assigned_mobile'][C__DATA__VALUE]);
        }

        return parent::sync($categoryData, $objId, $status); // TODO: Change the autogenerated stub
    }

    /**
     * Get Simcard by its title
     *
     * @param int    $objectId
     * @param string $cardTitle
     *
     * @return int|null;
     */
    public function getSimcardByTitle($objectId, $cardTitle)
    {
        // Check for correct parameters
        if (empty($objectId) || empty($cardTitle)) {
            throw new \idoit\Exception\Exception('Please provide parameters');
        }

        return $this->get_data(null, $objectId, ' AND (isys_catg_cards_list__title = ' . $this->convert_sql_text($cardTitle) . ')')
            ->get_row_value('isys_catg_cards_list__id');
    }
}

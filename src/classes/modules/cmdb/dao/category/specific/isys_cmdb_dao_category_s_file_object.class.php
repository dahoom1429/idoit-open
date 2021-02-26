<?php

use idoit\Component\Property\Property;
use idoit\Component\Property\Type\ObjectBrowserConnectionBackwardProperty;

/**
 * i-doit
 *
 * DAO: specific category for file objects.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Dennis Stücken <dstuecken@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_category_s_file_object extends isys_cmdb_dao_category_s_file
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'file_object';

    /**
     * Category's constant.
     *
     * @var  string
     */
    protected $m_category_const = 'C__CATS__FILE_OBJECTS';

    /**
     * Category's table
     *
     * @var string
     */
    protected $m_table = 'isys_cats_file_list';

    /**
     * Is category multi-valued or single-valued?
     *
     * @var  boolean
     */
    protected $m_multivalued = true;

    /**
     * Method for returning the properties.
     *
     * @return  array
     */
    protected function properties()
    {
        return [
            'assigned_objects' => (new ObjectBrowserConnectionBackwardProperty(
                'C__CATS__CMDB__FILE__OBJECTS',
                'LC__CATS__CMDB__FILE__OBJECTS',
                'isys_catg_file_list__isys_obj__id',
                'isys_catg_file_list',
                '',
                []
            ))->mergePropertyProvides(
                [
                    Property::C__PROPERTY__PROVIDES__IMPORT       => false,
                    Property::C__PROPERTY__PROVIDES__EXPORT       => false,
                    Property::C__PROPERTY__PROVIDES__REPORT       => true,
                    Property::C__PROPERTY__PROVIDES__LIST         => false,
                    Property::C__PROPERTY__PROVIDES__MULTIEDIT    => false,
                    Property::C__PROPERTY__PROVIDES__FILTERABLE   => false
                ]
            ),
        ];
    }

    /**
     * Method for retrieving the number of objects, assigned to an object.
     *
     * @param   integer $p_obj_id
     *
     * @return  integer
     */
    public function get_count($p_obj_id = null)
    {
        if ($p_obj_id !== null) {
            $l_obj_id = $p_obj_id;
        } else {
            $l_obj_id = $this->m_object_id;
        }

        $l_sql = "SELECT count(isys_obj__id) AS count FROM isys_obj " . "LEFT JOIN isys_connection ON isys_connection__isys_obj__id = isys_obj__id " .
            "LEFT JOIN isys_catg_manual_list ON isys_catg_manual_list__isys_connection__id = isys_connection__id " .
            "LEFT JOIN isys_catg_file_list ON isys_catg_file_list__isys_connection__id = isys_connection__id " .
            "LEFT JOIN isys_catg_emergency_plan_list ON isys_catg_emergency_plan_list__isys_connection__id = isys_connection__id " .
            "WHERE (isys_catg_manual_list__id IS NOT NULL OR isys_catg_file_list__id IS NOT NULL OR isys_catg_emergency_plan_list__id IS NOT NULL) ";

        if ($l_obj_id !== null) {
            $l_sql .= "AND (isys_obj__id = " . $this->convert_sql_id($l_obj_id) . ") ";
        }

        return (int)$this->retrieve($l_sql)
            ->get_row_value('count');
    }

    /**
     * Get data method.
     *
     * @param   integer $p_catg_list_id
     * @param   integer $p_obj_id
     * @param   string  $p_condition
     * @param   array   $p_filter
     * @param   integer $p_status
     *
     * @return  isys_component_dao_result
     */
    public function get_data($p_catg_list_id = null, $p_obj_id = null, $p_condition = '', $p_filter = null, $p_status = null)
    {
        $p_condition .= $this->prepare_filter($p_filter);

        $l_sql = "SELECT * FROM isys_obj " . "LEFT JOIN isys_connection ON isys_connection__isys_obj__id = isys_obj__id " .
            "LEFT JOIN isys_cats_file_list ON isys_cats_file_list__isys_obj__id = isys_obj__id " .
            "LEFT JOIN isys_file_version ON isys_file_version__id = isys_cats_file_list__isys_file_version__id " .
            "LEFT JOIN isys_catg_manual_list ON isys_catg_manual_list__isys_connection__id = isys_connection__id " .
            "LEFT JOIN isys_catg_file_list ON isys_catg_file_list__isys_connection__id = isys_connection__id " .
            "LEFT JOIN isys_catg_emergency_plan_list ON isys_catg_emergency_plan_list__isys_connection__id = isys_connection__id " . "WHERE TRUE " . $p_condition . " ";

        $l_sql .= "AND (isys_catg_manual_list__id IS NOT NULL OR isys_catg_file_list__id IS NOT NULL OR isys_catg_emergency_plan_list__id IS NOT NULL) ";

        if ($p_obj_id !== null) {
            $l_sql .= $this->get_object_condition($p_obj_id);
        }

        if ($p_status !== null) {
            $l_sql .= " AND (isys_obj__status = " . $this->convert_sql_int($p_status) . ") ";
        }

        return $this->retrieve($l_sql);
    }

    /**
     * Save element
     *
     * @param int  $p_cat_level
     * @param int  $p_intOldRecStatus
     * @param bool $p_create
     *
     * @return int|mixed|null
     * @throws isys_exception_dao
     */
    public function save_element(&$p_cat_level, &$p_intOldRecStatus, $p_create = false)
    {
        // Preparation...
        $errorCode = -1;
        $return = null;
        $isCreate = false;

        // Determine create/update
        if (isys_glob_get_param(C__CMDB__GET__CATG) == defined_or_default('C__CATG__OVERVIEW') && isys_glob_get_param(C__GET__NAVMODE) == C__NAVMODE__SAVE) {
            $isCreate = true;
        }

        if (!isset($_GET[C__CMDB__GET__CATLEVEL]) || ($_GET[C__CMDB__GET__CATLEVEL] <= 0 || !$_GET[C__CMDB__GET__CATLEVEL])) {
            $isCreate = true;
        }

        // Check whether we should create a new entry
        if ($isCreate) {
            $id = $this->create(
                $_GET[C__CMDB__GET__OBJECT],
                C__RECORD_STATUS__NORMAL,
                $_POST['C__CATS__FILE__OBJECT__HIDDEN'],
                $_POST['C__CATS__FILE__ASSIGNMENT_TYPE'],
                $_POST['C__CATS__FILE__FILE_LINK'],
                $_POST['C__CATS__FILE__MANUAL_TITLE'],
                $_POST['C__CATS__FILE__EMERGENCY_PLAN_TITLE'],
                $_POST["C__CMDB__CAT__COMMENTARY_" . $this->get_category_type() . $this->get_category_id()]
            );

            $this->m_strLogbookSQL = $this->get_last_query();

            if ($id) {
                $p_cat_level = null;

                return $id;
            }
        } else {
            $return = $this->save(
                $_GET[C__CMDB__GET__CATLEVEL],
                $p_intOldRecStatus,
                $_GET[C__CMDB__GET__OBJECT],
                $_POST['C__CATS__FILE__ASSIGNMENT_TYPE'],
                $_POST['C__CATS__FILE__FILE_LINK'],
                $_POST['C__CATS__FILE__MANUAL_TITLE'],
                $_POST['C__CATS__FILE__EMERGENCY_PLAN_TITLE'],
                $_POST["C__CMDB__CAT__COMMENTARY_" . $this->get_category_type() . $this->get_category_id()]
            );

            $this->m_strLogbookSQL = $this->get_last_query();
        }

        return $return == true ? null : $errorCode;
    }

    /**
     * Update entry
     *
     * @param $connectionId
     * @param $recordStatus
     * @param $objectId
     * @param $targetCategoryId
     * @param $externalLink
     * @param $manualTitle
     * @param $emergencyPlanTitle
     * @param $description
     *
     * @return bool
     * @throws isys_exception_dao
     */
    public function save($connectionId, $recordStatus, $objectId, $targetCategoryId, $externalLink, $manualTitle, $emergencyPlanTitle, $description)
    {
        // Create connection dao
        $databaseComponent = isys_application::instance()->container->get('database');

        // Retrieve data by connection id
        $data = $this->get_data(null, $objectId, ' AND isys_connection__id = ' . $this->convert_sql_id($connectionId), '')
            ->get_row();

        // Determine target category and create entry
        if ($targetCategoryId == defined_or_default('C__CATG__FILE')) {
            // Create category dao
            $targetCategoryDao = new isys_cmdb_dao_category_g_file($databaseComponent);

            // Create entry
            $id = $targetCategoryDao->save(
                $data['isys_catg_file_list__id'],
                $recordStatus ?: $data['isys_catg_file_list__status'],
                null,
                $externalLink,
                $objectId,
                $description
            );
        } elseif ($targetCategoryId == defined_or_default('C__CATG__MANUAL')) {
            // Create category dao
            $targetCategoryDao = new isys_cmdb_dao_category_g_manual($databaseComponent);

            // Create entry
            $id = $targetCategoryDao->save($data['isys_catg_manual_list__id'], $recordStatus ?: $data['isys_catg_manual_list__status'], $manualTitle, $objectId, $description);
        } elseif ($targetCategoryId == defined_or_default('C__CATG__EMERGENCY_PLAN')) {
            // Create category dao
            $targetCategoryDao = new isys_cmdb_dao_category_g_emergency_plan($databaseComponent);

            // Create entry
            $id = $targetCategoryDao->save(
                $data['isys_catg_emergency_plan_list__id'],
                $recordStatus ?: $data['isys_catg_emergency_plan_list__status'],
                $emergencyPlanTitle,
                $objectId,
                $description
            );
        }

        return $id;
    }

    /**
     * Rank element
     *
     * @param $connectionId
     * @param $direction
     *
     * @return bool
     * @throws isys_exception_dao
     */
    public function rank_element($connectionId, $direction)
    {
        /**
         * @todo Create logbock entries
         */

        // Create relation dao
        $relationDao = isys_cmdb_dao_category_g_relation::instance(isys_application::instance()->container->get('database'));

        // Retrieve data by connection id
        $data = $this->get_data(null, null, ' AND isys_connection__id = ' . $this->convert_sql_id($connectionId), '')
            ->get_row();

        // Prepare needed variables
        $categoryEntryId = null;
        $status = null;
        $table = null;

        // Determine target category
        if (!empty($data['isys_catg_file_list__id'])) {
            $table = 'isys_catg_file_list';
        } elseif (!empty($data['isys_catg_manual_list__id'])) {
            $table = 'isys_catg_manual_list';
        } elseif (!empty($data['isys_catg_emergency_plan_list__id'])) {
            $table = 'isys_catg_emergency_plan_list';
        }

        // Set variables based on calculated table value
        $categoryEntryId = $data[$table . '__id'];
        $status = $data[$table . '__status'];
        $targetStatus = $direction == C__CMDB__RANK__DIRECTION_DELETE ? $status + 1 : $status - 1;

        if ($direction == C__CMDB__RANK__DIRECTION_DELETE && $targetStatus == C__RECORD_STATUS__PURGE) {
            // Target status is purge -> DELETE category entry
            $this->delete_entry($categoryEntryId, $table);

            return true;
        }

        $sql = '
                    UPDATE ' . $table . '
                    SET ' . $table . '__status = ' . $this->convert_sql_id($targetStatus) . '
                    WHERE ' . $table . '__id     = ' . $this->convert_sql_id($categoryEntryId) . '
                 ';

        $this->update($sql);

        // Handle relations
        if (!empty($data[$table . "__isys_catg_relation_list__id"])) {
            $relationId = $data[$table . "__isys_catg_relation_list__id"];
            $relationObjectId = $relationDao->get_object_id_by_category_id($relationId, "isys_catg_relation_list");

            $this->set_object_status($relationObjectId, $targetStatus);

            $sql = 'UPDATE isys_catg_relation_list
                    SET isys_catg_relation_list__status = ' . $this->convert_sql_id($targetStatus) . '
                    WHERE isys_catg_relation_list__id = ' . $this->convert_sql_id($relationId);

            $this->update($sql);
        }

        return $this->apply_update();
    }

    /**
     * Create category entry
     *
     * @param     $objectId
     * @param int $recordStatus
     * @param     $assignedObjectId
     * @param     $targetCategoryId
     * @param     $externalLink
     * @param     $manualTitle
     * @param     $emergencyPlanTitle
     * @param     $description
     *
     * @return mixed
     * @throws Exception
     */
    public function create(
        $objectId,
        $recordStatus = C__RECORD_STATUS__NORMAL,
        $assignedObjectId,
        $targetCategoryId,
        $externalLink,
        $manualTitle,
        $emergencyPlanTitle,
        $description
    ) {
        // Get reference to database component
        $databaseComponent = isys_application::instance()->container->get('database');

        // Determine target category and create entry
        if ($targetCategoryId == defined_or_default('C__CATG__FILE')) {
            // Create category dao
            $targetCategoryDao = new isys_cmdb_dao_category_g_file($databaseComponent);

            // Create entry
            $id = $targetCategoryDao->create($assignedObjectId, C__RECORD_STATUS__NORMAL, null, $externalLink, $objectId, $description);
        } elseif ($targetCategoryId == defined_or_default('C__CATG__MANUAL')) {
            // Create category dao
            $targetCategoryDao = new isys_cmdb_dao_category_g_manual($databaseComponent);

            // Create entry
            $id = $targetCategoryDao->create($assignedObjectId, C__RECORD_STATUS__NORMAL, $manualTitle, $objectId, $description);
        } elseif ($targetCategoryId == defined_or_default('C__CATG__EMERGENCY_PLAN')) {
            // Create category dao
            $targetCategoryDao = new isys_cmdb_dao_category_g_emergency_plan($databaseComponent);

            // Create entry
            $id = $targetCategoryDao->create($assignedObjectId, C__RECORD_STATUS__NORMAL, $emergencyPlanTitle, $objectId, $description);
        }

        return $id;
    }

    /**
     * Verifiy posted data, save set_additional_rules and validation state for further usage.
     *
     * @param array $p_data
     * @param mixed $p_prepend_table_field
     *
     * @return  boolean
     */
    public function validate(array $p_data = [], $p_prepend_table_field = false)
    {
        /**
         * @todo Validation: Make assigned object to an mandatory field
         */
        return true;
    }

    /**
     * Verifiy posted data, save set_additional_rules and validation state for further usage.
     *
     * @return  boolean
     */
    public function validate_user_data()
    {
        /**
         * @todo Validation: Make assigned object to an mandatory field
         */
        return true;
    }
}

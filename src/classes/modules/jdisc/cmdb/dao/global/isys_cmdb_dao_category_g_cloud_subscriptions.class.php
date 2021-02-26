<?php
use idoit\Component\Property\Property;
use idoit\Component\Property\Type\DialogDataCaseProperty;
use idoit\Component\Property\Type\DialogPlusProperty;
use idoit\Component\Property\Type\DynamicProperty;
use idoit\Component\Property\Type\ObjectBrowserSecondListProperty;
use idoit\Component\Property\Type\DialogPlusCategoryDependencyProperty;
use idoit\Component\Property\Type\DialogCategoryDependencyProperty;
use idoit\Component\Property\Type\ObjectBrowserMultiselectProperty;
use idoit\Component\Property\Type\TextProperty;
use idoit\Component\Property\Type\VirtualProperty;

/**
 * i-doit
 *
 * DAO: global category for cloud_subscriptionss.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_category_g_cloud_subscriptions extends isys_cmdb_dao_category_global
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'cloud_subscriptions';

    /**
     * This variable holds the language constant of the current category.
     *
     * @var string
     */
    protected $categoryTitle = 'LC__CMDB__CATG__CLOUD_SUBSCRIPTIONS';

    /**
     * @var string
     */
    protected $m_connected_object_id_field = 'isys_connection__isys_obj__id';

    /**
     * @var string
     */
    protected $m_entry_identifier = 'cloud_subscription';

    /**
     * @var bool
     */
    protected $m_has_relation = true;

    /**
     * Is category multi-valued or single-valued?
     *
     * @var  boolean
     */
    protected $m_multivalued = true;

    /**
     * @var string
     */
    protected $m_object_id_field = 'isys_catg_cloud_subscriptions_list__isys_obj__id';

    /**
     * @param isys_request $request
     * @param int|null     $objectId
     *
     * @return array
     * @throws isys_exception_database
     */
    public function getCloudSubscriptionsByRequest(isys_request $request, int $objectId = null)
    {
        if ($objectId === null && $request->get_object_id() !== null) {
            $objectId = $request->get_object_id();
        }

        if ($objectId === null) {
            return [];
        }

        $query = 'SELECT * FROM isys_catg_cloud_subscriptions_list
			WHERE isys_catg_cloud_subscriptions_list__isys_obj__id = ' . $this->convert_sql_id($objectId) . ';';

        $result = $this->retrieve($query);
        $return = [];

        if (count($result)) {
            while ($row = $result->get_row()) {
                $return[$row['isys_catg_cloud_subscriptions_list__id']] = $row['isys_catg_cloud_subscriptions_list__uuid'];
            }
        }

        return $return;
    }

    /** IP_DONE
     * Save global category cloud_subscriptions element.
     *
     * @param   integer $p_cat_level
     * @param   integer &$p_intOldRecStatus
     * @param   boolean $p_create
     *
     * @throws  isys_exception_dao
     * @return  int|null
     */
    public function save_element($p_cat_level, $p_intOldRecStatus, $p_create = false)
    {
        $l_intErrorCode = -1;

        if (isys_glob_get_param(C__CMDB__GET__CATLEVEL) == 0 && isys_glob_get_param(C__CMDB__GET__CATG) == defined_or_default('C__CATG__OVERVIEW') &&
            isys_glob_get_param(C__GET__NAVMODE) == C__NAVMODE__SAVE) {
            $p_create = true;
        }

        if ($p_create) {
            // Overview page and no input was given
            $l_id = $this->create(
                $_GET[C__CMDB__GET__OBJECT],
                C__RECORD_STATUS__NORMAL,
                $_POST['C__CMDB__CATG__CLOUD_SUBSCRIPTIONS__UUID'],
                $_POST['C__CMDB__CATG__CLOUD_SUBSCRIPTIONS__CONSUMED_UNITS'],
                $_POST['C__CMDB__CATG__CLOUD_SUBSCRIPTIONS__PREPAID_ENABLED_UNITS'],
                $_POST['C__CMDB__CATG__CLOUD_SUBSCRIPTIONS__PREPAID_SUSPENDED'],
                $_POST['C__CMDB__CATG__CLOUD_SUBSCRIPTIONS__PREPAID_WARNING'],
                $_POST['C__CATG__CLOUD_SUBSCRIPTIONS__JDISC_STATUS'],
                $_POST['C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_GLOBAL . defined_or_default('C__CATG__CLOUD_SUBSCRIPTIONS', 'C__CATG__CLOUD_SUBSCRIPTIONS')]
            );

            $this->m_strLogbookSQL = $this->get_last_query();

            if ($l_id) {
                $l_catdata['isys_catg_application_list__id'] = $l_id;
                $l_bRet = true;
                $p_cat_level = null;
            } else {
                throw new isys_exception_dao("Could not create category element application");
            }

            return $l_id;
        } else {
            $l_catdata = $this->get_general_data();
            $p_intOldRecStatus = $l_catdata["isys_catg_cloud_subscriptions_list__status"];

            $l_bRet = $this->save(
                $l_catdata['isys_catg_cloud_subscriptions_list__id'],
                C__RECORD_STATUS__NORMAL,
                $_GET[C__CMDB__GET__OBJECT],
                $_POST['C__CMDB__CATG__CLOUD_SUBSCRIPTIONS__UUID'],
                $_POST['C__CMDB__CATG__CLOUD_SUBSCRIPTIONS__CONSUMED_UNITS'],
                $_POST['C__CMDB__CATG__CLOUD_SUBSCRIPTIONS__PREPAID_ENABLED_UNITS'],
                $_POST['C__CMDB__CATG__CLOUD_SUBSCRIPTIONS__PREPAID_SUSPENDED'],
                $_POST['C__CMDB__CATG__CLOUD_SUBSCRIPTIONS__PREPAID_WARNING'],
                $_POST['C__CATG__CLOUD_SUBSCRIPTIONS__JDISC_STATUS'],
                $_POST['C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_GLOBAL . defined_or_default('C__CATG__CLOUD_SUBSCRIPTIONS', 'C__CATG__CLOUD_SUBSCRIPTIONS')]
            );

            $this->m_strLogbookSQL = $this->get_last_query();
        }

        if ($p_create) {
            if (defined('C__CATG__OVERVIEW') && $_GET[C__CMDB__GET__CATG] == C__CATG__OVERVIEW && $_POST[C__GET__NAVMODE] == C__NAVMODE__SAVE) {
                return $l_catdata["isys_catg_cloud_subscriptions_list__id"];
            }
        }

        return ($l_bRet == true) ? null : $l_intErrorCode;
    }

    /** IP_DONE
     *
     * @param      $p_cat_level
     * @param      $p_newRecStatus
     * @param      $p_cloud_subscriptionsObjectId
     * @param      $p_uuid
     * @param      $p_consumedUnits
     * @param      $p_prepaidEnabledUnits
     * @param      $p_prepaidSuspended
     * @param      $p_prepaidWarning
     * @param      $p_jdiscStatus
     * @param      $p_description
     * @param null $p_data array
     *
     * @return bool
     * @throws isys_exception_dao
     */
    public function save(
        $p_cat_level,
        $p_newRecStatus,
        $p_cloud_subscriptionsObjectId = null,
        $p_uuid = null,
        $p_consumedUnits = null,
        $p_prepaidEnabledUnits = null,
        $p_prepaidSuspended = null,
        $p_prepaidWarning = null,
        $p_jdiscStatus = null,
        $p_description = null,
        $p_data = null
    ) {
        $intFields = ['isys_obj__id'];
        $sql_setUpdateFields = 'isys_catg_cloud_subscriptions_list__status = ' . $this->convert_sql_int($p_newRecStatus) . ' ';
        if ($p_data !== null) {
            foreach ($p_data as $propName => $value) {
                if (in_array($propName, $intFields)) {
                    $sql_setUpdateFields .= ', isys_catg_cloud_subscriptions_list__' . $propName . ' = ' . $this->convert_sql_int($value) . ' ';
                } else {
                    $sql_setUpdateFields .= ', isys_catg_cloud_subscriptions_list__' . $propName . ' = ' . $this->convert_sql_text($value) . ' ';
                }
                if ($propName === 'jdisc_status') {
                    $sql_setUpdateFields .= ', isys_catg_cloud_subscriptions_list__' . $propName . ' = ' . $this->convert_sql_id($p_jdiscStatus) . ' ';
                }
            }
        } else {
            $sql_setUpdateFields .= ', isys_catg_cloud_subscriptions_list__isys_obj__id = ' . $this->convert_sql_int($p_cloud_subscriptionsObjectId) . ' ' .
                ', isys_catg_cloud_subscriptions_list__uuid = ' . $this->convert_sql_text($p_uuid) . ' ' .
                ', isys_catg_cloud_subscriptions_list__consumed_units = ' . $this->convert_sql_text($p_consumedUnits) . ' ' .
                ', isys_catg_cloud_subscriptions_list__prepaid_enabled_units = ' . $this->convert_sql_text($p_prepaidEnabledUnits) . ' ' .
                ', isys_catg_cloud_subscriptions_list__prepaid_suspended = ' . $this->convert_sql_text($p_prepaidSuspended) . ' ' .
                ', isys_catg_cloud_subscriptions_list__prepaid_warning = ' . $this->convert_sql_text($p_prepaidWarning) . ' ' .
                ', isys_catg_cloud_subscriptions_list__jdisc_status = ' . $this->convert_sql_id($p_jdiscStatus) . ' ' .
                ', isys_catg_cloud_subscriptions_list__description = ' . $this->convert_sql_text($p_description) . ' ';
        }

        // Update subscriptions assignment
        $l_strSql = "UPDATE isys_catg_cloud_subscriptions_list 
            SET " . $sql_setUpdateFields . "
            WHERE isys_catg_cloud_subscriptions_list__id = " .
            $this->convert_sql_id($p_cat_level);

        if ($this->update($l_strSql) && $this->apply_update()) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     * @param   string  $p_table
     * @param   integer $p_obj_id
     *
     * @return  null
     */
    public function create_connector($p_table, $p_obj_id = null)
    {
        return null;
    }

    /** IP_DONE
     * Creates the condition to the object table
     *
     * @param int|array $p_obj_id
     *
     * @return string
     */
    public function get_object_condition($p_obj_id = null, $p_alias = 'isys_obj')
    {
        if (!empty($p_obj_id)) {
            if (is_array($p_obj_id)) {
                return ' AND (isys_catg_cloud_subscriptions_list__isys_obj__id ' . $this->prepare_in_condition($p_obj_id) . ') ';
            } else {
                return ' AND (isys_catg_cloud_subscriptions_list__isys_obj__id = ' . $this->convert_sql_id($p_obj_id) . ') ';
            }
        }
    }

    /**
     * @return array|DynamicProperty[]
     * @throws \idoit\Component\Property\Exception\UnsupportedConfigurationTypeException
     */
    protected function dynamic_properties()
    {
        return [
            '_subscribers' => new DynamicProperty(
                'LC__CMDB__CATG__CLOUD_SUBSCRIPTIONS__SUBSCRIBERS',
                'isys_catg_cloud_subscriptions_list__id',
                'isys_catg_cloud_subscriptions_list',
                [
                    $this,
                    'getSubscribersCount'
                ]
            )
        ];
    }

    /**
     * @param $row
     *
     * @return int
     */
    public function getSubscribersCount($row)
    {
        $query = 'SELECT count(isys_catg_assigned_subscriptions_list__id) AS subscribers
                FROM isys_catg_assigned_subscriptions_list
                GROUP BY isys_catg_assigned_subscriptions_list__cloud_subscr__id
                HAVING isys_catg_assigned_subscriptions_list__cloud_subscr__id = ' . $this->convert_sql_id($row['isys_catg_cloud_subscriptions_list__id']) . ' LIMIT 1';
        return (int)$this->retrieve($query)->get_row_value('subscribers');
    }

    /**
     * Method for returning the properties.
     *
     * @return  array
     */
    public function properties()
    {
        return [
            'uuid' => new TextProperty(
                'C__CMDB__CATG__CLOUD_SUBSCRIPTIONS__UUID',
                'LC__CMDB__CATG__CLOUD_SUBSCRIPTIONS__UUID',
                'isys_catg_cloud_subscriptions_list__uuid',
                'isys_catg_cloud_subscriptions_list'
            ),
            'consumed_units' => new TextProperty(
                'C__CMDB__CATG__CLOUD_SUBSCRIPTIONS__CONSUMED_UNITS',
                'LC__CMDB__CATG__CLOUD_SUBSCRIPTIONS__CONSUMED_UNITS',
                'isys_catg_cloud_subscriptions_list__consumed_units',
                'isys_catg_cloud_subscriptions_list'
            ),
            'prepaid_enabled_units' => new TextProperty(
                'C__CMDB__CATG__CLOUD_SUBSCRIPTIONS__PREPAID_ENABLED_UNITS',
                'LC__CMDB__CATG__CLOUD_SUBSCRIPTIONS__PREPAID_ENABLED_UNITS',
                'isys_catg_cloud_subscriptions_list__prepaid_enabled_units',
                'isys_catg_cloud_subscriptions_list'
            ),
            'prepaid_suspended' => new TextProperty(
                'C__CMDB__CATG__CLOUD_SUBSCRIPTIONS__PREPAID_SUSPENDED',
                'LC__CMDB__CATG__CLOUD_SUBSCRIPTIONS__PREPAID_SUSPENDED',
                'isys_catg_cloud_subscriptions_list__prepaid_suspended',
                'isys_catg_cloud_subscriptions_list'
            ),
            'prepaid_warning' => new TextProperty(
                'C__CMDB__CATG__CLOUD_SUBSCRIPTIONS__PREPAID_WARNING',
                'LC__CMDB__CATG__CLOUD_SUBSCRIPTIONS__PREPAID_WARNING',
                'isys_catg_cloud_subscriptions_list__prepaid_warning',
                'isys_catg_cloud_subscriptions_list'
            ),
            'jdisc_status' => new DialogPlusProperty(
                'C__CATG__CLOUD_SUBSCRIPTIONS__JDISC_STATUS',
                'LC__CATG__CLOUD_SUBSCRIPTIONS__JDISC_STATUS',
                'isys_catg_cloud_subscriptions_list__jdisc_status',
                'isys_catg_cloud_subscriptions_list',
                'isys_jdisc_status_list'
            ),
            'subscribers' => (new VirtualProperty(
                'C__CMDB__CATG__CLOUD_SUBSCRIPTIONS__SUBSCRIBERS',
                'LC__CMDB__CATG__CLOUD_SUBSCRIPTIONS__SUBSCRIBERS',
                'isys_connection__isys_obj__id',
                'isys_connection'
            ))->mergePropertyData([
                C__PROPERTY__DATA__SELECT     => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                    'SELECT COUNT(isys_catg_assigned_subscriptions_list__id) as amount
                     FROM isys_catg_assigned_subscriptions_list
                    INNER JOIN isys_connection ON isys_connection__id = isys_catg_assigned_subscriptions_list__isys_connection__id',
                    'isys_connection',
                    'isys_connection__id',
                    'isys_connection__isys_obj__id',
                    '',
                    '',
                    null,
                    null
                ),
                C__PROPERTY__DATA__JOIN       => [
                    idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                        'isys_connection',
                        'LEFT',
                        'isys_connection__isys_obj__id',
                        'isys_obj__id'
                    ),
                    idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                        'isys_catg_assigned_subscriptions_list',
                        'LEFT',
                        'isys_connection__id__id',
                        'isys_catg_assigned_subscriptions_list__isys_connection__id'
                    )
                ]
            ])->mergePropertyProvides([
                C__PROPERTY__PROVIDES__LIST => true
            ]),
            'description'              => array_replace_recursive(isys_cmdb_dao_category_pattern::commentary(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATG__DESCRIPTION',
                    C__PROPERTY__INFO__DESCRIPTION => 'Description'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_cloud_subscriptions_list__description',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT isys_catg_cloud_subscriptions_list__description FROM isys_catg_cloud_subscriptions_list',
                        'isys_catg_cloud_subscriptions_list',
                        'isys_catg_cloud_subscriptions_list__id',
                        'isys_catg_cloud_subscriptions_list__isys_obj__id',
                        '',
                        '',
                        null,
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_catg_cloud_subscriptions_list__isys_obj__id'])
                    )
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_GLOBAL . defined_or_default('C__CATG__CLOUD_SUBSCRIPTIONS', 'C__CATG__CLOUD_SUBSCRIPTIONS')
                ],
            ])
        ];
    }

    public function getJdiscStatusChoices()
    {
        return ['key1' => 'value01', 'key2' => 'Value02'];
    }

    /** IP_DONE
     * Synchronizes properties from an import with the database.
     *
     * @param   array   $p_category_data Values of category data to be saved.
     * @param   integer $p_object_id     Current object identifier (from database)
     * @param   integer $p_status        Decision whether category data should be created or just updated.
     *
     * @return  mixed  Returns category data identifier (int) on success, true (bool) if nothing had to be done, otherwise false.
     * @throws  Exception
     * @throws  isys_exception_dao
     * @throws  isys_exception_database
     * @throws  isys_exception_general
     */
    public function sync($p_category_data, $p_object_id, $p_status = 1 /* isys_import_handler_cmdb::C__CREATE */)
    {
        if (is_array($p_category_data) && isset($p_category_data['properties'])) {
            switch ($p_status) {
                case isys_import_handler_cmdb::C__CREATE:
                    if ($p_object_id > 0) {
                        return $this->create(
                            $p_object_id,
                            C__RECORD_STATUS__NORMAL,
                            $p_category_data['properties']['uuid'][C__DATA__VALUE],
                            $p_category_data['properties']['consumed_units'][C__DATA__VALUE],
                            $p_category_data['properties']['prepaid_enabled_units'][C__DATA__VALUE],
                            $p_category_data['properties']['prepaid_suspended'][C__DATA__VALUE],
                            $p_category_data['properties']['prepaid_warning'][C__DATA__VALUE],
                            $p_category_data['properties']['jdisc_status'][C__DATA__VALUE],
                            $p_category_data['properties']['description'][C__DATA__VALUE]
                        );
                    }
                    break;
                case isys_import_handler_cmdb::C__UPDATE:
                    if ($p_category_data['data_id'] > 0) {
                        $this->save(
                            (int)$p_category_data['data_id'],
                            C__RECORD_STATUS__NORMAL,
                            (int)$p_object_id,
                            $p_category_data['properties']['uuid'][C__DATA__VALUE],
                            $p_category_data['properties']['consumed_units'][C__DATA__VALUE],
                            $p_category_data['properties']['prepaid_enabled_units'][C__DATA__VALUE],
                            $p_category_data['properties']['prepaid_suspended'][C__DATA__VALUE],
                            $p_category_data['properties']['prepaid_warning'][C__DATA__VALUE],
                            $p_category_data['properties']['jdisc_status'][C__DATA__VALUE],
                            $p_category_data['properties']['description'][C__DATA__VALUE]
                        );

                        return $p_category_data['data_id'];
                    }
            }
        }

        return false;
    }

    /** IP_DONE
     *
     * @param $p_cloud_subscriptionsObjectId
     * @param $p_newRecStatus
     * @param $p_uuid
     * @param $p_consumedUnits
     * @param $p_prepaidEnabledUnits
     * @param $p_prepaidSuspended
     * @param $p_prepaidWarning
     * @param $p_jdiscStatus
     * @param $p_description
     *
     * @return bool|int
     * @throws isys_exception_dao
     */
    public function create(
        $p_cloud_subscriptionsObjectId,
        $p_newRecStatus,
        $p_uuid = null,
        $p_consumedUnits = null,
        $p_prepaidEnabledUnits = null,
        $p_prepaidSuspended = null,
        $p_prepaidWarning = null,
        $p_jdiscStatus = null,
        $p_description = null
    ) {
        $l_sql = 'INSERT INTO isys_catg_cloud_subscriptions_list SET
                  isys_catg_cloud_subscriptions_list__isys_obj__id = ' . $this->convert_sql_int($p_cloud_subscriptionsObjectId) . ' 
			    , isys_catg_cloud_subscriptions_list__status = ' . $this->convert_sql_id($p_newRecStatus) . '
                , isys_catg_cloud_subscriptions_list__uuid = ' . $this->convert_sql_text($p_uuid) . ' 
                , isys_catg_cloud_subscriptions_list__consumed_units = ' . $this->convert_sql_text($p_consumedUnits) . ' 
                , isys_catg_cloud_subscriptions_list__prepaid_enabled_units = ' . $this->convert_sql_text($p_prepaidEnabledUnits) . ' 
                , isys_catg_cloud_subscriptions_list__prepaid_suspended = ' . $this->convert_sql_text($p_prepaidSuspended) . ' 
                , isys_catg_cloud_subscriptions_list__prepaid_warning = ' . $this->convert_sql_text($p_prepaidWarning) . ' 
                , isys_catg_cloud_subscriptions_list__jdisc_status = ' . $this->convert_sql_id($p_jdiscStatus) . ' 
                , isys_catg_cloud_subscriptions_list__description = ' . $this->convert_sql_text($p_description) . ';';


        if ($this->update($l_sql) && $this->apply_update()) {
            $l_last_id = $this->get_last_insert_id();
            return $l_last_id;
        } else {
            return false;
        }
    }

    /** IP_DONE
     * Set Status for category entry.
     *
     * @param   integer $p_cat_id
     * @param   integer $p_status
     *
     * @return  boolean
     */
    public function set_status($p_cat_id, $p_status)
    {
        $l_sql = 'UPDATE isys_catg_cloud_subscriptions_list SET isys_catg_cloud_subscriptions_list__status = ' . $this->convert_sql_id($p_status) . '
			WHERE isys_catg_cloud_subscriptions_list__id = ' . $this->convert_sql_id($p_cat_id) . ';';

        return ($this->update($l_sql) && $this->apply_update());
    }

    /** IP_DONE
     * Deletes connection between cloud_subscriptions and object.
     *
     * @param   integer $p_cat_level
     *
     * @return  boolean
     * @throws  isys_exception_cmdb
     */
    public function delete($p_cat_level)
    {
        $this->update('DELETE FROM isys_catg_cloud_subscriptions_list WHERE isys_catg_cloud_subscriptions_list__id = ' . $this->convert_sql_id($p_cat_level) . ';');

        if ($this->apply_update()) {
            return true;
        } else {
            throw new isys_exception_cmdb("Could not delete id '" . $p_cat_level . "' in table isys_catg_cloud_subscriptions_list.");
        }
    }

    /** IP_DONE
     * @param int    $objectId
     * @param string $categoryTable
     * @param bool   $hasRelation
     *
     * @return bool
     * @throws isys_exception_database
     */
    public function clear_data($objectId, $categoryTable, $hasRelation = true)
    {
        $l_sql = 'DELETE FROM isys_catg_cloud_subscriptions_list WHERE isys_catg_cloud_subscriptions_list__isys_obj__id = ' . $this->convert_sql_id($objectId);

        return $this->update($l_sql) && $this->apply_update();
    }

    /**
     * @param isys_module_jdisc $l_jdisc
     *
     * @return bool
     * @throws \idoit\Exception\JsonException
     * @throws isys_exception_cmdb
     * @throws isys_exception_dao
     * @throws isys_exception_database
     */
    public function importSubscriptions(isys_module_jdisc $l_jdisc) : bool
    {
        $database = $this->m_db;
        $l_jdiscDao = $l_jdisc->m_dao;
        $cloudSubscriptions = $l_jdiscDao->getCloudSubscriptions();
        $cloudUsers = $l_jdiscDao->getCloudUsers();

        $l_dao = isys_cmdb_dao::factory($database);
        $C__OBJTYPE__LICENCE = $l_dao->get_objtype_id_by_const_string('C__OBJTYPE__LICENCE');
        $C__OBJTYPE__PERSON = $l_dao->get_objtype_id_by_const_string('C__OBJTYPE__PERSON');

        $l_cloudSubscriptionsDao = $this;
        $l_subscriptionsDao = isys_cmdb_dao_category_g_assigned_subscriptions::factory($database);
        $l_emailDao = isys_cmdb_dao_category_g_mail_addresses::instance($database);

        $successfulSubscriptions = [];
        foreach ($cloudSubscriptions as $key => $subscription) {
            $l_subscriptionTitle = $subscription['o365subscriptionname'];
            if (!$l_subscriptionTitle) {
                $l_subscriptionTitle = $subscription['partnumber'];
            }
            $l_licenceId = $l_dao->get_obj_id_by_title($l_subscriptionTitle, null, C__RECORD_STATUS__NORMAL);
            if (!$l_licenceId) {
                $cloudSubscriptions[$key]['licenseObjID'] = $l_dao->create_object(
                    $l_subscriptionTitle,
                    $C__OBJTYPE__LICENCE,
                    C__RECORD_STATUS__NORMAL
                );
            } else {
                $cloudSubscriptions[$key]['licenseObjID'] = $l_licenceId;
            }

            $l_categoryId = $l_cloudSubscriptionsDao->getCategoryIdByUUID($cloudSubscriptions[$key]['licenseObjID'], $subscription['uuid']);
            $capabilityStatus = null;

            if (!empty($subscription['capabilitystatus'])) {
                $capabilityStatus = isys_import::check_dialog('isys_jdisc_status_list', $subscription['capabilitystatus']);
            }

            if ($l_categoryId) {
                $l_cloudSubscriptionsDao->save(
                    $l_categoryId,
                    C__RECORD_STATUS__NORMAL,
                    $cloudSubscriptions[$key]['licenseObjID'],
                    $subscription['uuid'],
                    $subscription['consumedunits'],
                    $subscription['prepaidenabled'],
                    $subscription['prepaidsuspended'],
                    $subscription['prepaidwarning'],
                    $capabilityStatus,
                    ''
                );
                $cloudSubscriptions[$key]['subscriptionCatgID'] = $l_categoryId;
            } else {
                $cloudSubscriptions[$key]['subscriptionCatgID'] = $l_cloudSubscriptionsDao->create(
                    $cloudSubscriptions[$key]['licenseObjID'],
                    C__RECORD_STATUS__NORMAL,
                    $subscription['uuid'],
                    $subscription['consumedunits'],
                    $subscription['prepaidenabled'],
                    $subscription['prepaidsuspended'],
                    $subscription['prepaidwarning'],
                    $capabilityStatus,
                    ''
                );
            }
            $successfulSubscriptions[] = $cloudSubscriptions[$key]['subscriptionCatgID'];
        }
        foreach ($cloudUsers as $key => $user) {
            $cloudUsers[$key]['subscrpartnumberlist'] = json_decode($user['subscrpartnumberlist']);
            foreach ($cloudSubscriptions as $subscription) {
                if (!in_array($subscription['partnumber'], $cloudUsers[$key]['subscrpartnumberlist'])) {
                    continue;
                }

                if (!isset($cloudUsers[$key]['personObjID'])) {
                    $l_personId = $l_emailDao->getObjectIDByEmail($user['clouduserlogin'], C__RECORD_STATUS__NORMAL);
                    if (!$l_personId) {
                        $l_personId = $l_dao->create_object(
                            $user['cloudusername'],
                            $C__OBJTYPE__PERSON,
                            C__RECORD_STATUS__NORMAL
                        );
                        $l_emailDao->create(
                            $l_personId,
                            C__RECORD_STATUS__NORMAL,
                            $user['clouduserlogin'],
                            1
                        );
                    }
                    $cloudUsers[$key]['personObjID'] = $l_personId;
                }

                $l_assignedSubscription = $l_subscriptionsDao->getCategoryIdByPersonAndLicence(
                    $cloudUsers[$key]['personObjID'],
                    $subscription['licenseObjID']
                );
                if (!$l_assignedSubscription) {
                    $l_subscriptionsDao->create(
                        $cloudUsers[$key]['personObjID'],
                        C__RECORD_STATUS__NORMAL,
                        $subscription['licenseObjID'],
                        $subscription['subscriptionCatgID'],
                        ''
                    );
                }
            }
        }
        return (!empty($successfulSubscriptions));
    }

    /**
     * @param int    $p_licenceObjId
     * @param string $p_uuid
     *
     * @return mixed|null
     * @throws isys_exception_database
     */
    public function getCategoryIdByUUID(int $p_licenceObjId, string $p_uuid)
    {
        $l_sql = 'SELECT isys_catg_cloud_subscriptions_list__id AS id
                    FROM isys_catg_cloud_subscriptions_list
                    WHERE (isys_catg_cloud_subscriptions_list__uuid = ' . $this->convert_sql_text($p_uuid) .
                 ') AND (isys_catg_cloud_subscriptions_list__isys_obj__id = ' . $this->convert_sql_id($p_licenceObjId) . ') LIMIT 1;';
        return $this->retrieve($l_sql)->get_row_value('id');
    }
}

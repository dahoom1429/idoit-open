<?php

/**
 * CMDB module eventhandler
 *
 * @package     i-doit
 * @subpackage  Modules
 * @author      Dennis Stücken <dstuecken@i-doit.de>
 * @version     1.5
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_module_cmdb_eventhandler
{
    /**
     * Triggers an event.
     *
     * @param  string $eventHandler
     * @param  array  $args
     *
     * @throws isys_exception_database
     */
    public static function trigger($eventHandler, $args)
    {
        $events = \idoit\Module\Events\Model\Dao::instance(isys_application::instance()->container->get('database'))
            ->getEventSubscriptionsByHandler($eventHandler);

        while ($row = $events->get_row()) {
            isys_module_events::delegate($row, $args);
        }
    }

    /**
     * Returns all corresponding signals as hookable events
     *
     * @used in global hook method isys_module_cmdb::hooks
     *
     * @return isys_array
     */
    public static function hooks()
    {
        return new isys_array([
            'mod.cmdb.objectCreated'            => [
                'title'   => 'LC__MODULE__CMDB_EVENTS__OBJECT_CREATED',
                'handler' => 'isys_module_cmdb_eventhandler::onObjectCreated'
            ],
            'mod.cmdb.objectDeleted'            => [
                'title'   => 'LC__MODULE__CMDB_EVENTS__OBJECT_DELETED',
                'handler' => 'isys_module_cmdb_eventhandler::onObjectDeleted'
            ],
            'mod.cmdb.afterCreateCategoryEntry' => [
                'title'   => 'LC__MODULE__CMDB_EVENTS__AFTER_CATEGORY_CREATE',
                'handler' => 'isys_module_cmdb_eventhandler::onAfterCategoryEntryCreate'
            ],
            'mod.cmdb.afterCategoryEntrySave'   => [
                'title'   => 'LC__MODULE__CMDB_EVENTS__AFTER_CATEGORY_SAVE',
                'handler' => 'isys_module_cmdb_eventhandler::onAfterCategoryEntrySave'
            ],
            'mod.cmdb.beforeRankRecord'         => [
                'title'   => 'LC__MODULE__CMDB_EVENTS__BEFORE_RANK',
                'handler' => 'isys_module_cmdb_eventhandler::onBeforeRankRecord'
            ],
            'mod.cmdb.afterObjectTypeSave'      => [
                'title'   => 'LC__MODULE__CMDB_EVENTS__AFTER_OBJECT_TYPE_SAVE',
                'handler' => 'isys_module_cmdb_eventhandler::onAfterObjectTypeSave'
            ],
            'mod.cmdb.afterObjectTypePurge'     => [
                'title'   => 'LC__MODULE__CMDB_EVENTS__AFTER_OBJECT_TYPE_PURGE',
                'handler' => 'isys_module_cmdb_eventhandler::onAfterObjectTypePurge'
            ]
        ]);
    }

    /**
     * @param int    $objectId
     * @param int    $p_sysID
     * @param int    $p_objectTypeID
     * @param string $p_objectTitle
     * @param int    $p_cmdbStatus
     * @param string $p_username
     *
     * @throws isys_exception_database
     */
    public function onObjectCreated($objectId, $p_sysID, $p_objectTypeID, $p_objectTitle, $p_cmdbStatus, $p_username)
    {
        $database = isys_application::instance()->container->get('database');
        $language = isys_application::instance()->container->get('language');

        $dao = isys_cmdb_dao::instance($database);
        $objectType = $dao->get_object_type($p_objectTypeID);

        self::trigger(__METHOD__, [
            'id'              => $objectId,
            'title'           => $p_objectTitle,
            'cmdbStatusID'    => $p_cmdbStatus,
            'cmdbStatus'      => $language->get($dao->get_object($objectId)->get_row_value('isys_cmdb_status__title')),
            'objectTypeID'    => $p_objectTypeID,
            'objectTypeConst' => $objectType['isys_obj_type__const'],
            'objectType'      => $language->get($objectType['isys_obj_type__title']),
            'sysID'           => $p_sysID,
            'username'        => $p_username
        ]);
    }

    /**
     * @param int $objectId
     *
     * @throws isys_exception_database
     */
    public function onObjectDeleted($objectId)
    {
        $database = isys_application::instance()->container->get('database');
        $language = isys_application::instance()->container->get('language');

        self::trigger(__METHOD__, [
            'id'    => $objectId,
            'title' => isys_cmdb_dao::factory($database)->get_obj_name_by_id_as_string($objectId),
            'type'  => $language->get(isys_cmdb_dao::factory($database)->get_obj_type_name_by_obj_id($objectId))
        ]);
    }

    /**
     * @param  isys_cmdb_dao $p_dao
     * @param  integer       $p_object_id
     * @param  integer       $p_category_id
     * @param  string        $p_title
     * @param  array         $p_row
     * @param  string        $p_table
     * @param  integer       $p_currentStatus
     * @param  integer       $p_newStatus
     * @param  integer       $p_categoryType
     * @param  integer       $p_direction
     *
     * @throws Exception
     */
    public function onBeforeRankRecord(isys_cmdb_dao $p_dao, $p_object_id, $p_category_id, $p_title, $p_row, $p_table, $p_currentStatus, $p_newStatus, $p_categoryType, $p_direction)
    {
        $l_data = [];
        $l_source_table = 'isys_obj';
        $language = isys_application::instance()->container->get('language');

        if (!is_null($p_category_id) && $p_category_id > 0) {
            $l_data = [];

            if (is_a($p_dao, 'isys_cmdb_dao_category')) {
                if (!$p_row || !is_array($p_row)) {
                    /**
                     * @var isys_cmdb_dao_category $p_dao
                     */
                    $p_row = $p_dao->get_data_by_id($p_category_id)
                        ->get_row();
                }

                $l_source_table = $p_dao->get_source_table();
            }
        }

        if ($p_dao instanceof isys_cmdb_dao_category_g_custom_fields) {
            if (is_array($p_row)) {
                foreach ($p_row as $key => $row) {
                    if (isset($row['isys_catg_custom_fields_list__field_key']) && isset($row['isys_catg_custom_fields_list__field_content'])) {
                        if (is_scalar($row['isys_catg_custom_fields_list__field_key'])) {
                            $l_data[$row['isys_catg_custom_fields_list__field_key']] = $language->get($row['isys_catg_custom_fields_list__field_content']);
                        }
                    }
                }
            }
        } else {
            if (is_array($p_row)) {
                foreach ($p_row as $l_key => $l_value) {
                    if ($l_key == 'isys_obj__title') {
                        $l_new_key = 'object';
                    } elseif ($l_key == 'isys_obj_type__title') {
                        $l_new_key = 'objectType';
                        $l_value = $language->get($l_value);
                    } elseif ($l_key == 'isys_obj_type__const') {
                        $l_new_key = 'objectTypeConst';
                    } else {
                        $l_new_key = str_replace('_', '', str_replace('_list', '', str_replace($l_source_table, '', $l_key)));
                    }

                    if (!strstr($l_new_key, '__') && !strstr($l_new_key, 'isys')) {
                        $l_data[$l_new_key] = $l_value;
                    }
                }
            }
        }

        self::trigger(__METHOD__, [
            'title'              => $p_title,
            C__CMDB__GET__OBJECT => $p_object_id,
            'categoryID'         => method_exists($p_dao, 'get_category_id') ? $p_dao->get_category_id() : null,
            'categoryDataID'     => $p_category_id,
            'categoryConst'      => method_exists($p_dao, 'get_category_const') ? $p_dao->get_category_const() : null,
            'currentStatus'      => $p_currentStatus,
            'newStatus'          => $p_newStatus,
            'data'               => $l_data,
            'direction'          => $p_direction
        ]);
    }

    /**
     * @param isys_cmdb_dao_category $dao
     * @param integer                $categoryId
     * @param                        $saveSuccess
     * @param integer                $objectId
     * @param array                  $posts
     * @param array                  $changes
     *
     * @throws isys_exception_database
     */
    public function onAfterCategoryEntrySave(isys_cmdb_dao_category $dao, $categoryId, $saveSuccess, $objectId, $posts, $changes)
    {
        if (!$categoryId && !$dao->is_multivalued()) {
            $l_source_table = strstr($dao->get_source_table(), '_list')
                ? $dao->get_source_table()
                : $dao->get_source_table() . '_list';

            $categoryId = $dao->get_data_by_object($objectId)->get_row_value($l_source_table . '__id');
        }

        $dataPath = method_exists($dao, 'get_category_const')
            ? $dao->get_category_const()
            : 'C__CATG__GLOBAL';

        /**
         * Calculate category constant and type
         *
         * @see ID-6352
         */

        // Set category constant and type
        $categoryConstant = (method_exists($dao, 'get_category_const') ? $dao->get_category_const() : null);
        $categoryType = $dao->get_category_type_const();

        // Check whether we need to do some special handling for custom fields
        if ($dao instanceof isys_cmdb_dao_category_g_custom_fields) {
            // Get category constant of custom category
            $categoryConstantInfo = $dao->get_category_info($dao->get_catg_custom_id());
            $categoryConstant = $categoryConstantInfo['isysgui_catg_custom__const'];

            // Set category type statically to 'custom'
            $categoryType = 'C__CMDB__CATEGORY__TYPE_CUSTOM';
        }

        self::trigger(__METHOD__, [
            'success'        => is_null($saveSuccess) ? 1 : 0,
            'objectID'       => $objectId,
            'categoryID'     => $dao->get_category_id(),
            'categoryConst'  => $categoryConstant,
            'categoryType'   => $categoryType,
            'categoryDataID' => $categoryId,
            'multivalue'     => $dao->is_multivalued(),
            'changes'        => $changes,
            'postData'       => $posts,
            'data'           => isys_cmdb_dao_category_data::initialize($objectId)
                ->path($dataPath)
                ->data()
                ->toArray()
        ]);
    }

    /**
     * @param  integer $typeId
     * @param  array   $posts
     * @param          $success
     *
     * @throws isys_exception_database
     */
    public function onAfterObjectTypeSave($typeId, $posts, $success)
    {
        $database = isys_application::instance()->container->get('database');
        $language = isys_application::instance()->container->get('language');

        $objectTypeData = isys_cmdb_dao::instance($database)->get_objtype($typeId)->get_row();

        self::trigger(__METHOD__, [
            'success'      => $success,
            'typeID'       => $typeId,
            'postData'     => $posts,
            'title'        => $language->get($objectTypeData['isys_obj_type__title']),
            'description'  => $objectTypeData['isys_obj_type__description'],
            'const'        => $objectTypeData['isys_obj_type__const'],
            'status'       => $objectTypeData['isys_obj_type__status'],
            'visible'      => $objectTypeData['isys_obj_type__show_in_tree'],
            'locationType' => $objectTypeData['isys_obj_type__show_in_rack'],
            'color'        => $objectTypeData['isys_obj_type__color'],
            'sysidPrefix'  => $objectTypeData['isys_obj_type__sysid_prefix']
        ]);
    }

    /**
     * @param  $typeId
     * @param  $title
     * @param  $success
     * @param  $data
     *
     * @throws Exception
     */
    public function onAfterObjectTypePurge($typeId, $title, $success, $data)
    {
        self::trigger(__METHOD__, [
            'success'      => $success,
            'typeID'       => $typeId,
            'title'        => isys_application::instance()->container->get('language')->get($title),
            'description'  => $data['isys_obj_type__description'],
            'const'        => $data['isys_obj_type__const'],
            'status'       => $data['isys_obj_type__status'],
            'visible'      => $data['isys_obj_type__show_in_tree'],
            'locationType' => $data['isys_obj_type__show_in_rack'],
            'color'        => $data['isys_obj_type__color'],
            'sysidPrefix'  => $data['isys_obj_type__sysid_prefix']
        ]);
    }

    /**
     * @param  integer                $categoryID
     * @param  integer                $categoryEntryId
     * @param  boolean                $result
     * @param  integer                $objectId
     * @param  isys_cmdb_dao_category $dao
     *
     * @throws isys_exception_database
     */
    public function onAfterCategoryEntryCreate($categoryID, $categoryEntryId, $result, $objectId, isys_cmdb_dao_category $dao)
    {
        $categoryConstant = method_exists($dao, 'get_category_const') ? $dao->get_category_const() : null;
        $categoryType = $dao->get_category_type_const();

        // Check whether we need to do some special handling for custom fields
        if ($dao instanceof isys_cmdb_dao_category_g_custom_fields) {
            $categoryConstantInfo = $dao->get_category_info($dao->get_catg_custom_id());
            $categoryConstant = $categoryConstantInfo['isysgui_catg_custom__const'];

            // Set category type statically to 'custom'
            $categoryType = 'C__CMDB__CATEGORY__TYPE_CUSTOM';
        }

        self::trigger(__METHOD__, [
            'success'        => is_null($result) ? 1 : 0,
            'objectID'       => $objectId,
            'categoryID'     => $dao->get_category_id(),
            'categoryDataID' => $categoryEntryId,
            'categoryConst'  => $categoryConstant,
            'categoryType'   => $categoryType,
            'multivalue'     => $dao->is_multivalued()
        ]);
    }
}

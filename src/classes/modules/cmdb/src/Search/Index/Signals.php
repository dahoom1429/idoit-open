<?php

namespace idoit\Module\Cmdb\Search\Index;

use idoit\Component\Provider\Singleton;
use idoit\Module\Cmdb\Search\Index\Data\CategoryCollector;
use idoit\Module\Search\Index\Engine\Mysql;
use idoit\Module\Search\Index\Manager;
use isys_application;
use isys_cmdb_dao;
use isys_cmdb_dao_category;
use isys_component_signalcollection as SignalCollection;
use isys_tenantsettings;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * i-doit
 *
 * Signal manager for search index signals
 *
 * @package     i-doit
 * @subpackage  Cmdb
 * @author      Dennis Stücken <dstuecken@i-doit.com>
 * @version     1.7
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class Signals
{
    use Singleton;

    /**
     * @var int
     */
    private $startTime;

    /**
     * @var Manager
     */
    private $manager;

    /**
     * Default output is BufferedOutput
     *
     * @var OutputInterface
     */
    private $output;

    /**
     * Disconnect afterCategoryEntrySave Event
     *
     * @throws \Exception
     */
    public function disconnectOnAfterCategoryEntrySave()
    {
        SignalCollection::get_instance()
            ->disconnect('mod.cmdb.afterCategoryEntrySave', [
                $this,
                'onAfterCategoryEntrySave'
            ]);
    }

    public function onBeforeCategoryEntrySave(\isys_cmdb_dao_category $dao, $categoryID, $objectID, $posts, $changes)
    {
        if (isset($changes['C__OBJ__TYPE']) && $changes['C__OBJ__TYPE']['from'] != $changes['C__OBJ__TYPE']['to']) {
            /**
             * @var $collector CategoryCollector
             */
            $collector = $this->manager->getCollector('idoit.cmdb.search.index.category_collector');
            $collector->setCategoryConstants([$dao->get_category_const()]);
            $collector->setObjectIds([$objectID]);

            $this->manager->setMode(Manager::MODE_DELETE);
            $this->manager->setOutput($this->output);
            $this->manager->generateIndex();
        }
    }

    /**
     * @param \isys_cmdb_dao_category $dao
     * @param                         $categoryID
     * @param                         $saveSuccess
     * @param                         $objectID
     * @param                         $posts
     * @param                         $changes
     */
    public function onAfterCategoryEntrySave(\isys_cmdb_dao_category $dao, $categoryID, $saveSuccess, $objectID, $posts, $changes)
    {
        $constant = $dao->get_category_const();

        if ($dao instanceof \isys_cmdb_dao_category_g_custom_fields) {
            $constant = $dao->get_catg_custom_const();
        }

        /**
         * @var $collector CategoryCollector
         */
        $collector = $this->manager->getCollector('idoit.cmdb.search.index.category_collector');
        $collector->setCategoryConstants([$constant]);
        $collector->setObjectIds([$objectID]);

        if ($dao->is_multivalued()) {
            if ($categoryID === null) {
                $sql = 'SELECT isys_catg_custom_fields_list__data__id FROM isys_catg_custom_fields_list WHERE isys_catg_custom_fields_list__id = ' . $dao->get_last_insert_id();
                $categoryID = $dao->get_database_component()->retrieveArrayFromResource($dao->get_database_component()->query($sql))[0]['isys_catg_custom_fields_list__data__id'];
            }
            if ($categoryID) {
                $collector->setCategoryIds([$categoryID]);
            }
        }

        $this->manager->setMode(Manager::MODE_OVERWRITE);
        $this->manager->setOutput($this->output);
        $this->manager->generateIndex();
    }

    /**
     * AfterCsvImport::Event
     *
     * @param $modCsvImport
     * @param $transformedData
     * @param $createdObjects
     * @param $categoryMap
     * @param $updatedCategories
     *
     * @throws \Exception
     */
    public function onAfterCsvImport($modCsvImport, $transformedData, $createdObjects, $categoryMap, $updatedCategories)
    {
        // Use default postImport::Event to update the search index
        $this->onPostImport($this->startTime, $updatedCategories);
    }

    /**
     * PostImport::Event
     *
     * @param       $importStartTime
     * @param array $importedGlobalCategories
     * @param array $importedSpecific
     * @param bool  $indexCustomCategories
     *
     * @throws \Exception
     */
    public function onPostImport($importStartTime, $importedGlobalCategories = [], $importedSpecific = [], $indexCustomCategories = true /*, $importType = '', $rawData = []*/)
    {
        if ($importStartTime > 0) {
            // Retrieve changed objects
            $l_changed_objects = [];
            $l_objects = \isys_cmdb_dao_nexgen::instance(\isys_application::instance()->container->get('database'))
                ->get_objects([
                    'changed_after' => $importStartTime - 180
                ]);

            while ($l_row = $l_objects->get_row()) {
                $l_changed_objects[] = $l_row['isys_obj__id'];
            }

            /**
             * @var $collector CategoryCollector
             */
            $collector = $this->manager->getCollector('idoit.cmdb.search.index.category_collector');
            $collector->setCategoryConstants(array_merge((array)$importedGlobalCategories, (array)$importedSpecific));
            $collector->setObjectIds($l_changed_objects);

            $this->manager->setMode(Manager::MODE_OVERWRITE);
            $this->manager->setOutput($this->output);
            $this->manager->generateIndex();
        }
    }

    /**
     * @param \isys_cmdb_dao_category $dao
     * @param array                   $rawData
     * @param array                   $changes
     */
    public function onMultiEditSaved(\isys_cmdb_dao_category $dao, $rawData, $changes)
    {
        /**
         * @var $collector CategoryCollector
         */
        $collector = $this->manager->getCollector('idoit.cmdb.search.index.category_collector');
        $collector->setCategoryConstants([$dao->get_category_const()]);
        $collector->setObjectIds(array_keys($changes));

        $this->manager->setMode(Manager::MODE_OVERWRITE);
        $this->manager->setOutput($this->output);
        $this->manager->generateIndex();
    }

    /**
     * @param array                     $objects
     * @param int                       $templateID
     * @param \isys_import_handler_cmdb $importHandler
     */
    public function onMassChangeApplied(array $objects, $templateID, \isys_import_handler_cmdb $importHandler)
    {
        /**
         * @var $collector CategoryCollector
         */
        $collector = $this->manager->getCollector('idoit.cmdb.search.index.category_collector');
        $collector->setObjectIds(array_keys($objects));

        $this->manager->setMode(Manager::MODE_OVERWRITE);
        $this->manager->setOutput($this->output);
        $this->manager->generateIndex();
    }

    /**
     * @param int            $objectID
     * @param \isys_cmdb_dao $dao
     */
    public function onObjectDeleted($objectID, $dao)
    {
        $database = $dao->get_database_component();

        if ($objectID > 0) {
            if ($database) {
                $database->query('DELETE FROM isys_search_idx WHERE isys_search_idx__reference = ' . (int)$objectID);
            } else {
                \isys_application::instance()->container->get('logger')
                    ->warning("Search-Index error: Object with id {$objectID} not removed from index. Database not available at this stage.", ['trace' => debug_backtrace()]); // phpcs:ignore
            }
        }
    }

    /**
     * @param isys_cmdb_dao $dao
     * @param int           $objectID
     * @param int           $categoryID
     * @param string        $_title
     * @param array         $_row
     * @param string        $_table
     * @param int           $_currentStatus
     * @param int           $newStatus
     * @param int           $categoryType
     * @param int           $_direction
     */
    public function onBeforeRankRecord(\isys_cmdb_dao $dao, $objectID, $categoryID = null, $_title = null, $_row = null, $_table = null, $_currentStatus = null, $newStatus, $categoryType, $_direction = null)
    {
        switch ($categoryType) {
            case C__CMDB__CATEGORY__TYPE_GLOBAL:
                $catName = $dao->get_catg_name_by_id_as_string($categoryID);
                break;
            case C__CMDB__CATEGORY__TYPE_SPECIFIC:
                $catName = $dao->get_cats_name_by_id_as_string($categoryID);
                break;
            case C__CMDB__CATEGORY__TYPE_CUSTOM:
                $catName = $dao->get_cat_custom_name_by_id_as_string($categoryID);
                break;
        }

        if ($objectID > 0 && $categoryID > 0 && $newStatus == C__RECORD_STATUS__PURGE) {
            $sql = 'DELETE FROM isys_search_idx
                WHERE isys_search_idx__reference = ' . (int)$objectID . '
                AND isys_search_idx__key LIKE ' . $dao->convert_sql_text('%.' . $catName . '.' . $categoryID . '%') . ';';

            $dao->get_database_component()->query($sql);
        }
    }

    public function afterCategoryRank(isys_cmdb_dao_category $dao, $table, $result, $direction, $entries)
    {
        if (empty($entries)) {
            return;
        }

        $relatedObjectIds = [];

        /* tables that contain a field named like <tablename>__isys_obj__id */
        $specialTables = [
            'isys_contact_2_isys_obj',
            'isys_workflow_2_isys_obj',
        ];

        if ((stripos($table, '_2_isys_obj') > 0) && !(in_array($table, $specialTables))) {
            $l_field = 'isys_obj__id';
        } else {
            $l_field = $table . '__isys_obj__id';
        }
        // Retrieve objectIds for entries
        $sql = 'SELECT ' . $l_field . ' FROM ' . $table . ' WHERE ' . $table . '__id IN (' . implode(', ', array_map('intval', ((array) $entries))) . ')';

        $objectIdsResult = $dao->get_database_component()->retrieveArrayFromResource(
            $dao->get_database_component()->query($sql)
        );

        foreach ($objectIdsResult as $row) {
            $relatedObjectIds[] = (int) $row[$l_field];
        }

        if (empty($relatedObjectIds)) {
            return;
        }

        /**
         * @var $collector CategoryCollector
         */
        $collector = $this->manager->getCollector('idoit.cmdb.search.index.category_collector');
        $collector->setObjectIds($relatedObjectIds);
        $collector->setCategoryConstants([$dao->get_category_const()]);

        $this->manager->setMode(Manager::MODE_OVERWRITE);

        $setting = isys_tenantsettings::get('search.index.include_archived_deleted_objects', false);

        if ($direction !== C__CMDB__RANK__DIRECTION_RECYCLE && !$setting) {
            // Assuming we should delete a archived record we need to include the document in deletion mode after ranking first
            isys_tenantsettings::set('search.index.include_archived_deleted_objects', 1);
            $this->manager->setMode(Manager::MODE_DELETE);
        }

        $this->manager->setOutput($this->output);
        $this->manager->generateIndex();

        // Reset setting
        if (((int)$setting) !== 1) {
            isys_tenantsettings::set('search.index.include_archived_deleted_objects', $setting);
        }
    }

    public function afterObjectRank(isys_cmdb_dao $dao, $direction, $rankedObjectIds)
    {
        if (empty($rankedObjectIds)) {
            return;
        }

        /**
         * @var $collector CategoryCollector
         */
        $collector = $this->manager->getCollector('idoit.cmdb.search.index.category_collector');
        $collector->setObjectIds(((array)$rankedObjectIds));

        $this->manager->setMode(Manager::MODE_OVERWRITE);

        $setting = isys_tenantsettings::get('search.index.include_archived_deleted_objects', false);

        if ($direction !== C__CMDB__RANK__DIRECTION_RECYCLE && !$setting) {
            // Assuming we should delete a archived record we need to include the document in deletion mode after ranking first
            isys_tenantsettings::set('search.index.include_archived_deleted_objects', 1);
            $this->manager->setMode(Manager::MODE_DELETE);
        }

        $this->manager->setOutput($this->output);
        $this->manager->generateIndex();

        // Reset setting
        if (((int)$setting) !== 1) {
            isys_tenantsettings::set('search.index.include_archived_deleted_objects', $setting);
        }
    }

    /**
     * Connect all signals
     */
    public function connect()
    {
        $this->createContainerDependencies();

        $output = new BufferedOutput();
        $this->output = $output;

        $this->startTime = microtime(true);

        SignalCollection::get_instance()
            ->connect('mod.cmdb.afterCategoryEntrySave', [
                $this,
                'onAfterCategoryEntrySave'
            ])
            ->connect('mod.cmdb.objectDeleted', [
                $this,
                'onObjectDeleted'
            ])
            ->connect('mod.cmdb.beforeRankRecord', [
                $this,
                'onBeforeRankRecord'
            ])
            ->connect('mod.cmdb.beforeRankRecord', [
                $this,
                'onBeforeRankRecord'
            ])
            ->connect('mod.cmdb.afterCategoryEntryRank', [
                $this,
                'afterCategoryRank'
            ])
            ->connect('mod.cmdb.afterObjectRank', [
                $this,
                'afterObjectRank'
            ])
            ->connect('mod.cmdb.massChangeApplied', [
                $this,
                'onMassChangeApplied'
            ])
            ->connect('mod.cmdb.multiEditSaved', [
                $this,
                'onMultiEditSaved'
            ])
            ->connect('mod.import_csv.afterImport', [
                $this,
                'onAfterCsvImport'
            ])
            ->connect('mod.cmdb.afterLegacyImport', [
                $this,
                'onPostImport'
            ])
        ->connect('mod.cmdb.beforeCategoryEntrySave', [
            $this,
            'onBeforeCategoryEntrySave'
        ]);
    }

    /**
     * @param OutputInterface $output
     */
    public function setOutput(OutputInterface $output)
    {
        $this->output = $output;
    }

    /**
     * Load dependencies for search signals like index manager from container
     */
    private function loadContainerDependencies()
    {
        $this->manager = isys_application::instance()->container->get('idoit.search.index.manager');
    }

    /**
     * Container from modules not available, so create dependencies
     */
    private function createContainerDependencies()
    {
        $searchEngine = new Mysql(isys_application::instance()->container->get('database'));

        $collector = new CategoryCollector(isys_application::instance()->container->get('database'), [], [], []);

        $manager = new Manager($searchEngine, isys_application::instance()->container->get('event_dispatcher'));
        $manager->addCollector($collector, 'idoit.cmdb.search.index.category_collector');

        $this->manager = $manager;
    }
}

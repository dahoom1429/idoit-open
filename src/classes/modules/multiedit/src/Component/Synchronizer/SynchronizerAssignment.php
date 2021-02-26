<?php

namespace idoit\Module\Multiedit\Component\Synchronizer;

use idoit\Component\Property\Property;
use idoit\Module\Cmdb\Interfaces\ObjectBrowserReceiver;

/**
 * @package     Modules
 * @subpackage  multiedit
 * @author      Van Quyen Hoang <qhoang@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class SynchronizerAssignment extends AbstractSynchronizer implements SynchronizerInterface
{
    /**
     * @return $this|mixed
     * @throws \idoit\Exception\JsonException
     */
    public function mapSyncData()
    {
        $this->syncData[self::ENTRY__DATA__ID] = null;
        $ids = current($this->entryData);
        $this->entryKey = key($this->entryData);

        $this->entryData[$this->entryKey] = (\isys_format_json::is_json($ids) ? \isys_format_json::decode($ids) : $ids);

        return $this;
    }

    /**
     * @return mixed|void
     * @throws \isys_exception_database
     * @throws \isys_exception_validation
     */
    public function synchronize()
    {
        if ($this->categoryDao instanceof ObjectBrowserReceiver) {
            $daoClass = (string) substr($this->entryKey, 0, strpos($this->entryKey, '__'));
            $propertyKey = (string) substr($this->entryKey, strpos($this->entryKey, '__') + 2);

            $searchString = $daoClass . '__';
            $replaceString = $daoClass . '::';

            $key = str_replace($searchString, $replaceString, $this->entryKey);
            if (empty($this->entryChanges[$key]['to']) && count($this->entryData[$this->entryKey])) {
                $language = \isys_application::instance()->container->get('language');
                $changes = [];
                foreach ($this->entryData[$this->entryKey] as $objId) {
                    $objectData = $this->categoryDao->get_object($objId)->get_row();
                    $changes[] = $language->get($objectData['isys_obj_type__title']) . ' >> ' . $objectData['isys_obj__title'];
                }
                $this->entryChanges[$key]['to'] = implode(',', $changes);
            }

            $property = $this->merger->getProperties()[$propertyKey];
            if ($property instanceof Property) {
                $propertyUiParams = $property->getUi()
                    ->getParams();

                if (isset($propertyUiParams[\isys_popup_browser_object_ng::C__SECOND_LIST_FORMAT])) {
                    // In Second List we have to retrieve the changes via Format method
                    $formatSelection = $propertyUiParams[\isys_popup_browser_object_ng::C__SECOND_LIST_FORMAT];
                    list($daoClass, $formatMethod) = explode('::', $formatSelection);

                    $dao = call_user_func([
                        $daoClass,
                        'instance'
                    ], \isys_application::instance()->container->get('database'));

                    if (method_exists($dao, $formatMethod)) {
                        foreach ($this->entryData[$this->entryKey] as $id) {
                            $changes[] = $dao->{$formatMethod}($id);
                        }
                        $this->entryChanges[$key]['to'] = implode(',', $changes);
                    }
                }
            }

            // @see  ID-6923  Second parameter of `attachObjects` has to be an array.
            $this->entryData[$this->entryKey] = array_filter((array) $this->entryData[$this->entryKey]);

            $this->categoryDao->attachObjects($this->getObjectId(), $this->entryData[$this->entryKey]);
            $this->synchronizeSuccess = true;
        } else {
            $propertyKey = substr($this->entryKey, strpos($this->entryKey, '__') + 2);
            $selectedObjects = [];

            if (method_exists($this->categoryDao, 'get_selected_objects')) {
                /**
                 * @var $resultSelectedObjects \isys_component_dao_result
                 */
                $resultSelectedObjects = $this->categoryDao->get_selected_objects($this->getObjectId());
                if ($resultSelectedObjects->count()) {
                    while ($selection = $resultSelectedObjects->get_row()) {
                        $selectedObjects[$selection['isys_obj__id']] = $selection['isys_obj__id'];
                    }
                }
            }
            if (isset($this->entryData[$this->entryKey]) && !is_array($this->entryData[$this->entryKey])) {
                $this->entryData[$this->entryKey] = [$this->entryData[$this->entryKey]];
            }

            foreach ($this->entryData[$this->entryKey] as $objectId) {
                $this->syncData[self::ENTRY__PROPERTIES][$propertyKey][C__DATA__VALUE] = $objectId;

                $this->categoryDao->sync($this->syncData, $this->getObjectId(), \isys_import_handler_cmdb::C__CREATE);

                if (isset($selectedObjects[$objectId])) {
                    unset($selectedObjects[$objectId]);
                }
            }

            if (count($selectedObjects) && method_exists($this->categoryDao, 'deleteEntryByAssignedId')) {
                foreach ($selectedObjects as $objId) {
                    $this->categoryDao->deleteEntryByAssignedId($this->getObjectId(), $objId);
                }
            }

            $this->synchronizeSuccess = true;
        }
    }
}

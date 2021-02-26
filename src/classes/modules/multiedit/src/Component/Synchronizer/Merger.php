<?php

namespace idoit\Module\Multiedit\Component\Synchronizer;

use idoit\Component\Property\Property;
use isys_cmdb_dao_category_g_custom_fields;

/**
 * @package     Modules
 * @subpackage  multiedit
 * @author      Van Quyen Hoang <qhoang@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class Merger
{
    /**
     * @var \isys_cmdb_dao_category
     */
    protected $categoryDao;

    /**
     * @var Property[]
     */
    protected $properties;

    /**
     * @var Property[]
     */
    protected $propertiesToMerge;

    /**
     * @var \isys_export_helper[]
     */
    protected $helperClasses = [];

    /**
     * Ignore properties which no provides multiedit
     * per definition but should be handled
     * during save process
     *
     * @var array
     */
    private $ignoredProvisions = [];

    /**
     * @var array
     */
    private static $ignoredCallbackMethods = [
        'dialog',
        'dialog_plus',
        'get_reference_value',
        'object_image',
        'cable_connection'
    ];

    private $ignoredUiTypes = [
        'dialog',
        'dialog_plus'
    ];

    /**
     * @param Property[] $properties
     *
     * @return Merger
     */
    public function setProperties($properties)
    {
        $this->properties = $properties;

        return $this;
    }

    /**
     * @return Property[]
     */
    public function getProperties()
    {
        return $this->properties;
    }

    /**
     * @param \isys_cmdb_dao_category $categoryDao
     *
     * @return Merger
     */
    public function setCategoryDao($categoryDao)
    {
        $this->categoryDao = $categoryDao;

        return $this;
    }

    /**
     * @return $this
     */
    public function mapPropertiesToMerge()
    {
        $categoryId = $this->categoryDao->get_category_id();
        foreach ($this->properties as $propertyKey => $property) {
            if (is_array($this->ignoredProvisions[$categoryId]) && in_array($propertyKey, $this->ignoredProvisions[$categoryId])) {
                continue;
            }

            if ((!$property->getProvides()
                    ->isMultiedit() && $property->getProvides()
                    ->isImport() && $property->getProvides()
                    ->isExport()) || ($property->getProvides()->isMultiedit() && $property->getProvides()->isVirtual())) {
                $this->propertiesToMerge[$propertyKey] = $property;
            }
        }

        return $this;
    }

    /**
     * @param SynchronizerEntry $entry
     *
     * @return $this
     */
    public function merge(&$entry)
    {
        $dataId = $entry->getEntryId();
        $objectId = $entry->getObjectId();
        $syncedData = $entry->getSyncData();
        $result = null;
        $dataSet = [];

        if (!$objectId) {
            return $this;
        }

        $this->categoryDao->set_object_id($objectId);
        $customCategory = ($this->categoryDao->get_category_type() === defined_or_default('C__CMDB__CATEGORY__TYPE_CUSTOM'));

        if ($dataId !== 'new') {
            $result = $this->categoryDao->get_data($dataId);

            if (is_countable($result) && count($result) > 0) {
                if ($customCategory) {
                    while ($currentData = $result->get_row()) {
                        $key = $currentData['isys_catg_custom_fields_list__field_type'] ===
                        'commentary' ? 'description' : $currentData['isys_catg_custom_fields_list__field_type'] . '_' .
                            $currentData['isys_catg_custom_fields_list__field_key'];
                        $dataSet[$dataId][$key] = $currentData;
                    }
                } else {
                    $dataSet[$dataId] = $result->get_row();
                }
            }

            if ($objectId && empty($dataSet)) {
                $result = $this->categoryDao->get_data(null, $objectId);
                $table = $this->categoryDao->get_table();
                while ($row = $result->get_row()) {
                    $dataSet[$row[$table . '__id']] = $row;
                }
            }
        }

        if (count($this->propertiesToMerge)) {
            foreach ($this->propertiesToMerge as $propertyKey => $property) {
                if (isset($syncedData[SynchronizerEntry::ENTRY__PROPERTIES][$propertyKey])) {
                    // Its already been set just skip it
                    continue;
                }

                if ($dataId === 'new') {
                    // Set Default value for new entries
                    $uiData = $property->getUi();
                    $uiParams = $uiData->getParams();
                    $defaultValue = is_scalar($uiParams['default']) ? $uiParams['default'] : $uiData->getDefault();

                    $syncedData[SynchronizerEntry::ENTRY__PROPERTIES][$propertyKey] = [C__DATA__VALUE => $defaultValue];
                    continue;
                }

                if (count($result) > 1) {
                    // Assignment category
                    $data = $this->mergeProperty($property, $propertyKey, $dataSet);

                    $syncedData[SynchronizerEntry::ENTRY__PROPERTIES][$propertyKey] = [C__DATA__VALUE => $data];
                } else {
                    $syncedData[SynchronizerEntry::ENTRY__PROPERTIES][$propertyKey] = [C__DATA__VALUE => $this->mergeEntry($propertyKey, $property, $dataSet, $entry)];
                }
            }
            $entry->setSyncData($syncedData);
        }
    }

    /**
     * @param Property $property
     * @param string   $propertyKey
     * @param array    $dataSet
     *
     * @return array
     */
    private function mergeProperty($property, $propertyKey, $dataSet)
    {
        $data = [];
        $dbField = $property->getData()->getField();

        $customCategory = ($this->categoryDao->get_category_type() === defined_or_default('C__CMDB__CATEGORY__TYPE_CUSTOM'));

        foreach ($dataSet as $entryId => $entryData) {
            if ($customCategory) {
                $data[] = isset($entryData[$propertyKey][$dbField]) ? $entryData[$propertyKey][$dbField]: null;
            } else {
                $data[] = isset($entryData[$dbField]) ? $entryData[$dbField] : null;
            }
        }

        if (count($data) === 1) {
            return $data[0];
        }

        return $data;
    }

    /**
     * @param $helperClass
     * @param $exportValue
     */
    private function registerCategoryIdsToHelperClass($helperClass, $exportValue)
    {
        if (method_exists($this->helperClasses[$helperClass], 'set_category_data_ids')) {
            $categoryDataArr = $categoryDataIds = [];
            $categoryId = $this->categoryDao->get_category_id();
            $categoryType = $this->categoryDao->get_category_type();
            if (is_array($exportValue[C__DATA__VALUE])) {
                if (isset($exportValue[C__DATA__VALUE]['id'])) {
                    $categoryData = $exportValue[C__DATA__VALUE];
                    if (isset($categoryData['ref_id']) && $categoryData['ref_id'] > 0) {
                        $categoryDataIds[$categoryData['ref_id']] = $categoryData['ref_id'];

                        if (isset($categoryData['ref_type']) && defined($categoryData['ref_type'])) {
                            if (strpos($categoryData['ref_type'], 'CATG')) {
                                $categoryType = C__CMDB__CATEGORY__TYPE_GLOBAL;
                            } else {
                                $categoryType = C__CMDB__CATEGORY__TYPE_SPECIFIC;
                            }

                            $categoryDataArr[$categoryType][constant($categoryData['ref_type'])][$categoryData['ref_id']] = $categoryData['ref_id'];
                        }
                    }

                    if (isset($categoryData['id']) && $categoryData['id'] > 0) {
                        $categoryDataIds[$categoryData['id']] = $categoryData['id'];

                        if (isset($categoryData['type']) && !isset($categoryData['sysid']) && defined($categoryData['type'])) {
                            if (strpos($categoryData['type'], 'CATG')) {
                                $categoryType = C__CMDB__CATEGORY__TYPE_GLOBAL;
                            } else {
                                $categoryType = C__CMDB__CATEGORY__TYPE_SPECIFIC;
                            }

                            $categoryDataArr[$categoryType][constant($categoryData['type'])][$categoryData['id']] = $categoryData['id'];
                        }
                    }
                } else {
                    foreach ($exportValue[C__DATA__VALUE] as $categoryData) {
                        if (is_array($categoryData)) {
                            if (isset($categoryData['ref_id']) && $categoryData['ref_id'] > 0) {
                                $categoryDataIds[$categoryData['ref_id']] = $categoryData['ref_id'];

                                if (isset($categoryData['ref_type']) && defined($categoryData['ref_type'])) {
                                    if (strpos($categoryData['ref_type'], 'CATG')) {
                                        $categoryType = C__CMDB__CATEGORY__TYPE_GLOBAL;
                                    } else {
                                        $categoryType = C__CMDB__CATEGORY__TYPE_SPECIFIC;
                                    }

                                    $categoryDataArr[$categoryType][constant($categoryData['ref_type'])][$categoryData['ref_id']] = $categoryData['ref_id'];
                                }
                                continue;
                            }

                            if (isset($categoryData['id']) && $categoryData['id'] > 0) {
                                $categoryDataIds[$categoryData['id']] = $categoryData['id'];

                                if (isset($categoryData['type']) && !isset($categoryData['sysid']) && defined($categoryData['type'])) {
                                    if (strpos($categoryData['type'], 'CATG')) {
                                        $categoryType = C__CMDB__CATEGORY__TYPE_GLOBAL;
                                    } else {
                                        $categoryType = C__CMDB__CATEGORY__TYPE_SPECIFIC;
                                    }

                                    $categoryDataArr[$categoryType][constant($categoryData['type'])][$categoryData['id']] = $categoryData['id'];
                                }

                                continue;
                            }
                        }
                    }
                }
            }

            if (count($categoryDataArr)) {
                $this->helperClasses[$helperClass]->set_category_data_ids($categoryDataArr);
            } else {
                $this->helperClasses[$helperClass]->set_category_data_ids([
                    $categoryType => [
                        $categoryId => $categoryDataIds
                    ]
                ]);
            }
        }
    }

    /**
     * @param $helperClass
     * @param $exportValue
     */
    private function registerObjectIdsToHelperClass($helperClass, $exportValue)
    {
        if (method_exists($this->helperClasses[$helperClass], 'set_object_ids')) {
            $objectIds = $this->helperClasses[$helperClass]->get_object_ids();
            $objectIds[$this->categoryDao->get_object_id()] = $this->categoryDao->get_object_id();

            if (is_array($exportValue[C__DATA__VALUE])) {
                if (isset($exportValue[C__DATA__VALUE]['id'])) {
                    $categoryData = $exportValue[C__DATA__VALUE];
                    if (isset($categoryData['id']) && isset($categoryData['sysid']) && $categoryData['id'] > 0) {
                        $objectIds[$categoryData['id']] = $categoryData['id'];
                    }
                } else {
                    foreach ($exportValue[C__DATA__VALUE] as $categoryData) {
                        if (isset($categoryData['id']) && isset($categoryData['sysid']) && $categoryData['id'] > 0) {
                            $objectIds[$categoryData['id']] = $categoryData['id'];
                        }
                    }
                }
            }

            $this->helperClasses[$helperClass]->set_object_ids($objectIds);
        }
    }

    /**
     * @param string   $propertyKey
     * @param Property $property
     * @param array    $dataSet
     * @param SynchronizerEntry $entry
     *
     * @return mixed
     */
    private function mergeEntry($propertyKey, $property, $dataSet, $entry)
    {
        if (($propertyKey === 'description' && (int)$this->categoryDao->get_category_type() === C__CMDB__CATEGORY__TYPE_CUSTOM) ||
            ($propertyKey === 'contact' && (int)$this->categoryDao->get_category_id() === defined_or_default('C__CATG__CONTACT'))) {
            return null;
        }

        $callback = $property->getFormat()
            ->getCallback();
        $uiType = $property->getUi()
            ->getType();
        $references = $property->getData()
            ->getReferences();
        $syncData = $entry->getSyncData();
        $dbField = $property->getData()
            ->getField();

        if (is_array($callback) && !empty($callback) && !in_array($callback[1], self::$ignoredCallbackMethods)) {
            $helperClass = $callback[0];
            if (isset($this->helperClasses[$helperClass])) {
                $this->helperClasses[$helperClass]->set_row($dataSet[$syncData['data_id']]);
                $this->helperClasses[$helperClass]->set_reference_info($property->getData());
                $this->helperClasses[$helperClass]->set_format_info($property->getFormat());
                $this->helperClasses[$helperClass]->set_ui_info($property->getUi());
            } else {
                $this->helperClasses[$helperClass] = new $helperClass(
                    $dataSet[$syncData['data_id']],
                    \isys_application::instance()->container->get('database'),
                    $property->getData(),
                    $property->getFormat(),
                    $property->getUi()
                );
            }

            if (($unitPropertyKey = $property->getFormat()
                ->getUnit())) {
                $unitProperty = $this->properties[$unitPropertyKey];
                $unitDbField = $unitProperty->getData()
                    ->getField();
                if (isset($dataSet[$unitDbField])) {
                    $this->helperClasses[$helperClass]->set_unit_const($dataSet[$syncData['data_id']][$unitDbField]);
                }
            }

            $exportMethod = $callback[1];
            $exportValue = $this->helperClasses[$helperClass]->$exportMethod($dataSet[$syncData['data_id']][$dbField]);

            if (is_object($exportValue)) {
                $exportValue = [C__DATA__VALUE => $exportValue->get_data()];
            } else {
                $exportValue = (!isset($exportValue[C__DATA__VALUE]) ? [C__DATA__VALUE => $exportValue]: $exportValue);
            }

            $this->registerCategoryIdsToHelperClass($helperClass, $exportValue);
            $this->registerObjectIdsToHelperClass($helperClass, $exportValue);

            $importMethod = $exportMethod . '_import';

            if (method_exists($this->helperClasses[$helperClass], $importMethod)) {
                $importValue = $this->helperClasses[$helperClass]->$importMethod($exportValue);
            } else {
                $importValue = $dataSet[$syncData['data_id']][$dbField];
            }

            return $importValue;
        }
        if (is_array($references)) {
            return $dataSet[$syncData['data_id']][$references[1]];
        }
        if ($dbField) {
            return $dataSet[$syncData['data_id']][$dbField];
        }

        return null;
    }

    public function __construct()
    {
        if (defined('C__CATG__CONTACT')) {
            $this->ignoredProvisions[C__CATG__CONTACT] = [
                'contact'
            ];
        }
    }
}

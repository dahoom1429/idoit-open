<?php

namespace idoit\Module\Multiedit\Component\Multiedit\Formatter\Popup;

use idoit\Component\Property\Property;
use idoit\Module\Multiedit\Component\Multiedit\Formatter\Formatter;
use idoit\Module\Multiedit\Component\Multiedit\Formatter\FormatterManager;
use idoit\Module\Multiedit\Component\Multiedit\Formatter\FormatterInterface;
use idoit\Module\Multiedit\Component\Multiedit\Formatter\Value;
use idoit\Module\Multiedit\Component\Multiedit\Formatter\ValueFormatter;
use isys_callback;
use isys_cmdb_dao_category;
use isys_request;
use isys_smarty_plugin_f_popup;
use idoit\Exception\Exception;
use idoit\Module\Cmdb\Interfaces\ObjectBrowserReceiver;

/**
 * Class ObjectBrowser
 *
 * @package idoit\Module\Multiedit\Component\Multiedit\Formatter\Popup
 */
class ObjectBrowser extends Formatter implements FormatterInterface
{
    /**
     * @var string
     */
    protected static $type = C__PROPERTY__INFO__TYPE__OBJECT_BROWSER;

    /**
     * @var bool
     */
    public static $changeAll = true;

    /**
     * @param isys_cmdb_dao_category $dao
     * @param array                  $objectIds
     *
     * @return array
     * @throws \isys_exception_database
     */
    private static function getViewValuesByObjectIdArray(isys_cmdb_dao_category $dao, array $objectIds)
    {
        $return = [];
        $objectIds = array_filter($objectIds, function ($item) {
            return (is_numeric($item) && $item > 0);
        });

        if (empty($objectIds)) {
            return $return;
        }

        $query = 'SELECT isys_obj__title FROM isys_obj WHERE isys_obj__id IN (' . implode(',', $objectIds) . ');';

        $result = $dao->retrieve($query);
        while ($row = $result->get_row()) {
            $return[] = $row['isys_obj__title'];
        }

        return $return;
    }

    /**
     * @param ValueFormatter $valueFormatter
     *
     * @return Value
     */
    public static function formatSource($valueFormatter)
    {
        $valueObject = $valueFormatter->getValue();
        $value = $valueObject->getValue();
        $rawDataSet = $valueFormatter->getRawDataset();
        $key = $valueFormatter->getPropertyKey();
        $property = $valueFormatter->getProperty();
        $objectId = $valueFormatter->getObjectId();
        $multivalueCategoryWithMultiselection = false;

        list($class, $propKey) = explode('__', $key);

        /**
         * @var $dao \isys_cmdb_dao_category
         */
        $dao = $class::instance(\isys_application::instance()->container->get('database'));
        $language = \isys_application::instance()->container->get('language');
        $field = $property->getData()
            ->getField();
        $propertyParams = $property->getUi()
            ->getParams();
        $references = $property->getData()
            ->getReferences();
        $joins = $property->getData()
            ->getJoins();
        $selectCondition = clone $property->getData()
            ->getSelect()
            ->getSelectCondition();

        // If value is from callback use callback
        if (is_object($propertyParams['p_strValue']) && $propertyParams['p_strValue'] instanceof isys_callback) {
            $request = isys_request::factory();
            $request
                ->set_row($rawDataSet)
                ->set_object_id($objectId)
                ->set_category_data_id($valueFormatter->getEntryId());

            $data = $propertyParams['p_strValue']->execute($request);
            $valueObject->setValue($data);

            if ($data) {
                if (!is_array($data)) {
                    $data = [$data];
                }

                $valueObject->setViewValue(
                    $language->get(
                        self::getViewValuesByObjectIdArray($dao, $data)
                    )
                );
            }

            return $valueObject;
        }

        if ($joins && is_countable($joins)) {
            $joinString = '';
            $rootAlias = 'rootAlias';
            $lastAlias = 'alias0';
            $idField = 'isys_obj__id';
            $titleField = 'isys_obj__title';
            $selection = "{$rootAlias}.{$idField} as id, {$rootAlias}.isys_obj__title as title";
            $max = count($joins) - 1;
            $joinType = 'INNER';

            $aliase = $tables = [];

            foreach ($joins as $count => $selectJoin) {
                $join = clone $selectJoin;
                $idFieldAlias = 'alias' . $count;
                $join->setJoinType($joinType);

                if ($count === 0) {
                    // The right field is always the isys_obj__id
                    $join->setOnRightAlias($rootAlias);
                    $idFieldAlias = $join->getTableAlias() ?: $idFieldAlias;

                    if ($join->getOnLeftAlias() === '') {
                        $join->setOnLeftAlias($idFieldAlias);
                    }
                    $firstAlias = $join->getOnLeftAlias();
                    $firstTable = $join->getTable();
                    $firstField = $firstTable . '__id';
                } else {
                    if ($join->getOnRightAlias() === '') {
                        $join->setOnRightAlias($idFieldAlias);
                    } else {
                        $idFieldAlias = $join->getTableAlias();
                    }

                    if ($join->getOnLeftAlias() === '') {
                        $join->setOnLeftAlias($lastAlias);
                    }

                    $lastAlias = $join->getOnRightAlias();
                    $idField = $join->getOnRight();
                    $titleField = $join->getTable() . '__title';
                }

                $tables[] = ($join->getTableAlias() ? $join->getTableAlias() . '.': '') . $join->getTable();
                $aliase[] = $idFieldAlias;

                if ($join->getTableAlias() === '') {
                    $join->setTableAlias($idFieldAlias);
                }

                if ($join->getOnLeft() === $field) {
                    $conditionField = $join->getOnLeft();
                    $conditionFieldAlias = $join->getOnLeftAlias();
                } elseif ($join->getOnRight() === $field) {
                    $conditionField = $join->getOnRight();
                    $conditionFieldAlias = $join->getOnRightAlias();
                }

                if ((bool)$propertyParams['secondSelection'] === true && ($max - 1) === $count) {
                    $secondListAlias = $idFieldAlias;
                    $secondListIdField = $join->getTable() . '__id';
                    $secondListTitleField = $titleField;

                    if (isset($references[2]) && $join->getTable() === $references[0]) {
                        $secondListTitleField = $references[2];
                    }
                }

                $joinString .= $join;
            }

            $selection = "{$idFieldAlias}.{$idField} as id, {$idFieldAlias}.{$titleField} as title";

            if (!empty($selectCondition->getCondition())) {
                $conditions = $selectCondition->getCondition();
                $newConditions = [];
                foreach ($conditions as $conditionString) {
                    $matches = [];

                    $conditionStringStripped = str_replace(['AND', 'OR'], '', $conditionString);
                    preg_match_all('/\w.*/', $conditionStringStripped, $matches);

                    if (!empty($matches)) {
                        // Replace alias in condition
                        foreach ($matches[0] as $match) {
                            $matchTableWithoutAlias = '';
                            $matchTable = substr($match, 0, strpos($match, '__'));
                            $matchField = $replaceField = substr($match, 0, strpos($match, ' '));
                            if (strpos($matchTable, '.') !== false) {
                                $matchField = substr($matchField, strpos($match, '.') + 1);
                                $matchTableWithoutAlias = substr($matchTable, strpos($match, '.') + 1);
                            }

                            $aliasIndex = array_search($matchTable, $tables) ?: array_search($matchTableWithoutAlias, $tables);

                            if (!is_bool($aliasIndex)) {
                                $tableAlias = $aliase[$aliasIndex];
                                $newField = $tableAlias . '.' . $matchField;
                                $conditionString = str_replace($replaceField, $newField, $conditionString);
                                $newConditions[] = $conditionString;
                                continue;
                            }

                            if ($matchTableWithoutAlias === 'isys_obj') {
                                $conditionString = str_replace($replaceField, 'rootAlias.' . $matchField, $conditionString);
                                $newConditions[] = $conditionString;
                            }
                        }
                    }
                }

                if (!empty($newConditions)) {
                    $selectCondition->setCondition($newConditions);
                }
            }

            if ($secondListAlias) {
                $titleAddition = "{$secondListAlias}.{$secondListTitleField}";
                if ($secondListTitleField === 'isys_catg_ip_list__title') {
                    $titleAddition = "(SELECT isys_cats_net_ip_addresses_list__title FROM isys_cats_net_ip_addresses_list WHERE isys_cats_net_ip_addresses_list__id = {$secondListAlias}.{$secondListIdField})";
                }
                $selection = "{$secondListAlias}.{$secondListIdField} as id, CONCAT({$idFieldAlias}.{$titleField} , ' >> ', {$titleAddition}) as title";
            }

            $selectCondition->addCondition("AND {$rootAlias}.isys_obj__id = '{$objectId}'");

            if (!($dao instanceof ObjectBrowserReceiver) && $property->getUi()->getParams()['p_strPopupType'] !== 'browser_cable_connection_ng') {
                $selectCondition->setCondition(["{$rootAlias}.isys_obj__id = '{$objectId}'"]);
                $multivalueCategoryWithMultiselection = $dao->is_multivalued() && (bool)$propertyParams['multiselection'];
            }

            if (!$propertyParams['multiselection'] || $multivalueCategoryWithMultiselection || (isset($references[0]) && strncmp(strrev($references[0]), 'tsil_', 5) === 0)) {
                if ($valueFormatter->getEntryId()) {
                    if ($firstTable === 'isys_connection') {
                        $index = (int)array_search($firstTable, $tables) + 1;
                        $firstTable = $tables[$index];
                        $firstAlias = $aliase[$index];
                    }

                    $selectCondition->addCondition(" AND {$firstAlias}.{$firstTable}__id = '{$valueFormatter->getEntryId()}'");
                } else {
                    $selectCondition->addCondition("AND {$conditionFieldAlias}.{$conditionField} = '{$value}'");
                }
            }

            $query = "SELECT {$selection} FROM isys_obj as {$rootAlias} {$joinString} {$selectCondition}";
        } elseif ($references) {
            $sourceTable = $property->getData()
                ->getSourceTable() ?: $dao->get_table();
            $sourceAlias = 'main';
            $joinField = $references[1];
            $joinTable = $references[0];
            $joinAlias = 'sec';
            $idField = $joinTable . '__id';
            $titleField = $joinTable . '__title';

            $selection = "{$joinAlias}.{$idField} as id, {$joinAlias}.{$titleField} as title";

            $query = "SELECT {$selection} FROM {$sourceTable} as {$sourceAlias} LEFT JOIN {$joinTable} as {$joinAlias} ON {$joinAlias}.{$joinField} = {$sourceAlias}.{$field} 
          WHERE {$sourceAlias}.{$field} = {$value};";
        }

        if ($query) {
            $result = $dao->retrieve($query);
            $values = [];
            $viewValues = [];
            while ($row = $result->get_row()) {
                $values[] = $row['id'];
                $viewValues[] = $row['title'];
            }

            if ($propertyParams[\isys_popup_browser_object_ng::C__MULTISELECTION]) {
                $values = \isys_format_json::encode($values);
                $viewValues = implode(',', $viewValues);
            } else {
                $values = current($values);
                $viewValues = current($viewValues);
            }

            $valueObject->setValue($values);
            $valueObject->setViewValue($language->get($viewValues));
        }

        return $valueObject;
    }

    /**
     * @param ValueFormatter $valueFormatter
     *
     * @return string|void
     * @throws \Exception
     */
    public static function formatCell($valueFormatter)
    {
        $property = $valueFormatter->getProperty();
        $value = ($valueFormatter->getValue() ?: (new Value()));
        $objectId = $valueFormatter->getObjectId();
        $entryId = $valueFormatter->getEntryId();
        $type = self::$type;

        $content = "<td data-cell-type='{$type}' data-old-value='{$value->getViewValue()}' data-sort='{$value->getViewValue()}' data-key='{$valueFormatter->getPropertyKey()}' class='multiedit-table-td'>%s</td>";

        $params = $property->getUi()
            ->getParams();
        $params['name'] = null;
        $id = $valueFormatter->getPropertyKey();

        if ($id && !$valueFormatter->isDeactivated()) {
            $params['name'] = $id . "[{$objectId}-{$entryId}]";
        }

        if (!$value->getValue()) {
            $request = (new \isys_request())->set_category_data_id($valueFormatter->getEntryId())
                ->set_object_id($valueFormatter->getObjectId());

            if ($params['p_arData'] instanceof \isys_callback) {
                $params['p_arData'] = $params['p_arData']->execute($request);
            }
            if ($params['p_strSelectedID'] instanceof \isys_callback) {
                $params['p_strSelectedID'] = $params['p_strSelectedID']->execute($request);

                if (is_object($params['p_strSelectedID'])) {
                    unset($params['p_strSelectedID']);
                }
            }
            if ($params['p_strValue'] instanceof \isys_callback) {
                $params['p_strValue'] = $params['p_strValue']->execute($request);

                if (is_object($params['p_strValue'])) {
                    unset($params['p_strValue']);
                }
            }

            if (isset($params['p_strPrim'])) {
                unset($params['p_strPrim']);
            }
        } else {
            $params['p_strSelectedID'] = $value->getValue();
            unset($params['p_strValue']);
        }

        if ($params['name']) {
            $params[\isys_popup_browser_object_ng::C__CALLBACK__ACCEPT] = "window.multiEdit.changed(null, '{$params['name']}');";
            $params[\isys_popup_browser_object_ng::C__CALLBACK__DETACH] = "window.multiEdit.changed(null, '{$params['name']}');";
            $params['p_onChange'] = "window.multiEdit.changed(null, '{$params['name']}');";
        }

        $params['p_strPopupType'] = (isset($params['p_strPopupType']) ? $params['p_strPopupType'] : 'browser_object_ng');
        $params[\isys_popup_browser_object_ng::C__EDIT_MODE] = true;
        $params['edit'] = true;
        $params['p_strClass'] = ($params['p_strClass'] ? preg_replace('/(input-[a-z0-9]*)/', 'input-block', $params['p_strClass']) : 'input-block ') . " {$id}";
        $params['inputGroupMarginClass'] = '';

        if ($valueFormatter->isChangeAllRowsActive() && $params['name'] !== null) {
            $params[\isys_popup_browser_object_ng::C__CALLBACK__ACCEPT] .= "window.multiEdit.overwriteAll(null, '{$valueFormatter->getPropertyKey()}', 'objectBrowser');";
            $params[\isys_popup_browser_object_ng::C__CALLBACK__DETACH] .= "window.multiEdit.overwriteAll(null, '{$valueFormatter->getPropertyKey()}', 'objectBrowser');";
        }

        unset($params[\isys_popup_browser_object_ng::C__FORM_SUBMIT], $params[\isys_popup_browser_object_ng::C__RETURN_ELEMENT]);

        $plugin = new isys_smarty_plugin_f_popup();
        $pluginContent = $plugin->navigation_edit(\isys_application::instance()->container->template, $params);

        return sprintf($content, $pluginContent);
    }

    public static function checkFilter($value, $property)
    {
        // TODO: Implement checkFilter() method.
    }
}

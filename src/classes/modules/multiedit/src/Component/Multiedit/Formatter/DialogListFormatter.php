<?php

namespace idoit\Module\Multiedit\Component\Multiedit\Formatter;

use idoit\Exception\Exception;
use idoit\Module\Report\SqlQuery\Structure\SelectJoin;
use isys_smarty_plugin_f_dialog_list;
use idoit\Module\Multiedit\Component\Multiedit\Exception\FormatSourceException;
use idoit\Module\Multiedit\Component\Multiedit\Exception\FormatCellException;

/**
 * Class DialogListFormatter
 *
 * @package idoit\Module\Multiedit\Component\Multiedit\Formatter
 */
class DialogListFormatter extends Formatter implements FormatterInterface
{
    /**
     * @var string
     */
    protected static $type = C__PROPERTY__UI__TYPE__DIALOG_LIST;

    /**
     * @var bool
     */
    public static $changeAll = false;

    /**
     * @param ValueFormatter $valueFormatter
     *
     * @return Value
     * @throws \isys_exception_database
     */
    public static function formatSource($valueFormatter)
    {
        try {
            $valueObject = $valueFormatter->getValue();
            $key = $valueFormatter->getPropertyKey();
            $property = $valueFormatter->getProperty();

            $references = $property->getData()
                ->getReferences();
            $callback = $property->getFormat()
                ->getCallback();
            $propertySelect = $property->getData()
                ->getSelect();
            $propertyJoins = $property->getData()
                ->getJoins();
            $value = $valueObject->getValue();
            $container = \isys_application::instance()->container;
            $language = $container->get('language');

            if ($references && $propertySelect && is_countable($propertyJoins) && count($propertyJoins) && $value > 0) {
                /**
                 * @var $cmdbDao  \isys_cmdb_dao
                 * @var $lastJoin SelectJoin
                 */
                $cmdbDao = $container->get('cmdb_dao');

                $propertySelect->setSelectGroupBy(null);

                // Modifying Query
                $query = $propertySelect->getSelectQuery();
                $query = preg_replace('/(?<=SELECT ).*(?=FROM)/s', ' * ', $query);
                $propertySelect->setSelectQuery($query);

                // Get id and title
                $lastJoin = array_pop($propertyJoins);
                $table = $lastJoin->getTable();
                $valueSelection = $table . '__id';
                $viewSelection = $table . '__title';

                $primaryField = $propertySelect->getSelectPrimaryKey();
                $propertySelect->setSelectCondition($propertySelect->getSelectCondition()
                    ->setCondition([$primaryField . " = {$value}"]));
                $result = $cmdbDao->retrieve($propertySelect);

                $viewValues = $ids = [];
                while ($data = $result->get_row()) {
                    $ids[] = $data[$valueSelection];
                    $viewValues[] = $data[$viewSelection];
                }

                $valueObject->setValue(\isys_format_json::encode($ids));
                $valueObject->setViewValue(\isys_format_json::encode($viewValues));
            }
        } catch (\Exception $e) {
            throw new FormatSourceException("Source Data for property: '{$valueFormatter->getPropertyKey()}' could not be handled for Formatter 'DialogList'. Message: " . $e->getMessage());
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
        try {
            $value = $valueFormatter->getValue() ?: new Value();
            $type = self::$type;

            $content = "<td data-cell-type='{$type}' data-old-value='{$value->getViewValue()}' data-sort='{$value->getViewValue()}' data-key='{$valueFormatter->getPropertyKey()}' class='multiedit-table-td'>%s</td>";

            $property = $valueFormatter->getProperty();

            $objectId = $valueFormatter->getObjectId();
            $entryId = $valueFormatter->getEntryId();

            $params = $property->getUi()
                ->getParams();
            $params['name'] = null;
            $identifier = "[{$objectId}-{$entryId}]";
            $id = $valueFormatter->getPropertyKey();

            if ($id && !$valueFormatter->isDeactivated()) {
                $params['name'] = $id . $identifier;
            }
            $params['p_strSelectedID'] = $value->getValue();
            $newArData = [
                ['id' => null, 'val' => null, 'sel' => false]
            ];

            $request = (new \isys_request())->set_category_data_id($valueFormatter->getEntryId())
                ->set_object_id($valueFormatter->getObjectId());

            if ($params['p_arData'] instanceof \isys_callback) {
                $params['p_arData'] = $params['p_arData']->execute($request);

                if (is_countable($params['p_arData']) && count($params['p_arData']) && !is_array(current($params['p_arData']))) {
                    $selection = \isys_format_json::decode($value->getValue());
                    $newArData = [];
                    foreach ($params['p_arData'] as $id => $val) {
                        $newArData[] = [
                            'id'  => $id,
                            'val' => $val,
                            'sel' => in_array($id, $selection)
                        ];
                    }
                    $params['p_arData'] = $newArData;
                }
            }
            if ($params['p_strSelectedID'] instanceof \isys_callback) {
                $params['p_strSelectedID'] = $params['p_strSelectedID']->execute($request);
            }
            if ($params['p_strValue'] instanceof \isys_callback) {
                $params['p_strValue'] = $params['p_strValue']->execute($request);
            }

            $params['p_bEditMode'] = true;
            $params['p_strClass'] = ($params['p_strClass'] ? preg_replace('/(input-[a-z0-9]*)/', 'input-small', $params['p_strClass']) : 'input-small ') . " {$id}";

            // It is not possible to change all values because there is always a dependency
            if ($valueFormatter->isChangeAllRowsActive() && $params['name'] !== null) {
                unset($params['p_arData']);
                $params['emptyMessage'] = 'LC__MODULE__MULTIEDIT__IT_IS_NOT_POSSIBLE_TO_CHANGE_ALL';
            }
            $params['inputGroupMarginClass'] = '';

            $plugin = new isys_smarty_plugin_f_dialog_list();
            $pluginContent = $plugin->navigation_edit(\isys_application::instance()->container->template, $params);
        } catch (\Exception $e) {
            throw new FormatCellException("Formating cell for property '{$valueFormatter->getPropertyKey()}' could not be handled for Formatter 'DialogList'. Message: " . $e->getMessage());
        }
        return sprintf($content, $pluginContent);
    }

    public static function checkFilter($value, $property)
    {
        // TODO: Implement checkFilter() method.
    }
}

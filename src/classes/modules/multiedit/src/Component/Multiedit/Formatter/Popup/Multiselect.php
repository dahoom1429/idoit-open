<?php

namespace idoit\Module\Multiedit\Component\Multiedit\Formatter\Popup;

use idoit\Component\Property\Property;
use idoit\Module\Multiedit\Component\Multiedit\Formatter\Formatter;
use idoit\Module\Multiedit\Component\Multiedit\Formatter\FormatterManager;
use idoit\Module\Multiedit\Component\Multiedit\Formatter\FormatterInterface;
use idoit\Module\Multiedit\Component\Multiedit\Formatter\Value;
use idoit\Module\Multiedit\Component\Multiedit\Formatter\ValueFormatter;
use isys_smarty_plugin_f_popup;

/**
 * Class Multiselect
 *
 * @package idoit\Module\Multiedit\Component\Multiedit\Formatter\Popup
 */
class Multiselect extends Formatter implements FormatterInterface
{
    /**
     * @var string
     */
    protected static $type = C__PROPERTY__INFO__TYPE__MULTISELECT;

    /**
     * @var bool
     */
    public static $changeAll = true;

    /**
     * @param ValueFormatter $valueFormatter
     *
     * @return Value
     * @throws \Exception
     */
    public static function formatSource($valueFormatter)
    {
        $valueObject = $valueFormatter->getValue();
        $value = $valueObject->getValue();

        if ($value === null) {
            return $valueObject;
        }

        $key = $valueFormatter->getPropertyKey();
        $property = $valueFormatter->getProperty();

        $propertyJoins = $property->getData()
            ->getJoins();
        $propertyDataField = $property->getData()
            ->getField();
        $propertyReferences = $property->getData()
            ->getReferences();
        $propertyUi = $property->getUi()
            ->getParams();
        $propertySourceTable = $property->getData()
            ->getSourceTable();

        $container = \isys_application::instance()->container;
        $cmdbDao = $container->get('cmdb_dao');

        if ($propertySourceTable === 'isys_catg_custom_fields_list') {
            // We know that its from table isys_dialog_plus_custom
            $query = 'SELECT isys_dialog_plus_custom__title FROM isys_dialog_plus_custom WHERE isys_dialog_plus_custom__id = ' . $cmdbDao->convert_sql_id($value);

            $valueObject->setValue($value);
            $valueObject->setViewValue($cmdbDao->retrieve($query)
                ->get_row_value('isys_dialog_plus_custom__title'));

            return $valueObject;
        }

        $return = [];
        $viewValue = [];
        $rootAlias = 'root';
        $joinAlias = 'sec';

        if ($propertyReferences && $propertySourceTable) {
            $rootTable = $propertySourceTable;
            $joinTable = $propertyReferences[0];
            $selectIdField = $rootTable . '__id';
            $selectTitleField = $rootTable . '__title';
            $joinOn = $propertyReferences[1];
            $conditionField = $propertyDataField;
        } elseif ($propertyJoins) {
            if (is_countable($propertyJoins) && count($propertyJoins)) {
                /**
                 * @var $join \idoit\Module\Report\SqlQuery\Structure\SelectJoin
                 */
                $rootTable = '';
                foreach ($propertyJoins as $join) {
                    $joinTable = $join->getTable();
                    if ($rootTable === '') {
                        if ($joinTable !== $propertyReferences[0]) {
                            continue;
                        }

                        $rootTable = $joinTable;
                        $conditionField = $join->getOnLeft();
                        continue;
                    }

                    $selectIdField = $join->getOnRight();
                    $selectTitleField = str_replace('__id', '__title', $join->getOnRight());
                    break;
                }
            }
        }

        if ($selectIdField !== '') {
            $query = "SELECT {$rootAlias}.{$selectIdField}, {$rootAlias}.{$selectTitleField} FROM {$propertySourceTable} as {$rootAlias} 
              INNER JOIN {$joinTable} as {$joinAlias} ON {$joinAlias}.{$selectIdField} = {$rootAlias}.{$selectIdField} WHERE {$joinAlias}.{$conditionField} = " .
                $cmdbDao->convert_sql_id($value);

            $result = $cmdbDao->retrieve($query);
            if (is_countable($result) && count($result)) {
                while ($data = $result->get_row()) {
                    $return[] = $data[$selectIdField];
                    $viewValue[] = $data[$selectTitleField];
                }
            }
        }

        $dataValue = (is_countable($return) && count($return) > 1) ?
            \isys_format_json::encode($return): $return[0];

        $valueObject->setValue($dataValue);
        $valueObject->setViewValue(implode(',', $viewValue));

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
        $value = ($valueFormatter->getValue() ?: new Value());
        $type = self::$type;
        $content = "<td data-cell-type='{$type}' data-old-value='{$value->getViewValue()}' data-sort='{$value->getViewValue()}' data-key='{$valueFormatter->getPropertyKey()}' class='multiedit-table-td'>%s</td>";

        $params = DialogPlus::cellParamsHelper($valueFormatter);
        $chosenRegister = '';

        if (!isset($params['chosen'])) {
            $params['chosen'] = true;
        }

        if ($valueFormatter->isChangeAllRowsActive() && $params['name'] !== null) {
            $params[\isys_popup_browser_object_ng::C__CALLBACK__ACCEPT] .= ";window.multiEdit.overwriteAll(this, '{$valueFormatter->getPropertyKey()}', 'multiselect');";
            $params['p_onChange'] .= ";window.multiEdit.overwriteAll(this, '{$valueFormatter->getPropertyKey()}', 'multiselect');";
        }

        if ($params['name'] !== null) {
            $chosenRegister = "<script type='text/javascript'>new Chosen($('{$params['name']}'), {search_contains: true});</script>";
        }

        $plugin = new isys_smarty_plugin_f_popup();
        $pluginContent = $plugin->navigation_edit(\isys_application::instance()->container->template, $params);

        return sprintf($content, $pluginContent . $chosenRegister);
    }

    /**
     * @param Value    $value
     * @param Property $property
     *
     * @return mixed|void
     */
    public static function checkFilter($value, $property)
    {
        // TODO: Implement checkFilter() method.
    }
}

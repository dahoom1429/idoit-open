<?php

namespace idoit\Module\Multiedit\Component\Multiedit\Formatter\Popup;

use idoit\Component\Property\Property;
use idoit\Module\Multiedit\Component\Multiedit\Formatter\Formatter;
use idoit\Module\Multiedit\Component\Multiedit\Formatter\FormatterManager;
use idoit\Module\Multiedit\Component\Multiedit\Formatter\FormatterInterface;
use idoit\Module\Multiedit\Component\Multiedit\Formatter\Value;
use idoit\Module\Multiedit\Component\Multiedit\Formatter\ValueFormatter;
use idoit\Module\Report\SqlQuery\Structure\SelectCondition;
use isys_smarty_plugin_f_popup;

/**
 * Class DialogPlus
 *
 * @package idoit\Module\Multiedit\Component\Multiedit\Formatter\Popup
 */
class DialogPlus extends Formatter implements FormatterInterface
{
    protected static $type = C__PROPERTY__INFO__TYPE__DIALOG_PLUS;

    /**
     * @var bool
     */
    public static $changeAll = true;

    /**
     * @param $valueFormatter ValueFormatter
     *
     * @return Value
     */
    public static function formatSource($valueFormatter)
    {
        $valueObject = $valueFormatter->getValue();

        if ($valueObject->getValue() === null) {
            return $valueObject;
        }

        $value = $valueObject->getValue();
        $key = $valueFormatter->getPropertyKey();
        $property = $valueFormatter->getProperty();
        $entryId = $valueFormatter->getEntryId();
        $objectId = $valueFormatter->getObjectId();

        $references = $property->getData()
            ->getReferences() ?: [];
        $dataField = $property->getData()
            ->getField();
        $uiParams = $property->getUi()
            ->getParams() ?: [];
        $callback = $property->getFormat()
            ->getCallback() ?: [];
        $propertySelect = $property->getData()->offsetExists(Property::C__PROPERTY__DATA__SELECT) ?
            clone $property->getData()->getSelect(): null;

        $container = \isys_application::instance()->container;
        $language = $container->get('language');
        $condition = null;

        /**
         * @var $cmdbDao        \isys_cmdb_dao
         * @var $callback       \isys_callback
         * @var $arDataCallback \isys_callback
         */
        $cmdbDao = $container->get('cmdb_dao');

        // Simple dialog plus field
        if ($callback[1] === 'get_yes_or_no') {
            $valueObject->setViewValue(($value ? 'LC__UNIVERSAL__YES' : 'LC__UNIVERSAL__NO'));
        } elseif (isset($uiParams['p_arData']) && is_array($uiParams['p_arData'])) {
            $arData = $uiParams['p_arData'];
            if (isset($arData[$value])) {
                $valueObject->setViewValue($language->get($arData[$value]));
            }
        } elseif ($entryId && $objectId && is_object($uiParams['p_arData']) && $uiParams['p_arData'] instanceof \isys_callback) {
            $request = \isys_request::factory();
            $request->set_object_id($objectId)
                ->set_category_data_id($entryId);

            $arDataCallback = $uiParams['p_arData'];
            $data = $arDataCallback->execute($request);
            if (isset($data[$value])) {
                $valueObject->setViewValue($language->get($data[$value]));
            }
        } elseif ($references && strpos($references[0], '_list') === false && strpos($references[0], '_2_') === false) {
            $condition = "{$references[1]} = {$value}";

            $query = "SELECT {$references[0]}__title FROM {$references[0]} WHERE {$condition}";
            $valueObject->setViewValue($language->get($cmdbDao->retrieve($query)
                ->get_row_value($references[0] . '__title')));
        } elseif ($references && $propertySelect) {
            $propertySelect->setSelectCondition(SelectCondition::factory([
                $propertySelect->getSelectPrimaryKey() . ' = ' . $cmdbDao->convert_sql_id($entryId),
                ' AND ' . $references[1] . ' = ' . $cmdbDao->convert_sql_id($value)
            ]));

            $query = $propertySelect->getSelectQuery();

            $data = $cmdbDao->retrieve($propertySelect)
                ->get_row();
            $valueObject->setViewValue(($data ? current($data) : null));
        }

        return $valueObject;
    }

    /**
     * @param $valueFormatter ValueFormatter
     *
     * @return array
     * @throws \Exception
     */
    public static function cellParamsHelper($valueFormatter)
    {
        $property = $valueFormatter->getProperty();
        $value = ($valueFormatter->getValue() ?: new Value());

        $objectId = $valueFormatter->getObjectId();
        $entryId = $valueFormatter->getEntryId();

        $params = $property->getUi()
            ->getParams();
        $identifier = "[{$objectId}-{$entryId}]";
        $params['name'] = null;
        $id = $valueFormatter->getPropertyKey();

        if ($id && !$valueFormatter->isDeactivated()) {
            $params['name'] = $id . $identifier;
        }

        $request = (new \isys_request())
            ->set_category_data_id($entryId)
            ->set_object_id($objectId);

        if ((int)$valueFormatter->getObjectId()) {
            if ($params['p_arData'] instanceof \isys_callback) {
                $params['p_arData'] = $params['p_arData']->execute($request);
            }
            if ($params['p_strSelectedID'] instanceof \isys_callback) {
                $params['p_strSelectedID'] = $params['p_strSelectedID']->execute($request);
            } else {
                $params['p_strSelectedID'] = $value->getValue();
            }
            if ($params['p_strValue'] instanceof \isys_callback) {
                $params['p_strValue'] = $params['p_strValue']->execute($request);
            }
            if ($params['secTableID'] instanceof \isys_callback) {
                $params['secTableID'] = $params['secTableID']->execute($request);
            }
        } elseif ($identifier !== '[-]') {
            unset($params['p_arData']);
        }

        if (!isset($params['p_bDbFieldNN'])) {
            $params['p_bDbFieldNN'] = 0;
        }

        $params['p_strPopupType'] = 'dialog_plus';
        $params['p_bEditMode'] = true;
        $params['inputGroupMarginClass'] = '';
        $params['p_strClass'] = ($params['p_strClass'] ? preg_replace('/(input-[a-z0-9]*)/', 'input-small', $params['p_strClass']) : 'input-small ') . " {$id}";

        if ($params['name']) {
            $params['p_onChange'] = "window.multiEdit.changed(null, '{$params['name']}');";
        }
        unset($params['p_ajaxIdentifier']);

        if (isset($params['p_ajaxIdentifier']) && $valueFormatter->getReferencedProperty()) {
            $params['p_ajaxIdentifier'] = $valueFormatter->getReferencedProperty() . $identifier;
        }

        if ($valueFormatter->getReferencedProperty() && !$valueFormatter->isDeactivated()) {
            FormatterManager::registerDependencyCallback($valueFormatter, $identifier, $params);
            unset($params['p_strSecDataIdentifier']);
        }

        return $params;
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

        $params = self::cellParamsHelper($valueFormatter);

        if ($valueFormatter->isChangeAllRowsActive() && $params['name'] !== null) {
            $params[\isys_popup_browser_object_ng::C__CALLBACK__ACCEPT] .= ";window.multiEdit.overwriteAll(this, '{$valueFormatter->getPropertyKey()}', 'dialogPlus');";
            $params['p_onChange'] .= ";window.multiEdit.overwriteAll(this, '{$valueFormatter->getPropertyKey()}', 'dialogPlus');";
        }

        $plugin = new isys_smarty_plugin_f_popup();
        $pluginContent = $plugin->navigation_edit(\isys_application::instance()->container->template, $params);

        return sprintf($content, $pluginContent);
    }

    /**
     * @param Value    $value
     * @param Property $property
     *
     * @return mixed|void
     */
    public static function checkFilter($value, $property)
    {
        $references = $property->getData()
            ->getReferences();
        $subSelect = $property->getData()
            ->getSelect();

        // Simple dialog plus field
        if (strpos($references[0], '_list') === false && strpos($references[0], '_2_') === false) {
        }
    }
}

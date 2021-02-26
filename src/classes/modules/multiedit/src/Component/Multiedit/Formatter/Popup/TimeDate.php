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
 * Class Date
 *
 * @package idoit\Module\Multiedit\Component\Multiedit\Formatter\Popup
 */
class TimeDate extends Formatter implements FormatterInterface
{
    protected static $type = C__PROPERTY__INFO__TYPE__DATE;

    /**
     * @var bool
     */
    public static $changeAll = true;

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
        $params['p_strValue'] = $value->getValue();

        $request = (new \isys_request())->set_category_data_id($valueFormatter->getEntryId())
            ->set_object_id($valueFormatter->getObjectId());

        if ($params['p_arData'] instanceof \isys_callback) {
            $params['p_arData'] = $params['p_arData']->execute($request);
        }
        if ($params['p_strSelectedID'] instanceof \isys_callback) {
            $params['p_strSelectedID'] = $params['p_strSelectedID']->execute($request);
        }
        if ($params['p_strValue'] instanceof \isys_callback) {
            $params['p_strValue'] = $params['p_strValue']->execute($request);
        }

        if ($params['name']) {
            $params['cellCallback'] = "function(){window.multiEdit.changed(null, '{$params['name']}');}";
            $params['p_onChange'] = "window.multiEdit.changed(null, '{$params['name']}');";
        }

        $params['p_strPopupType'] = 'calendar';
        $params['p_bEditMode'] = true;
        $params['p_strClass'] = ($params['p_strClass'] ? preg_replace('/(input-[a-z0-9]*)/', 'input-small', $params['p_strClass']) : 'input-small ') . " {$id}";
        $params['inputGroupMarginClass'] = '';

        return $params;
    }

    public static function formatCell($valueFormatter)
    {
        $value = $valueFormatter->getValue() ?: new Value();
        $type = self::$type;
        $content = "<td data-cell-type='{$type}' data-old-value='{$value->getViewValue()}' data-sort='{$value->getViewValue()}' data-key='{$valueFormatter->getPropertyKey()}' class='multiedit-table-td'>%s</td>";

        $params = self::cellParamsHelper($valueFormatter);

        if ($valueFormatter->isDeactivated()) {
            $params['name'] = null;
            $params['p_bReadonly'] = true;
        }

        if ($valueFormatter->isChangeAllRowsActive() && $params['name'] !== null) {
            $params['name'] = $valueFormatter->getPropertyKey() . '[-]';
            // @see  ID-6678  Wrong "onChange" action was performed, so that a manual change was not observed.
            $params['p_onChange'] = "window.multiEdit.overwriteAll(this, '{$valueFormatter->getPropertyKey()}', 'popupDate');";
            $params['timeOnChange'] = "window.multiEdit.overwriteAll(this.previous(), '{$valueFormatter->getPropertyKey()}', 'popupDate');";
            $params['cellCallback'] = 'function(){$(this._relative).simulate(\'change\');}';
            //$params['cellCallback'] = "function(){window.multiEdit.overwriteAll($(this._relative), '{$valueFormatter->getPropertyKey()}', 'popupDate');}";
        }

        // @see  ID-6582  Use the new "scroll-observer".
        $params['observeScrollingParent'] = 'multiEditContainer';

        $plugin = new isys_smarty_plugin_f_popup();
        $pluginContent = $plugin->navigation_edit(\isys_application::instance()->container->template, $params);

        return sprintf($content, $pluginContent);
    }

    public static function checkFilter($value, $property)
    {
        // TODO: Implement checkFilter() method.
    }
}

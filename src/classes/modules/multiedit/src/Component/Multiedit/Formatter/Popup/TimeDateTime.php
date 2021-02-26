<?php

namespace idoit\Module\Multiedit\Component\Multiedit\Formatter\Popup;

use idoit\Component\Property\Property;
use idoit\Module\Multiedit\Component\Multiedit\Formatter\Formatter;
use idoit\Module\Multiedit\Component\Multiedit\Formatter\FormatterManager;
use idoit\Module\Multiedit\Component\Multiedit\Formatter\FormatterInterface;
use idoit\Module\Multiedit\Component\Multiedit\Formatter\Value;
use isys_smarty_plugin_f_popup;

/**
 * Class TimeDateTime
 *
 * @package idoit\Module\Multiedit\Component\Multiedit\Formatter\Popup
 */
class TimeDateTime extends Formatter implements FormatterInterface
{
    /**
     * @var string
     */
    protected static $type = C__PROPERTY__INFO__TYPE__DATETIME;

    /**
     * @var bool
     */
    public static $changeAll = true;

    public static function formatCell($valueFormatter)
    {
        $value = ($valueFormatter->getValue() ?: new Value());
        $type = self::$type;
        $content = "<td data-cell-type='{$type}' data-old-value='{$value->getViewValue()}' data-sort='{$value->getViewValue()}' data-key='{$valueFormatter->getPropertyKey()}' class='multiedit-table-td'>%s</td>";

        $params = TimeDate::cellParamsHelper($valueFormatter);

        if ($valueFormatter->isChangeAllRowsActive() && $params['name'] !== null) {
            $params['p_onChange'] = "window.multiEdit.overwriteAll(this, '{$valueFormatter->getPropertyKey()}', 'popupDateTime');";
            $params['timeOnChange'] = "window.multiEdit.overwriteAll(this.previous(), '{$valueFormatter->getPropertyKey()}', 'popupDateTime');";
            $params['cellCallback'] = 'function(){$(this._relative).simulate(\'change\');}';
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
        // TODO: Implement checkFilter() method.
    }
}

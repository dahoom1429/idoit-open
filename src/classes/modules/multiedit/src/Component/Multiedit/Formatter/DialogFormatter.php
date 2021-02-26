<?php

namespace idoit\Module\Multiedit\Component\Multiedit\Formatter;

use idoit\Exception\Exception;
use idoit\Module\Multiedit\Component\Multiedit\Formatter\Popup\DialogPlus;
use isys_smarty_plugin_f_dialog;
use idoit\Module\Multiedit\Component\Multiedit\Exception\FormatSourceException;
use idoit\Module\Multiedit\Component\Multiedit\Exception\FormatCellException;

/**
 * Class DialogFormatter
 *
 * @package idoit\Module\Multiedit\Component\Multiedit\Formatter
 */
class DialogFormatter extends Formatter implements FormatterInterface
{
    /**
     * @var string
     */
    protected static $type = C__PROPERTY__UI__TYPE__DIALOG;

    /**
     * @var bool
     */
    public static $changeAll = true;

    /**
     * @param ValueFormatter $valueFormatter
     *
     * @return Value
     */
    public static function formatSource($valueFormatter)
    {
        try {
            return DialogPlus::formatSource($valueFormatter);
        } catch (\Exception $e) {
            throw new FormatSourceException("Source Data for property: '{$valueFormatter->getPropertyKey()}' could not be handled for Formatter 'Dialog'. Message: " . $e->getMessage());
        }
    }

    /**
     * @param ValueFormatter $valueFormatter
     *
     * @return string
     * @throws \Exception
     */
    public static function formatCell($valueFormatter)
    {
        $value = ($valueFormatter->getValue() ?: (new Value()));
        $type = self::$type;
        $content = "<td data-cell-type='{$type}' data-old-value='{$value->getViewValue()}' data-sort='{$value->getViewValue()}' data-key='{$valueFormatter->getPropertyKey()}' class='multiedit-table-td'>%s</td>";
        $pluginContent = '';

        try {
            $params = DialogPlus::cellParamsHelper($valueFormatter);
            unset($params['p_strPopupType']);

            $plugin = new isys_smarty_plugin_f_dialog();

            if ($valueFormatter->isDisabled()) {
                unset($params['p_strSelectedID'], $params['p_strValue']);
                $params['p_bDisabled'] = true;
                $params['p_strClass'] .= ' multiedit-disabled ';
            }

            if ($valueFormatter->isChangeAllRowsActive() && self::$changeAll && $params['name'] !== null) {
                $params['p_onChange'] .= ";window.multiEdit.overwriteAll(this, '{$valueFormatter->getPropertyKey()}', 'dialog');";
                unset($params['p_bDisabled']);
            }

            $pluginContent = $plugin->navigation_edit(\isys_application::instance()->container->template, $params);
        } catch (\Exception $e) {
            throw new FormatCellException("Formating cell for property '{$valueFormatter->getPropertyKey()}' could not be handled for Formatter 'Dialog'. Message: " . $e->getMessage());
        }

        // Can not use sprintf because there is a problem with Strings which have '%' in it. See category property 'service_level'
        $content = str_replace('%s', $pluginContent, $content);

        return $content;
    }

    public static function checkFilter($value, $property)
    {
        // TODO: Implement checkFilter() method.
    }
}

<?php

namespace idoit\Module\Multiedit\Component\Multiedit\Formatter\Category\G\Ip;

use idoit\Component\Property\Property;
use idoit\Module\Multiedit\Component\Multiedit\Exception\FormatCellException;
use idoit\Module\Multiedit\Component\Multiedit\Formatter\DialogFormatter;
use idoit\Module\Multiedit\Component\Multiedit\Formatter\Formatter;
use idoit\Module\Multiedit\Component\Multiedit\Formatter\FormatterInterface;
use idoit\Module\Multiedit\Component\Multiedit\Formatter\Popup\DialogPlus;
use idoit\Module\Multiedit\Component\Multiedit\Formatter\Value;
use idoit\Module\Multiedit\Component\Multiedit\Formatter\ValueFormatter;
use idoit\Module\Report\SqlQuery\Structure\SelectCondition;
use isys_application;
use isys_helper;
use isys_smarty_plugin_f_dialog;

/**
 * Class DaysFormatter
 *
 * @package idoit\Module\Multiedit\Component\Multiedit\Formatter
 */
class UseStandardGatewayFormatter extends DialogFormatter
{
    /**
     * @var string
     */
    protected static $type = C__PROPERTY__UI__TYPE__DIALOG;

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

            $language = isys_application::instance()->container->get('language');

            if (!is_object($params['p_strSelectedID']) && $params['p_strSelectedID'] > 0) {
                $params['p_strSelectedID'] = 1;
            } else {
                $params['p_strSelectedID'] = 0;
            }

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
}

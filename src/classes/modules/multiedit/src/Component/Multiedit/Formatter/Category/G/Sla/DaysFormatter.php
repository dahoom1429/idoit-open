<?php

namespace idoit\Module\Multiedit\Component\Multiedit\Formatter\Category\G\Sla;

use idoit\Module\Multiedit\Component\Multiedit\Exception\FormatCellException;
use idoit\Module\Multiedit\Component\Multiedit\Formatter\Formatter;
use idoit\Module\Multiedit\Component\Multiedit\Formatter\FormatterInterface;
use idoit\Module\Multiedit\Component\Multiedit\Formatter\Popup\DialogPlus;
use idoit\Module\Multiedit\Component\Multiedit\Formatter\Value;
use idoit\Module\Multiedit\Component\Multiedit\Formatter\ValueFormatter;
use isys_application;
use isys_helper;
use isys_smarty_plugin_f_dialog;

/**
 * Class DaysFormatter
 *
 * @package idoit\Module\Multiedit\Component\Multiedit\Formatter
 */
class DaysFormatter extends Formatter implements FormatterInterface
{
    /**
     * @var string
     */
    protected static $type = C__PROPERTY__UI__TYPE__DIALOG_LIST;

    /**
     * @var bool
     */
    public static $changeAll = true;

    /**
     * @param ValueFormatter $valueFormatter
     *
     * @return string|void
     * @throws \Exception
     */
    public static function formatCell($valueFormatter)
    {
        try {
            $language = isys_application::instance()->container->get('language');

            $value = $valueFormatter->getValue() ?: new Value();
            $type = self::$type;
            $content = "<td data-cell-type='{$type}' data-old-value='{$value->getViewValue()}' data-sort='{$value->getViewValue()}' data-key='{$valueFormatter->getPropertyKey()}' class='multiedit-table-td'>%s</td>";

            $property = $valueFormatter->getProperty();

            $objectId = $valueFormatter->getObjectId();
            $entryId = $valueFormatter->getEntryId();

            $params = DialogPlus::cellParamsHelper($valueFormatter);
            unset($params['p_strPopupType']);

            $params['name'] = null;
            $params['p_bDbFieldNN'] = 1;
            $params['chosen'] = true;
            $params['p_bSort'] = false;
            $params['p_multiple'] = true;
            $params['p_strClass'] = 'input-small re-chosen-select';
            $params['inputGroupMarginClass'] = '';

            $identifier = "[{$objectId}-{$entryId}]";
            $id = $valueFormatter->getPropertyKey();

            if ($id && !$valueFormatter->isDeactivated()) {
                $params['id'] = $id . $identifier;
                $params['name'] = $id . $identifier . '[]';
            }

            $decimalValue = bindec($value->getValue());

            $params['p_strValue'] = implode(',', isys_helper::split_bitwise($decimalValue));
            $params['p_strSelectedID'] = implode(',', isys_helper::split_bitwise($decimalValue));

            $params['p_arData'] = [
                64 => 'LC__UNIVERSAL__CALENDAR__DAYS_MONDAY',
                32 => 'LC__UNIVERSAL__CALENDAR__DAYS_TUESDAY',
                16 => 'LC__UNIVERSAL__CALENDAR__DAYS_WEDNESDAY',
                8  => 'LC__UNIVERSAL__CALENDAR__DAYS_THURSDAY',
                4  => 'LC__UNIVERSAL__CALENDAR__DAYS_FRIDAY',
                2  => 'LC__UNIVERSAL__CALENDAR__DAYS_SATURDAY',
                1  => 'LC__UNIVERSAL__CALENDAR__DAYS_SUNDAY'
            ];

            if ($valueFormatter->isChangeAllRowsActive() && self::$changeAll && $params['name'] !== null) {
                unset($params['p_arData']);
                $params['emptyMessage'] = 'LC__MODULE__MULTIEDIT__IT_IS_NOT_POSSIBLE_TO_CHANGE_ALL';
            }

            $plugin = new isys_smarty_plugin_f_dialog();
            $pluginContent = $plugin->navigation_edit(isys_application::instance()->container->get('template'), $params);
        } catch (\Exception $e) {
            throw new FormatCellException("Formating cell for property '{$valueFormatter->getPropertyKey()}' could not be handled for Formatter 'DialogList'. Message: " .
                $e->getMessage());
        }

        return sprintf($content, $pluginContent);
    }
}

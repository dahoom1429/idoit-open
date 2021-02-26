<?php

namespace idoit\Module\Multiedit\Component\Multiedit\Formatter;

use isys_smarty_plugin_f_button;
use isys_application;

/**
 * Class ActionFormatter
 *
 * @package idoit\Module\Multiedit\Component\Multiedit\Formatter
 */
class ActionFormatter extends Formatter implements FormatterInterface
{
    /**
     * @param ValueFormatter $valueFormatter
     *
     * @return Value|void
     */
    public static function formatSource($valueFormatter)
    {
        // do nothing
    }

    /**
     * @param ValueFormatter $valueFormatter
     *
     * @return string
     */
    public static function formatCell($valueFormatter)
    {
        global $g_dirs;

        $value = $valueFormatter->getValue();
        $language = isys_application::instance()->container->get('language');

        $pattern = '<td class="multiedit-td-action">%s</td>';

        $objectId = $valueFormatter->getObjectId();
        $entryId = $valueFormatter->getEntryId();

        $rowIdentifier = "object-row_{$objectId}-{$entryId}";

        if (!$entryId) {
            $rowIdentifier = "object-row_{$objectId}";
        }

        $options = [
            'p_strClass'        => 'fr btn mr5',
            'icon'              => isys_application::instance()->www_path . 'images/icons/eye-strike.png',
            'p_bInfoIconSpacer' => 0,
            'p_onClick'         => "window.multiEdit.disableRow('{$rowIdentifier}');",
            'type'              => 'button',
            'p_strTitle'        => $language->get('LC__UNIVERSAL__HIDE'),
            'p_strValue'        => '',
            'inputGroupMarginClass' => ''
        ];

        $content = (new isys_smarty_plugin_f_button())->navigation_edit(isys_application::instance()->container->template, $options);

        return sprintf($pattern, $content);
    }

    /**
     * @param Value                              $value
     * @param \idoit\Component\Property\Property $property
     *
     * @return mixed|void
     */
    public static function checkFilter($value, $property)
    {
        // do nothing
    }
}

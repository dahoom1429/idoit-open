<?php

namespace idoit\Module\Multiedit\Component\Multiedit\Formatter;

use isys_smarty_plugin_f_label;

/**
 * Class ObjectInfoFormatter
 *
 * @package idoit\Module\Multiedit\Component\Multiedit\Formatter
 */
class ObjectInfoFormatter extends Formatter implements FormatterInterface
{
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
        $value = $valueFormatter->getValue();

        $title = (is_array($value) && isset($value['title']) ? $value['title'] : \isys_tenantsettings::get('gui.empty_value', '-'));

        $params = [
            'ident' => $title,
            'description' => "<p class='multiedit-td-object-title-info-sysid hide' data-sort='{$value['sysId']}'>SYSID: {$value['sysId']}</p> <p class='multiedit-td-object-title-info-id hide' data-sort='{$value['id']}'>ID: {$value['id']}</p>"
        ];

        $plugin = new isys_smarty_plugin_f_label();

        $content = "<td class='multiedit-td-object-title' data-sort='{$title}' data-key='object-title'>";
        $content .= $plugin->navigation_edit(\isys_application::instance()->container->template, $params);

        return $content . '</td>';
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

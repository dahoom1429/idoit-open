<?php

namespace idoit\Module\Multiedit\Component\Multiedit\Row;

use idoit\Component\Property\Property;
use idoit\Module\Multiedit\Component\Multiedit\Formatter\FormatterManager;
use idoit\Module\Multiedit\Component\Multiedit\Formatter\ObjectInfoFormatter;
use idoit\Module\Multiedit\Component\Multiedit\Formatter\ValueFormatter;
use isys_application;
use isys_smarty_plugin_f_button;

/**
 * Class HeaderRow
 *
 * @package idoit\Module\Multiedit\Component\Multiedit\Row
 */
class HeaderRow extends Row implements RowInterface
{

    /**
     * @return string
     * @throws \Exception
     */
    public function render()
    {
        $properties = current($this->getProperties()
            ->getData());
        $data = $this->getData();
        $language = isys_application::instance()->container->get('language');

        $content = '<thead><tr id="">';
        $pattern = '<th %s>%s %s</th>';

        $hideIcon = "<img class='fr vam m5 multiedit-header-eye-img' %s src='" . isys_application::instance()->www_path . "images/icons/eye-strike.png' />";
        $sortIcon = "<img class='fr vam m5 opacity-30 multiedit-header-sort-img' src='" . isys_application::instance()->www_path . "images/icons/silk/bullet_arrow_up.png' />";

        $content .= sprintf($pattern, 'class="multiedit-td-action"', '', '');
        $content .= sprintf($pattern, 'style="width:150px" class="multiedit-td-object-title mouse-pointer multiedit-header-sort-asc" data-key="object-title" onclick="window.multiEdit.sortContent(this, \'object-title\')"', $language->get('LC__UNIVERSAL__OBJECT_TITLE'), $sortIcon);

        $options = [
            'p_strClass'        => 'fr btn mr5',
            'icon'              => isys_application::instance()->www_path . 'images/icons/eye-strike.png',
            'p_bInfoIconSpacer' => 0,
            'type'              => 'button',
            'p_strTitle'        => $language->get('LC__UNIVERSAL__HIDE'),
            'p_strValue'        => '',
            'inputGroupMarginClass' => '',
            'p_onMouseOver' => 'window.multiEdit.disableSort(this);',
            'p_onMouseOut' => 'window.multiEdit.enableSort(this);'
        ];

        foreach ($properties as $propertyKey => $property) {
            $options['p_onClick'] = "window.multiEdit.disableColumn('{$propertyKey}')";
            $hideColumnBtn = (new isys_smarty_plugin_f_button())->navigation_edit(isys_application::instance()->container->template, $options);

            $content .= sprintf(
                $pattern,
                'class="mouse-pointer multiedit-header-sort-asc" enable-sort=1 data-key="' . $propertyKey . '" onclick="window.multiEdit.sortContent(this, \'' . $propertyKey . '\')"',
                $language->get($property->getInfo()->getTitle()),
                $sortIcon . $hideColumnBtn
            );
        }

        return $content . '</tr></thead>';
    }
}

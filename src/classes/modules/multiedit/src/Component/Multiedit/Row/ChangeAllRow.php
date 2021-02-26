<?php

namespace idoit\Module\Multiedit\Component\Multiedit\Row;

use idoit\Component\Property\Property;
use idoit\Module\Multiedit\Component\Multiedit\Formatter\FormatterManager;
use idoit\Module\Multiedit\Component\Multiedit\Formatter\ObjectInfoFormatter;
use idoit\Module\Multiedit\Component\Multiedit\Formatter\Value;
use idoit\Module\Multiedit\Component\Multiedit\Formatter\ValueFormatter;
use isys_smarty_plugin_f_button;

/**
 * Class ChangeAllRow
 *
 * @package idoit\Module\Multiedit\Component\Multiedit\Row
 */
class ChangeAllRow extends Row implements RowInterface
{

    /**
     * @var bool
     */
    private $fieldsDisabled = false;

    /**
     * @return $this
     */
    public function disableFields()
    {
        $this->fieldsDisabled = true;

        return $this;
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function render()
    {
        global $g_dirs;
        $properties = current($this->getProperties()
            ->getData());
        $data = $this->getData();
        $language = \isys_application::instance()->container->get('language');

        $valueFormater = new ValueFormatter();

        $options = [
            'p_strClass'        => 'fr btn mr5',
            'icon'              => $g_dirs['images'] . 'icons/silk/arrow_rotate_anticlockwise-half.png',
            'p_bInfoIconSpacer' => 0,
            'p_onClick'         => 'window.multiEdit.enableRows();',
            'type'              => 'button',
            'p_strTitle'        => $language->get('LC__UNIVERSAL__BUTTON_RESET'),
        ];

        $showAllBtn = (new isys_smarty_plugin_f_button())->navigation_edit(\isys_application::instance()->container->template, $options);

        if ($this->fieldsDisabled) {
            $valueFormater->setDeactivated(true);
            $showAllBtn = '';
        }

        $content = "<tr id='changeAllRow' class='multiedit-table-tr-changeall'>";
        $content .= "<td class='bold'>{$showAllBtn}</td>";
        $content .= "<td class='bold'>{$language->get('LC__UNIVERSAL__ALL')}</td>";

        /**
         * @var $property Property
         */
        foreach ($properties as $propertyKey => $property) {
            $formatter = FormatterManager::getFormatterByProperty($propertyKey);

            if ($formatter === false) {
                $formatter = FormatterManager::getFormatterByUiType($property->getUi()->getType());
            }

            $callbackProperty = null;

            $valueFormater->unsetReferencedPropertyKey()
                ->unsetReferencedProperty()
                ->unsetReferencedPropertyValue();

            $value[$propertyKey] = new Value();

            if ($property->getDependency()
                ->getPropkey()) {
                list($class, ) = explode('__', $propertyKey);
                $referencedPropertyKey = $class . '__' . $property->getDependency()
                        ->getPropkey();
                $valueFormater->setReferencedProperty($properties[$referencedPropertyKey]);
            }

            $params = $property->getUi()
                ->getParams();

            if (!isset($params['p_bDbFieldNN'])) {
                $params['p_bDbFieldNN'] = 0;
            }

            $property->getUi()
                ->setParams($params);

            if ($property->getDependency()
                    ->getPropkey() || ($callbackProperty = ($property->getFormat()
                    ->getUnit() ?: $property->getFormat()
                    ->getRequires()))) {
                if ($callbackProperty) {
                    $referencedPropertyKey = $class . '__' . $callbackProperty;
                } else {
                    $referencedPropertyKey = $class . '__' . $property->getDependency()
                            ->getPropkey();
                }
                $valueFormater->setReferencedPropertyKey($referencedPropertyKey);
                $valueFormater->setReferencedProperty($properties[$referencedPropertyKey]);
                $valueFormater->setReferencedPropertyValue($value[$propertyKey]);
            }

            $valueFormater->activateChangeAllRows()
                ->setValue($value[$propertyKey])
                ->setPropertyKey($propertyKey)
                ->setDataset($value)
                ->setProperty($property);

            if ($this->getObjectId() && !(is_array($params) &&
                    isset($params['p_strPopupType']) && $params['p_strPopupType'] === 'browser_object_ng')) {
                $valueFormater->setObjectId($this->getObjectId());
            }

            $content .= $formatter::formatCell($valueFormater);
        }

        return $content . '</tr>';
    }
}

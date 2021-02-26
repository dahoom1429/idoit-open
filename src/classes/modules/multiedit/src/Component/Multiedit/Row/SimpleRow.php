<?php

namespace idoit\Module\Multiedit\Component\Multiedit\Row;

use idoit\Component\Property\Property;
use idoit\Module\Multiedit\Component\Multiedit\Formatter\ActionFormatter;
use idoit\Module\Multiedit\Component\Multiedit\Formatter\FormatterManager;
use idoit\Module\Multiedit\Component\Multiedit\Formatter\ObjectInfoFormatter;
use idoit\Module\Multiedit\Component\Multiedit\Formatter\ValueFormatter;
use idoit\Module\Multiedit\Component\Multiedit\Formatter\Check;

/**
 * Class SimpleRow
 *
 * @package idoit\Module\Multiedit\Component\Multiedit\Row
 */
class SimpleRow extends Row implements RowInterface
{

    /**
     * @return mixed|string
     */
    public function render()
    {
        $properties = current($this->getProperties()
            ->getData());
        $data = $this->getData();

        /**
         * @var $property   Property
         * @var $classCheck Check\isys_cmdb_dao_category_g_ip
         */

        $content = "<tr id='object-row_{$this->getObjectId()}-{$this->getId()}' class='multiedit-tr-data' data-entry='{$this->getObjectId()}-{$this->getId()}'>";

        $valueFormater = (new ValueFormatter())->setEntryId($this->getId())
            ->setObjectId($this->getObjectId())
            ->setValue($this->getObjectData());

        $content .= ActionFormatter::formatCell($valueFormater);
        $content .= ObjectInfoFormatter::formatCell($valueFormater);

        foreach ($properties as $propertyKey => $property) {
            $formatter = FormatterManager::getFormatterByProperty($propertyKey);

            if ($formatter === false) {
                $formatter = FormatterManager::getFormatterByUiType($property->getUi()->getType());
            }

            $callbackProperty = null;

            $valueFormater->unsetReferencedPropertyKey()
                ->unsetReferencedProperty()
                ->unsetReferencedPropertyValue();

            list($class, $propKey) = explode('__', $propertyKey);

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
                $valueFormater->setReferencedPropertyValue($data[$referencedPropertyKey]);
            }

            $valueFormater->setPropertyKey($propertyKey)
                ->setValue($data[$propertyKey])
                ->setDataset($data)
                ->setProperty($property);

            if (class_exists('idoit\Module\Multiedit\Component\Multiedit\Formatter\Check\\' . $class) && strpos($valueFormater->getEntryId(), 'new') === false) {
                $classCheck = 'idoit\Module\Multiedit\Component\Multiedit\Formatter\Check\\' . $class;
                $valueFormater->setDisabled($classCheck::check($propKey, $data));
            }

            $content .= $formatter::formatCell($valueFormater);
        }

        return $content . '</tr>';
    }
}

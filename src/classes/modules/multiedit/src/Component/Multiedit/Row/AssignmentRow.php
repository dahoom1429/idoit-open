<?php

namespace idoit\Module\Multiedit\Component\Multiedit\Row;

use idoit\Module\Multiedit\Component\Multiedit\Formatter\ObjectInfoFormatter;
use idoit\Module\Multiedit\Component\Multiedit\Formatter\ValueFormatter;
use idoit\Module\Multiedit\Component\Multiedit\Formatter\FormatterManager;
use idoit\Module\Multiedit\Component\Multiedit\Formatter\Value;
use idoit\Module\Multiedit\Component\Multiedit\Formatter\ActionFormatter;
use idoit\Component\Property\Property;

/**
 * Class AssignmentRow
 *
 * @package idoit\Module\Multiedit\Component\Multiedit\Row
 */
class AssignmentRow extends Row implements RowInterface
{

    /**
     * @return string
     */
    public function render()
    {
        global $g_dirs;

        $propertiesSource = $this->getProperties();
        $propertiesCount = $propertiesSource->count();
        $properties = current($propertiesSource->getData());

        $propertyKey = key($properties);
        /**
         * @var Property $property
         */
        $property = $properties[$propertyKey];
        $uiParams = $property->getUi()
            ->getParams();

        $daoClass = substr($propertyKey, 0, strpos($propertyKey, '__'));

        $data = $this->getData();

        $valueFormater = (new ValueFormatter())->setEntryId($this->getId())
            ->setObjectId($this->getObjectId());

        $message = \isys_application::instance()->container->get('language')
            ->get('LC__MODULE__MULTIEDIT__OBJECT_HAS_NO_DATA_IN_CATEGORY');
        $iconSrc = $g_dirs['images'] . '/icons/silk/information.png';

        $content = "<tr id='object-row_{$this->getObjectId()}' class='multiedit-tr-data' data-entry='{$this->getObjectId()}'>";
        $content .= ActionFormatter::formatCell($valueFormater);
        $content .= ObjectInfoFormatter::formatCell((new ValueFormatter())->setValue($this->getObjectData()));

        $newData = [];
        $newViewData = [];
        if ($data) {
            if (isset($uiParams[\isys_popup_browser_object_ng::C__MULTISELECTION]) && $uiParams[\isys_popup_browser_object_ng::C__MULTISELECTION]) {
                foreach ($data as $entryId => $categoryData) {
                    $dataValueObject = current($categoryData);
                    if (\isys_format_json::is_json_array($dataValueObject->getValue())) {
                        $dataValue = \isys_format_json::decode($dataValueObject->getValue());
                        $dataViewValue = explode(',', $dataValueObject->getViewValue());
                        $newData = array_merge($newData, $dataValue);
                        $newViewData = array_merge($newViewData, $dataViewValue);
                    } else {
                        $newData[] = $dataValueObject->getValue();
                        $newViewData[] = $dataValueObject->getViewValue();
                    }
                }
                $newData = array_unique($newData);
                $newViewData = array_unique($newViewData);
                $newData = (new Value)
                    ->setValue(\isys_format_json::encode($newData))
                    ->setViewValue(implode(',', $newViewData));
            } else {
                $dataValue = current($data);
                $newData = $dataValue[$propertyKey];
            }
        }

        foreach ($properties as $propertyKey => $property) {
            $formatter = FormatterManager::getFormatterByProperty($propertyKey);

            if ($formatter === false) {
                $formatter = FormatterManager::getFormatterByUiType($property->getUi()
                    ->getType());
            }
            $callbackProperty = null;

            if (empty($newData)) {
                $newData = new Value;
            }

            $valueFormater->setPropertyKey($propertyKey)
                ->setValue($newData)
                ->setProperty($property);

            $content .= $formatter::formatCell($valueFormater);
        }

        return $content . '</tr>';
    }
}

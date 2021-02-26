<?php

namespace idoit\Component\Property;

use idoit\Component\Property\Configuration\PropertyCheck;
use idoit\Component\Property\Configuration\PropertyData;
use idoit\Component\Property\Configuration\PropertyDependency;
use idoit\Component\Property\Configuration\PropertyFormat;
use idoit\Component\Property\Configuration\PropertyInfo;
use idoit\Component\Property\Configuration\PropertyProvides;
use idoit\Component\Property\Configuration\PropertyUi;
use ReflectionClass;

class Property implements \ArrayAccess, LegacyPropertyInterface, LegacyPropertyCreatorInterface
{
    const MAPPING = [
        Property::C__PROPERTY__FORMAT => [
            'property' => 'format',
            'type' => PropertyFormat::class
        ],
        Property::C__PROPERTY__INFO => [
            'property' => 'info',
            'type' => PropertyInfo::class
        ],
        Property::C__PROPERTY__DATA => [
            'property' => 'data',
            'type' => PropertyData::class
        ],
        Property::C__PROPERTY__CHECK => [
            'property' => 'check',
            'type' => PropertyCheck::class
        ],
        Property::C__PROPERTY__UI => [
            'property' => 'ui',
            'type' => PropertyUi::class
        ],
        Property::C__PROPERTY__PROVIDES => [
            'property' => 'provides',
            'type' => PropertyProvides::class
        ],
        Property::C__PROPERTY__DEPENDENCY => [
            'property' => 'dependency',
            'type' => PropertyDependency::class
        ]
    ];

    /**
     * @var PropertyFormat
     */
    protected $format;

    /**
     * @var PropertyInfo
     */
    protected $info;

    /**
     * @var PropertyData
     */
    protected $data;

    /**
     * @var PropertyCheck
     */
    protected $check;

    /**
     * @var PropertyUi
     */
    protected $ui;

    /**
     * @var PropertyProvides
     */
    protected $provides;

    /**
     * @var PropertyDependency
     */
    protected $dependency;

    /**
     * Returns an instance of the class which implements this interface, build by given $propertyArray
     *
     * @param array $propertyArray
     *
     * @return Property
     */
    public static function createInstanceFromArray(array $propertyArray = [])
    {
        $property = new static();
        return $property->mapAttributes($propertyArray);
    }

    /**
     * Maps the property
     *
     * @param array $propertyArray
     *
     * @return Property
     * @throws Exception\UnknownTypeException
     * @throws Exception\UnsupportedConfigurationTypeException
     */
    public function mapAttributes(array $propertyArray)
    {
        $this->info = PropertyInfo::createInstanceFromArray($propertyArray[self::C__PROPERTY__INFO] ?: []);
        $this->data = PropertyData::createInstanceFromArray($propertyArray[self::C__PROPERTY__DATA] ?: []);
        $this->check = PropertyCheck::createInstanceFromArray($propertyArray[self::C__PROPERTY__CHECK] ?: []);
        $this->format = PropertyFormat::createInstanceFromArray($propertyArray[self::C__PROPERTY__FORMAT] ?: []);
        $this->ui = PropertyUi::createInstanceFromArray($propertyArray[self::C__PROPERTY__UI] ?: []);
        $this->provides = PropertyProvides::createInstanceFromArray($propertyArray[self::C__PROPERTY__PROVIDES] ?: []);
        $this->dependency = PropertyDependency::createInstanceFromArray($propertyArray[self::C__PROPERTY__DEPENDENCY] ?: []);
        return $this;
    }

    /**
     * @return PropertyFormat
     */
    public function &getFormat()
    {
        return $this->format;
    }

    /**
     * Set info regarding of given data
     *
     * @param array|object|null  $data
     * @param string $propertyClass
     *
     * @return mixed
     */
    private function _set_info($data, string $propertyClass)
    {
        global $g_dirs;

        if (is_array($data) && method_exists($propertyClass, 'createInstanceFromArray')) {
            // Transform array to its corresponding property class
            return $propertyClass::createInstanceFromArray($data);
        } elseif (is_object($data) && is_a($data, $propertyClass)) {
            // Return object because class is matching
            return $data;
        } else {
            // We can not handle this!

            return null;
        }
    }

    /**
     * @param PropertyFormat $format
     *
     * @return Property
     */
    public function setFormat($format)
    {
        $this->format = $this->_set_info($format, PropertyFormat::class);

        return $this;
    }

    /**
     * @return PropertyInfo
     */
    public function &getInfo()
    {
        return $this->info;
    }

    /**
     * @param PropertyInfo $info
     *
     * @return Property
     */
    public function setInfo($info)
    {
        $this->info = $this->_set_info($info, PropertyInfo::class);

        return $this;
    }

    /**
     * @return PropertyData
     */
    public function &getData()
    {
        return $this->data;
    }

    /**
     * @param PropertyData $data
     *
     * @return Property
     */
    public function setData($data)
    {
        $this->data = $this->_set_info($data, PropertyData::class);

        return $this;
    }

    /**
     * @return PropertyCheck
     */
    public function &getCheck()
    {
        return $this->check;
    }

    /**
     * @param PropertyCheck $check
     *
     * @return Property
     */
    public function setCheck($check)
    {
        $this->check = $this->_set_info($check, PropertyCheck::class);

        return $this;
    }

    /**
     * @return PropertyUi
     */
    public function &getUi()
    {
        return $this->ui;
    }

    /**
     * @param PropertyUi $ui
     *
     * @return Property
     */
    public function setUi($ui)
    {
        $this->ui = $this->_set_info($ui, PropertyUi::class);

        return $this;
    }

    /**
     * @return PropertyProvides
     */
    public function &getProvides()
    {
        return $this->provides;
    }

    /**
     * @param PropertyProvides $provides
     *
     * @return Property
     */
    public function setProvides($provides)
    {
        $this->provides = $this->_set_info($provides, PropertyProvides::class);

        return $this;
    }

    /**
     * @return PropertyDependency
     */
    public function &getDependency()
    {
        return $this->dependency;
    }

    /**
     * @param PropertyDependency $dependency
     *
     * @return $this
     */
    public function setDependency($dependency)
    {
        $this->dependency = $this->_set_info($dependency, PropertyDependency::class);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function offsetExists($offset)
    {
        if (property_exists($this, $offset)) {
            return (is_a($this->{static::MAPPING[$offset]['property']}, static::MAPPING[$offset]['type']));
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    public function offsetGet($offset)
    {
        return (property_exists($this, static::MAPPING[$offset]['property']) ? $this->{static::MAPPING[$offset]['property']} : null);
    }

    /**
     * @inheritdoc
     */
    public function offsetSet($offset, $value)
    {
        if (is_object($value) && $value instanceof $this->{static::MAPPING[$offset]['type']}) {
            $this->{static::MAPPING[$offset]['property']} = $value;
        } else {
            $this->{static::MAPPING[$offset]['property']} = call_user_func([
                static::MAPPING[$offset]['type'],
                'createInstanceFromArray'
            ], $value);
        }
    }

    /**
     * @inheritdoc
     */
    public function offsetUnset($offset)
    {
        if (property_exists($this, static::MAPPING[$offset]['property'])) {
            unset($this->{static::MAPPING[$offset]['property']});
        }
    }

    /**
     * Set Info with specified key value
     *
     * @param $key
     * @param $value
     *
     * @return $this
     */
    public function setPropertyInfoOffset($key, $value)
    {
        $this->getInfo()->offsetSet($key, $value);
        return $this;
    }

    /**
     * Gets specific Info Attribute
     *
     * @param $key
     *
     * @return bool|mixed|string
     */
    public function getPropertyInfoOffset($key)
    {
        return $this->getInfo()->offsetGet($key);
    }

    /**
     * Set Data with specified key value
     *
     * @param $key
     * @param $value
     *
     * @return $this
     */
    public function setPropertyDataOffset($key, $value)
    {
        $this->getData()->offsetSet($key, $value);
        return $this;
    }

    /**
     * Gets specific Data Attribute
     *
     * @param $key
     *
     * @return bool|SelectSubSelect|mixed|string
     */
    public function getPropertyDataOffset($key)
    {
        return $this->getData()->offsetGet($key);
    }

    /**
     * Set Ui with specified key value
     *
     * @param $key
     * @param $value
     *
     * @return $this
     */
    public function setPropertyUiOffset($key, $value)
    {
        $this->getUi()->offsetSet($key, $value);
        return $this;
    }

    /**
     * Gets specific Ui Attribute
     *
     * @param $key
     *
     * @return mixed|string
     */
    public function getPropertyUiOffset($key)
    {
        return $this->getUi()->offsetGet($key);
    }

    /**
     * Set Provides with specified key value
     *
     * @param $key
     * @param $value
     *
     * @return $this
     */
    public function setPropertyProvidesOffset($key, $value)
    {
        $this->getProvides()->offsetSet($key, $value);
        return $this;
    }

    /**
     * Gets specific Provides Attribute
     *
     * @param $key
     *
     * @return bool|mixed
     */
    public function getPropertyProvidesOffset($key)
    {
        return $this->getProvides()->offsetGet($key);
    }

    /**
     * Set Format with specified key value
     *
     * @param $key
     * @param $value
     *
     * @return $this
     */
    public function setPropertyFormatOffset($key, $value)
    {
        $this->getFormat()->offsetSet($key, $value);
        return $this;
    }

    /**
     * Gets specific Format Attribute
     *
     * @param $key
     *
     * @return array|mixed|string
     */
    public function getPropertyFormatOffset($key)
    {
        return $this->getFormat()->offsetGet($key);
    }

    /**
     * Set Check with specified key value
     *
     * @param $key
     * @param $value
     *
     * @return $this
     */
    public function setPropertyCheckOffset($key, $value)
    {
        $this->getCheck()->offsetSet($key, $value);
        return $this;
    }

    /**
     * Gets specific Check Attribute
     *
     * @param $key
     *
     * @return array|mixed
     */
    public function getPropertyCheckOffset($key)
    {
        return $this->getCheck()->offsetGet($key);
    }

    /**
     * Set Dependency with specified key value
     *
     * @param string $key
     * @param mixed  $value
     *
     * @return $this
     */
    public function setPropertyDependencyOffset($key, $value)
    {
        $this->getDependency()->offsetSet($key, $value);
        return $this;
    }

    /**
     * Gets specific Dependency Attribute;
     *
     * @param $key
     *
     * @return \idoit\Module\Report\SqlQuery\Structure\SelectSubSelect|mixed|string
     */
    public function getPropertyDependencyOffset($key)
    {
        return $this->getDependency()->offsetGet($key);
    }

    /**
     * Maps the "info" data
     *
     * @param array $params
     *
     * @return $this
     */
    public function setPropertyInfo(array $params)
    {
        $this->getInfo()->mapAttributes($params);
        return $this;
    }

    /**
     * Maps the "ui" data
     *
     * @param array $params
     *
     * @return $this
     */
    public function setPropertyUi(array $params)
    {
        $this->getUi()->mapAttributes($params);
        return $this;
    }

    /**
     * Maps the "check" data
     *
     * @param array $params
     *
     * @return $this
     */
    public function setPropertyCheck(array $params)
    {
        $this->getCheck()->mapAttributes($params);
        return $this;
    }

    /**
     * Maps the "data" data
     *
     * @param array $params
     *
     * @return $this
     */
    public function setPropertyData(array $params)
    {
        $this->getData()->mapAttributes($params);
        return $this;
    }

    /**
     * Maps the "dependency" data
     *
     * @param array $params
     *
     * @return $this
     */
    public function setPropertyDependency(array $params)
    {
        $this->getDependency()->mapAttributes($params);
        return $this;
    }

    /**
     * Maps the "format" data
     *
     * @param array $params
     *
     * @return $this
     */
    public function setPropertyFormat(array $params)
    {
        $this->getFormat()->mapAttributes($params);
        return $this;
    }

    /**
     * Maps the "provides" data
     *
     * @param array $params
     *
     * @return $this
     */
    public function setPropertyProvides(array $params)
    {
        $this->getProvides()->mapAttributes($params);
        return $this;
    }

    /**
     * @param array $formatParams
     *
     * @return $this
     */
    public function mergePropertyFormat(array $formatParams)
    {
        foreach ($formatParams as $dataKey => $dataValue) {
            $this->getFormat()->offsetSet($dataKey, $dataValue);
        }
        return $this;
    }

    /**
     * @param array $dataParams
     *
     * @return $this
     */
    public function mergePropertyData(array $dataParams)
    {
        foreach ($dataParams as $dataKey => $dataValue) {
            $this->getData()->offsetSet($dataKey, $dataValue);
        }
        return $this;
    }

    /**
     * @param array $providesParams
     *
     * @return $this
     */
    public function mergePropertyProvides(array $providesParams)
    {
        foreach ($providesParams as $bit => $isProvided) {
            $this->getProvides()->offsetSet($bit, $isProvided);
        }
        return $this;
    }

    /**
     * @param array $uiParams
     *
     * @return $this
     */
    public function mergePropertyUi(array $uiParams)
    {
        foreach ($uiParams as $key => $value) {
            $this->getUi()->offsetSet($key, $value);
        }
        return $this;
    }

    /**
     * Convenience method to set the UI Params
     *
     * @param array $uiParams
     *
     * @return $this
     */
    public function mergePropertyUiParams(array $uiParams)
    {
        $existingUiParams = $this->getUi()->getParams() ?: [];
        foreach ($uiParams as $uiKey => $uiValue) {
            $existingUiParams[$uiKey] = $uiValue;
        }
        $this->getUi()->setParams($existingUiParams);
        return $this;
    }

    /**
     * Merges the "check" data.
     *
     * @param array $checkParams
     *
     * @return $this
     */
    public function mergePropertyCheck(array $checkParams)
    {
        foreach ($checkParams as $checkKey => $checkValue) {
            $this->getCheck()->offsetSet($checkKey, $checkValue);
        }

        return $this;
    }

    /**
     * @param array $dependencyParams
     *
     * @return $this
     */
    public function mergePropertyDependency(array $dependencyParams)
    {
        foreach ($dependencyParams as $checkKey => $checkValue) {
            $this->getDependency()->offsetSet($checkKey, $checkValue);
        }
        return $this;
    }

    /**
     * Sets p_arData as isys_callback
     *
     * @param array $uiDataArray
     *
     * @return $this
     * @throws \Exception
     */
    public function setPropertyUiDataAsCallback(array $uiDataArray)
    {
        $existingUiParams = $this->getUi()->getParams() ?: [];
        $existingUiParams['p_arData'] = new \isys_callback($uiDataArray);
        $this->getUi()->setParams($existingUiParams);
        return $this;
    }

    /**
     * Sets UI default value
     *
     * @param mixed $default
     *
     * @return $this
     */
    public function setPropertyUiDefault($default)
    {
        $this->getUi()->setDefault($default);
        return $this;
    }

    /**
     * Sets p_arData as Array
     *
     * @param array $uiDataArray
     *
     * @return $this
     */
    public function setPropertyUiDataAsArray(array $uiDataArray)
    {
        $existingUiParams = $this->getUi()->getParams() ?: [];
        $existingUiParams['p_arData'] = $uiDataArray;
        $this->getUi()->setParams($existingUiParams);
        return $this;
    }

    /**
     * @param int|\isys_callback $relationType
     *
     * @return $this
     */
    public function setPropertyDataRelationType($relationType)
    {
        $this->getData()->setRelationType($relationType);
        return $this;
    }

    /**
     * @param \isys_callback $relationHandler
     *
     * @return $this
     */
    public function setPropertyDataRelationHandler(\isys_callback $relationHandler)
    {
        $this->getData()->setRelationHandler($relationHandler);
        return $this;
    }

    /**
     * Sets object browser specific ui param
     *
     * @param $key
     * @param $value
     */
    private function setObjectBrowserParams($key, $value)
    {
        $params = $this->getUi()->getParams();
        $params[$key] = $value;
        $this->getUi()->setParams($params);
    }

    /**
     * Activate multiselection for object browser property
     *
     * @return $this
     */
    public function activateMultiselection()
    {
        $this->setObjectBrowserParams(\isys_popup_browser_object_ng::C__MULTISELECTION, true);
        return $this;
    }

    /**
     * Activate SecondSelection for object browser property
     *
     * @return $this
     */
    public function activateSecondSelection()
    {
        $this->setObjectBrowserParams(\isys_popup_browser_object_ng::C__SECOND_SELECTION, true);
        return $this;
    }

    /**
     * Activate Form Submit for object browser property
     *
     * @return $this
     */
    public function activateFormSubmit()
    {
        $this->setObjectBrowserParams(\isys_popup_browser_object_ng::C__FORM_SUBMIT, true);
        return $this;
    }

    /**
     * Sets the return element for object browser property
     * Default: C__POST__POPUP_RECEIVER
     *
     * @param string $returnElement
     *
     * @return $this
     */
    public function setReturnElement($returnElement = '')
    {
        $this->setObjectBrowserParams(\isys_popup_browser_object_ng::C__RETURN_ELEMENT, ($returnElement ?: defined_or_default('C__POST__POPUP_RECEIVER')));
        return $this;
    }

    /**
     * Sets the dataretrieval for object browser property
     * Array Example;
     * [
     *      [
     *          'isys_cmdb_dao_category_s_database_access', 'get_data_by_object'
     *      ],
     *      $_GET[C__CMDB__GET__OBJECT],
     *      [
     *          "isys_connection__id",
     *          "assignment_title",
     *          "assignment_type",
     *          "assignment_sysid"
     *      ]
     * ]
     *
     * @param array $dataRetrieval
     *
     * @return $this
     */
    public function setDataRetrieval(array $dataRetrieval)
    {
        $this->setObjectBrowserParams(\isys_popup_browser_object_ng::C__DATARETRIEVAL, $dataRetrieval);
        return $this;
    }

    /**
     * Sets category filter for the object browser
     *
     * @param string $categoryFilter
     *
     * @return $this
     */
    public function setCategoryFilter($categoryFilter)
    {
        $this->setObjectBrowserParams(\isys_popup_browser_object_ng::C__CAT_FILTER, $categoryFilter);
        return $this;
    }

    /**
     * Sets the Second List for object browser property
     * Array Example:
     *  [
     *      'isys_cmdb_dao_category_s_database_access::object_browser',
     *      [
     *          'typefilter' => defined_or_default('C__RELATION_TYPE__SOFTWARE')
     *      ]
     *   ]
     *
     * @param array $secondList
     *
     * @return $this
     */
    public function setSecondList(array $secondList)
    {
        $this->setObjectBrowserParams(\isys_popup_browser_object_ng::C__SECOND_LIST, $secondList);
        return $this;
    }

    /**
     * Property constructor.
     */
    public function __construct()
    {
        $this->info = new PropertyInfo();
        $this->data = new PropertyData();
        $this->ui = new PropertyUi();
        $this->provides = new PropertyProvides();
        $this->check = new PropertyCheck();
        $this->dependency = new PropertyDependency();
        $this->format = new PropertyFormat();
    }

    /**
     * @return array
     * @throws \ReflectionException
     */
    public function toArray(): array
    {
        $return = [];
        $properties = (new ReflectionClass($this))->getProperties();

        foreach ($properties as $property) {
            $propertyName = $property->getName();

            if (!isset($this->$propertyName)) {
                continue;
            }

            if (\is_array($this->$propertyName) && empty($this->$propertyName)) {
                continue;
            }

            if (\is_object($this->$propertyName) && method_exists($this->$propertyName, 'toArray')) {
                $return[$propertyName] = $this->$propertyName->toArray();
                continue;
            }

            $return[$propertyName] = $this->$propertyName;
        }

        return $return;
    }
}

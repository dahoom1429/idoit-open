<?php

namespace idoit\Module\Multiedit\Component\Synchronizer;

use idoit\Component\Property\Property;
use idoit\Exception\Exception;
use idoit\Module\Multiedit\Component\Synchronizer\Converter\ConvertInterface;
use idoit\Module\Multiedit\Component\Synchronizer\Converter\Standard\DialogList;
use idoit\Module\Multiedit\Component\Synchronizer\Converter\Standard\Multiselect;
use idoit\Module\Multiedit\Component\Synchronizer\Converter\Standard\ObjectBrowser;

abstract class AbstractSynchronizer
{
    const ENTRY__DATA__ID   = 'data_id';
    const ENTRY__PROPERTIES = 'properties';

    /**
     * @var int
     */
    protected $objectId;

    /**
     * @var int|string
     */
    protected $entryId;

    /**
     * @var string
     */
    protected $entryKey;

    /**
     * @var array
     */
    protected $entryData;

    /**
     * @var array
     */
    protected $entryChanges;

    /**
     * @var bool
     */
    protected $synchronizeSuccess = false;

    /**
     * @var array
     */
    protected $validationErrors = [];

    /**
     * @var Merger
     */
    protected $merger;

    /**
     * @var \isys_cmdb_dao_category
     */
    protected $categoryDao;

    /**
     * @var array
     */
    protected $syncData = [];

    /**
     * @var ConvertInterface[]
     */
    protected $valueConverters = [];

    /**
     * @return mixed
     */
    public function getEntryChanges()
    {
        return $this->entryChanges;
    }

    /**
     * @param mixed $entryChanges
     *
     * @return AbstractSynchronizer
     */
    public function setEntryChanges($entryChanges)
    {
        $this->entryChanges = $entryChanges;

        return $this;
    }

    /**
     * @return array
     */
    public function getValidationErrors()
    {
        return $this->validationErrors;
    }

    /**
     * @return bool
     */
    public function isSynchronizeSuccess()
    {
        return $this->synchronizeSuccess;
    }

    /**
     * @param \isys_cmdb_dao_category $dao
     *
     * @return AbstractSynchronizer
     */
    public function setCategoryDao($dao)
    {
        $this->categoryDao = $dao;

        return $this;
    }

    /**
     * @param Merger $merger
     *
     * @return AbstractSynchronizer
     */
    public function setMerger($merger)
    {
        $this->merger = $merger;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getSyncData()
    {
        return $this->syncData;
    }

    /**
     * @param mixed $syncData
     *
     * @return AbstractSynchronizer
     */
    public function setSyncData($syncData)
    {
        $this->syncData = $syncData;

        return $this;
    }

    /**
     * @return int
     */
    public function getObjectId()
    {
        return $this->objectId;
    }

    /**
     * @param int $objectId
     *
     * @return AbstractSynchronizer
     */
    public function setObjectId($objectId)
    {
        $this->objectId = $objectId;

        return $this;
    }

    /**
     * @return int|string
     */
    public function getEntryId()
    {
        return $this->entryId;
    }

    /**
     * @param int|string $entryId
     *
     * @return AbstractSynchronizer
     */
    public function setEntryId($entryId)
    {
        $this->entryId = $entryId;

        return $this;
    }

    /**
     * @return string
     */
    public function getEntryKey()
    {
        return $this->entryKey;
    }

    /**
     * @param string $entryKey
     *
     * @return AbstractSynchronizer
     */
    public function setEntryKey($entryKey)
    {
        $this->entryKey = $entryKey;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getEntryData()
    {
        return $this->entryData;
    }

    /**
     * @param $entryData
     *
     * @return AbstractSynchronizer
     */
    public function setEntryData($entryData)
    {
        $this->entryData = $entryData;

        return $this;
    }

    /**
     * @return AbstractSynchronizer
     */
    public function reset()
    {
        $this->syncData = [];

        return $this;
    }

    /**
     * Validate sync data and if option 'import.validation.break-on-error' is set to false
     * then remove the value from the sync data on validation error
     *
     * @throws \Exception
     */
    public function validateSyncData()
    {
        $objectId = $this->getObjectId();
        $entryId = $this->getEntryId() === 'new' ? null : $this->getEntryId();

        $this->categoryDao->set_object_id($objectId);
        $this->categoryDao->set_list_id($entryId);

        $validationErrors = $this->categoryDao->validate($this->syncData[self::ENTRY__PROPERTIES]);
        $className = get_class($this->categoryDao);

        if (is_array($validationErrors)) {
            // Iterate through each validation error
            if ($entryId) {
                $row = $this->categoryDao->get_data($entryId)->get_row();
            } else {
                $row = null;
            }
            foreach ($validationErrors as $propertyKey => $validationMessage) {
                $property = $this->categoryDao->get_property_by_key($propertyKey);
                $attribute = \isys_application::instance()->container->get('language')
                    ->get($property[C__PROPERTY__INFO][C__PROPERTY__INFO__TITLE]);

                $this->validationErrors[] = [
                    'objId'       => $this->getObjectId(),
                    'value'        => $this->syncData[Synchronizer::ENTRY__PROPERTIES][$propertyKey][C__DATA__VALUE],
                    'propertyUiId'   => $className . '__' . $propertyKey . '[' . $this->getObjectId() . '-' . $this->getEntryId() . ']',
                    'message'      => $validationMessage,
                    'catEntryId' => ($this->entryId === 'new' ? null : $this->entryId)
                ];

                if ($entryId) {
                    $dataOldValue = $this->categoryDao->get_gui_value_for_property($property, $row);
                } else {
                    $dataOldValue = null;
                }

                // Empty value so that the dataset can be synced without any errors
                $this->syncData[self::ENTRY__PROPERTIES][$propertyKey][C__DATA__VALUE] = $dataOldValue;

                // Unset changes for the validation error
                unset($this->entryChanges[$className . '::' . $propertyKey]);
            }

            // Throw exception only if the setting break on valdiation error is active
            if ((bool)\isys_tenantsettings::get('import.validation.break-on-error', true) === true) {
                throw new \isys_exception_validation(\isys_application::instance()->container->get('language')
                    ->get('LC__VALIDATION_ERROR'), $validationErrors, ($this->entryId === 'new' ? null : $this->entryId));
            }
        }
        return true;
    }

    /**
     * @return $this
     */
    public function setConverter()
    {
        $category = ucfirst($this->categoryDao->get_category());
        $properties = $this->categoryDao->get_properties();

        switch ($this->categoryDao->get_category_type()) {
            case defined_or_default('C__CMDB__CATEGORY__TYPE_GLOBAL'):
                $categoryType = 'G';
                break;
            case defined_or_default('C__CMDB__CATEGORY__TYPE_SPECIFIC'):
                $categoryType = 'S';
                break;
            case defined_or_default('C__CMDB__CATEGORY__TYPE_CUSTOM'):
                $categoryType = 'Custom';
                $category = str_replace(' ', '', ucwords(str_replace('_', ' ', $category)));
                break;
            default:
                $categoryType = null;
                break;
        }

        if ($categoryType === null) {
            return $this;
        }

        foreach ($properties as $propKey => $property) {
            try {
                $propertyObj = ($property instanceof Property) ? $property : Property::createInstanceFromArray($property);
            } catch (\Exception $e) {
                continue;
            }

            if ($categoryType === 'Custom') {
                $className = ucfirst(substr($propKey, 2, strpos($propKey, '_c_') - 2));
            } else {
                $className = str_replace(' ', '', ucwords(str_replace('_', ' ', $propKey)));
            }
            $class = 'idoit\\Module\\Multiedit\\Component\\Synchronizer\\Converter\\' . $categoryType . '\\' . $category . '\\' . $className;

            if (class_exists($class)) {
                $this->valueConverters[$propKey] = new $class;
                continue;
            }

            $this->setDefaultConverter($propKey, $propertyObj->getInfo()->getType());
        }

        return $this;
    }

    /**
     * @param $propKey
     * @param $propertyInfoType
     */
    private function setDefaultConverter($propKey, $propertyInfoType)
    {
        switch ($propertyInfoType) {
            case C__PROPERTY__INFO__TYPE__OBJECT_BROWSER:
                $this->valueConverters[$propKey] = new ObjectBrowser();
                break;
            case C__PROPERTY__INFO__TYPE__MULTISELECT:
                $this->valueConverters[$propKey] = new Multiselect();
                break;
            case C__PROPERTY__INFO__TYPE__DIALOG_LIST:
                $this->valueConverters[$propKey] = new DialogList();
                break;
        }
    }
}

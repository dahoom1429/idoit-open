<?php
namespace idoit\Module\Multiedit\Component\Multiedit\Config;

use idoit\Component\Property\Property;
use idoit\Module\Multiedit\Component\Multiedit\Source\DataSource;
use idoit\Module\Multiedit\Component\Multiedit\Source\Source;
use idoit\Module\Multiedit\Component\Multiedit\Source\PropertiesSource;
use idoit\Module\Multiedit\Component\Multiedit\Source\FilterSource;
use idoit\Module\Multiedit\Component\Multiedit\Type\Type;
use idoit\Module\Cmdb\Interfaces\ObjectBrowserReceiver;
use isys_cmdb_dao_category_g_custom_fields;

class Config
{

    /**
     * Raw data
     *
     * @var
     */
    protected $data;

    /**
     * @var
     */
    protected $objects;

    /**
     * @var
     */
    protected $listType;

    /**
     * @var PropertiesSource
     */
    protected $propertySource;

    /**
     * @var DataSource
     */
    protected $dataSource;

    /**
     * @var FilterSource
     */
    protected $filterSource;

    /**
     * @var
     */
    protected $categoryDao;

    /**
     * @var Type
     */
    protected $type;

    /**
     * @return Type
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param Type $type
     *
     * @return Config
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCategoryDao()
    {
        return $this->categoryDao;
    }

    /**
     * @param mixed $categoryDao
     *
     * @return Config
     */
    public function setCategoryDao($categoryDao)
    {
        $this->categoryDao = $categoryDao;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param mixed $data
     *
     * @return Config
     */
    public function setData($data)
    {
        $this->data = $data;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getObjects()
    {
        return $this->objects;
    }

    /**
     * @param mixed $objects
     *
     * @return Config
     */
    public function setObjects($objects)
    {
        $dao = \isys_cmdb_dao::instance(\isys_application::instance()->container->get('database'));
        $result = $dao->get_object($objects);
        $return = [];
        while ($row = $result->get_row()) {
            $return[$row['isys_obj__id']] = [
                'id' => $row['isys_obj__id'],
                'title' => $row['isys_obj__title'],
                'typeId' => $row['isys_obj__isys_obj_type__id'],
                'typeTitle' => $row['isys_obj_type__title'],
                'sysId' => $row['isys_obj__sysid']
            ];
        }

        $this->objects = $return;
        return $this;
    }

    /**
     * @return Source
     */
    public function getPropertySource()
    {
        return $this->propertySource;
    }

    /**
     * @param Source $propertySource
     *
     * @return Config
     */
    public function setPropertySource($propertySource)
    {
        $this->propertySource = $propertySource;
        return $this;
    }

    /**
     * @return Source
     */
    public function getDataSource()
    {
        return $this->dataSource;
    }

    /**
     * @param Source $dataSource
     *
     * @return Config
     */
    public function setDataSource($dataSource)
    {
        $this->dataSource = $dataSource;
        return $this;
    }

    /**
     * @return FilterSource
     */
    public function getFilterSource()
    {
        return $this->filterSource;
    }

    /**
     * @param FilterSource $filterSource
     *
     * @return Config
     */
    public function setFilterSource($filterSource)
    {
        $this->filterSource = $filterSource;
        return $this;
    }

    /**
     * Checks if the list is an assignment type
     *
     * @param \isys_cmdb_dao_category $categoryDao
     * @param PropertiesSource        $propertiesSource
     */
    public static function isAssignmentType(\isys_cmdb_dao_category $categoryDao, PropertiesSource $propertiesSource)
    {
        if ($categoryDao instanceof isys_cmdb_dao_category_g_custom_fields) {
            return false;
        }

        if ($propertiesSource->count() === 1) {
            if ($categoryDao instanceof ObjectBrowserReceiver) {
                return true;
            }

            /**
             * @var $property Property
             */
            $property = current(current($propertiesSource->getData()));
            if ($categoryDao->is_multivalued() === false && $property->getInfo()->getType() === C__PROPERTY__INFO__TYPE__OBJECT_BROWSER) {
                return true;
            }
        }

        return false;
    }
}

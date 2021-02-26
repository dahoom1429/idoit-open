<?php

namespace idoit\Component\Browser;

use idoit\Component\Settings\Tenant;
use idoit\Module\Cmdb\Component\Browser\Filter\AuthFilter;
use isys_application as App;
use isys_cmdb_dao_category as daoCategory;
use isys_cmdb_dao_category_g_custom_fields as daoCategoryCustom;
use isys_cmdb_dao_category_property_ng as daoProperty;
use isys_tenantsettings;

class Retriever
{
    /**
     * @var ConditionInterface
     */
    private $condition;

    /**
     * @var boolean
     */
    private $showCategoryNames = false;

    /**
     * @var integer
     */
    private $contextObjectId;

    /**
     * @var array
     */
    private $attributes = [
        'C__CATG__GLOBAL::title',
        'C__CATG__GLOBAL::type',
        'C__CATG__GLOBAL::sysid'
    ];

    /**
     * @param ConditionInterface $condition
     *
     * @return $this
     */
    public function setCondition(ConditionInterface $condition)
    {
        $this->condition = $condition;

        return $this;
    }

    /**
     * @param array $attributes
     *
     * @return $this
     */
    public function setAttributes(array $attributes)
    {
        $this->attributes = $attributes;

        return $this;
    }

    /**
     * @param integer $contextObjectId
     *
     * @return $this
     */
    public function setContextObjectId($contextObjectId)
    {
        $this->contextObjectId = (int)$contextObjectId;

        return $this;
    }

    /**
     * @param  boolean $showCategoryNames
     *
     * @return Retriever
     */
    public function showCategoryNames($showCategoryNames)
    {
        $this->showCategoryNames = (bool)$showCategoryNames;

        return $this;
    }

    /**
     * Retrieve the attributes.
     *
     * @return array
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * @return array
     */
    public function getOverviewData()
    {
        if ($this->contextObjectId !== null) {
            $this->condition->setContextObjectId($this->contextObjectId);
        }

        // @see  ID-#  Apply the AuthFilter to only display allowed objects.
        if (isys_tenantsettings::get('auth.use-in-object-browser', false)) {
            $this->condition->registerFilter(new AuthFilter(App::instance()->container->get('database')));
        }

        return $this->condition->retrieveOverview();
    }

    /**
     * @return array
     */
    public function getObjects()
    {
        if ($this->contextObjectId !== null) {
            $this->condition->setContextObjectId($this->contextObjectId);
        }

        // @see  ID-#  Apply the AuthFilter to only display allowed objects.
        if (isys_tenantsettings::get('auth.use-in-object-browser', false)) {
            $this->condition->registerFilter(new AuthFilter(App::instance()->container->get('database')));
        }

        // Retrieve objects by condition and process the visitors.
        return $this->condition->processVisitors($this->condition->retrieveObjects());
    }

    /**
     * Method to return formatted object data by defined attributes and condition.
     *
     * @param bool  $returnAsArray
     * @param array $objectIds
     *
     * @return \isys_component_dao_result|array
     * @throws \isys_exception_database
     * @throws \idoit\Exception\JsonException
     */
    public function getObjectData($returnAsArray = true, array $objectIds = [])
    {
        $db = App::instance()->container->get('database');
        $lang = App::instance()->container->get('language');

        $propertyDao = new daoProperty($db);
        $attributes = [];

        foreach ($this->attributes as $attribute) {
            list($categoryConstant, $propertyTitle) = explode('::', $attribute);

            $sql = 'SELECT isys_property_2_cat__id 
                FROM isys_property_2_cat
                WHERE isys_property_2_cat__cat_const = ' . $propertyDao->convert_sql_text($categoryConstant) . '
                AND isys_property_2_cat__prop_key = ' . $propertyDao->convert_sql_text($propertyTitle) . '
                LIMIT 1;';

            $propertyId = $propertyDao->retrieve($sql)
                ->get_row_value('isys_property_2_cat__id');

            if (is_numeric($propertyId)) {
                $attributes[] = (int)$propertyId;
            }
        }

        if (count($objectIds)) {
            $objects = $objectIds;
        } else {
            $objects = $this->getObjects();
        }

        $sql = $propertyDao->create_property_query_for_lists($attributes, null, $objects, [], true, true);

        if ($this->condition->retainObjectOrder() && count($objects)) {
            $sql .= ' ORDER BY FIELD(obj_main.isys_obj__id, ' . implode(',', $objects) . ');';
        }

        if (!$returnAsArray) {
            return $propertyDao->retrieve($sql);
        }

        $return = ['data' => []];
        $result = $propertyDao->retrieve($sql);

        while ($row = $result->get_row()) {
            if (!isset($return['header'])) {
                $return['header'] = array_map(function ($value) use ($db, $lang, $propertyDao) {
                    if ($value === '__id__') {
                        return '__checkbox__';
                    }

                    list($daoClass, $attributeKey) = explode('__', $value);

                    if (class_exists($daoClass) && is_a($daoClass, daoCategory::class, true)) {
                        $daoInstance = $daoClass::instance($db);

                        if ($daoInstance instanceof daoCategoryCustom) {
                            // Get the custom category ID by the provided field key.
                            preg_match('~c_\d+$~', $attributeKey, $match);

                            if (isset($match[0]) && mb_strlen($match[0])) {
                                $sql = 'SELECT isys_catg_custom_fields_list__isysgui_catg_custom__id AS customCategoryId 
                                    FROM isys_catg_custom_fields_list
                                    WHERE isys_catg_custom_fields_list__field_key = ' . $propertyDao->convert_sql_text($match[0]);

                                $daoInstance->set_catg_custom_id($propertyDao->retrieve($sql)
                                    ->get_row_value('customCategoryId'));
                            }
                        }

                        $property = $daoInstance->get_property_by_key($attributeKey);

                        // @see  ID-6426  Decode the HTML "&raquo;" (because I don't want to use UTF8 characters in code).
                        return ($this->showCategoryNames ? $lang->get($daoInstance->getCategoryTitle()) . isys_glob_html_entity_decode(' &raquo; ') : '') .
                            $lang->get($property[C__PROPERTY__INFO][C__PROPERTY__INFO__TITLE]);
                    }

                    // @todo  This should never happen - throw an exception?
                    return $value;
                }, array_keys($row));
            }

            $return['data'][] = array_map(function ($translation) use ($lang) {
                // Check whether value is null and convert it to an empty string instead
                if ($translation === null) {
                    return '';
                }

                if (strpos($translation, 'LC_') === false) {
                    return $translation;
                }

                if (strpos($translation, 'LC_') !== 0 || substr_count($translation, 'LC_') > 1) {
                    return $lang->get_in_text($translation);
                }

                return $lang->get($translation);
            }, array_values($row));
        }

        return $return;
    }
}

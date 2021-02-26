<?php

namespace idoit\Module\Cmdb\Model;

use idoit\Component\Provider\Factory;
use idoit\Component\Provider\Singleton;

/**
 * i-doit
 *
 * Ci Models
 *
 * @package     i-doit
 * @subpackage  Cmdb
 * @author      Leonard Fischer <lfischer@i-doit.com>
 * @version     1.7
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class CiTypeCategoryAssigner
{
    use Singleton, Factory;

    /**
     * Boolean switch to process all CI types.
     *
     * @var  boolean
     */
    private $allCiTypes = false;

    /**
     * Array of categories to be assigned.
     *
     * @var  array
     */
    private $categories = [
        C__CMDB__CATEGORY__TYPE_GLOBAL   => [],
        C__CMDB__CATEGORY__TYPE_SPECIFIC => null,
        C__CMDB__CATEGORY__TYPE_CUSTOM   => [],
    ];

    /**
     * Array of CI types to be checked for categories.
     *
     * @var  array
     */
    private $ciTypes = [];

    /**
     * Array of CI types to be excluded.
     *
     * @var  array
     */
    private $ciTypesToExclude = [];

    /**
     * DAO instance.
     *
     * @var  \isys_cmdb_dao_object_type
     */
    private $dao;

    /**
     * Method for setting all CI types.
     *
     * @param   boolean $activate
     *
     * @return  $this
     */
    public function setAllCiTypes($activate = true)
    {
        $this->ciTypes = [];
        $this->allCiTypes = (bool) $activate;

        return $this;
    }

    /**
     * Set any desirec CI types by their string constants or IDs.
     *
     * @param   array $ciTypes
     *
     * @return  $this
     */
    public function excludeCiTypes(array $ciTypes)
    {
        $this->ciTypesToExclude = $ciTypes;

        return $this;
    }

    /**
     * Set any desired CI types by their string constants or IDs.
     *
     * @param   array $ciTypes
     *
     * @return  $this
     */
    public function setCiTypes(array $ciTypes)
    {
        $this->ciTypes = $ciTypes;
        $this->allCiTypes = false;

        return $this;
    }

    /**
     * Add a single CI type by its string constant or ID.
     *
     * @param   string $ciType
     *
     * @return  $this
     */
    public function addCiType($ciType)
    {
        $this->ciTypes[] = $ciType;
        $this->allCiTypes = false;

        return $this;
    }

    /**
     * Sets the default categories (global, logbook, relation, virtual auth and planning).
     *
     * @return $this
     */
    public function setDefaultCategories()
    {
        $this->categories[C__CMDB__CATEGORY__TYPE_GLOBAL] = [
            'C__CATG__GLOBAL',
            'C__CATG__LOGBOOK',
            'C__CATG__RELATION',
            'C__CATG__VIRTUAL_AUTH',
            'C__CATG__PLANNING'
        ];

        return $this;
    }

    /**
     * Set any desired global categories by their ID or string constant.
     *
     * @param   array $categories
     *
     * @return  $this
     */
    public function setGlobalCategories(array $categories)
    {
        $this->categories[C__CMDB__CATEGORY__TYPE_GLOBAL] = $categories;

        return $this;
    }

    /**
     * Set a single specific category by its ID or string constant.
     *
     * @param   mixed $category
     *
     * @return  $this
     */
    public function setSpecificCategory($category)
    {
        if (is_numeric($category) || is_string($category)) {
            $this->categories[C__CMDB__CATEGORY__TYPE_SPECIFIC] = $category;
        }

        return $this;
    }

    /**
     * Set any desired custom categories by their ID or string constant.
     *
     * @param   array $categories
     *
     * @return  $this
     */
    public function setCustomCategories(array $categories)
    {
        $this->categories[C__CMDB__CATEGORY__TYPE_CUSTOM] = $categories;

        return $this;
    }

    /**
     * This method will start assigning the defined categories to the defined CI types.
     *
     * @return $this
     * @throws \isys_exception_dao
     */
    public function assign()
    {
        // Exit, if no categories have been selected.
        if (!$this->categories[C__CMDB__CATEGORY__TYPE_SPECIFIC] &&
            (!is_countable($this->categories[C__CMDB__CATEGORY__TYPE_GLOBAL]) || !count($this->categories[C__CMDB__CATEGORY__TYPE_GLOBAL])) &&
            (!is_countable($this->categories[C__CMDB__CATEGORY__TYPE_CUSTOM]) || !count($this->categories[C__CMDB__CATEGORY__TYPE_CUSTOM]))) {
            return $this;
        }

        // Check for CI types.
        if ($this->allCiTypes) {
            // We'd like to process all CI types.
            $result = $this->dao->get_object_types();
        } elseif (is_countable($this->ciTypes) && count($this->ciTypes)) {
            $this->ciTypes = array_unique($this->ciTypes);

            // We'd like to process only specific CI types.
            $result = $this->dao->get_object_types($this->ciTypes);
        } else {
            // No CI types have been selected: exit!
            return $this;
        }

        // Iterate over the results.
        if (is_countable($result) && count($result)) {
            // First we'll retrieve a clean array of category IDs.
            $categories = $this->collectCategoryIDs();
            $ciTypes = [];
            $ciTypeBlacklist = array_flip($this->ciTypesToExclude);

            while ($ciType = $result->get_row()) {
                if (isset($ciTypeBlacklist[$ciType['isys_obj_type__id']]) || isset($ciTypeBlacklist[$ciType['isys_obj_type__const']])) {
                    continue;
                }

                $ciTypes[] = $this->dao->convert_sql_id($ciType['isys_obj_type__id']);
            }

            $ciTypes = array_unique(array_filter($ciTypes));

            $this->dao->begin_update();

            if (count($ciTypes)) {
                if (is_countable($categories[C__CMDB__CATEGORY__TYPE_GLOBAL]) && count($categories[C__CMDB__CATEGORY__TYPE_GLOBAL])) {
                    $this->assignGlobalCategories($categories[C__CMDB__CATEGORY__TYPE_GLOBAL], $ciTypes);
                }

                if ($categories[C__CMDB__CATEGORY__TYPE_SPECIFIC] > 0) {
                    $this->assignSpecificCategories($categories[C__CMDB__CATEGORY__TYPE_SPECIFIC], $ciTypes);
                }

                if (is_countable($categories[C__CMDB__CATEGORY__TYPE_CUSTOM]) && count($categories[C__CMDB__CATEGORY__TYPE_CUSTOM])) {
                    $this->assignCustomCategories($categories[C__CMDB__CATEGORY__TYPE_CUSTOM], $ciTypes);
                }
            }

            // Remove all duplicate global- and custom category assignments.
            $this->deleteDuplicateAssignments();

            // At the end, apply the update.
            $this->dao->apply_update();
        }

        return $this;
    }

    /**
     * This method will remove all duplicated global- and custom category assignments.
     *
     * @return  $this
     * @throws  \isys_exception_dao
     */
    public function deleteDuplicateAssignments()
    {
        // Delete all directly assigned sub-categories.
        $this->dao->update('DELETE FROM isys_obj_type_2_isysgui_catg 
            WHERE isys_obj_type_2_isysgui_catg__isysgui_catg__id IN (SELECT isysgui_catg__id FROM isysgui_catg WHERE isysgui_catg__parent IS NOT NULL);');

        // This query will remove all duplicated assignments of global categories.
        $deleteDuplicateQuery = 'DELETE main FROM isys_obj_type_2_isysgui_catg main
            LEFT JOIN (
                SELECT *, COUNT(*) c
                FROM isys_obj_type_2_isysgui_catg
                GROUP BY isys_obj_type_2_isysgui_catg__isys_obj_type__id, isys_obj_type_2_isysgui_catg__isysgui_catg__id
                HAVING c > 1) sub
                    ON main.isys_obj_type_2_isysgui_catg__isysgui_catg__id = sub.isys_obj_type_2_isysgui_catg__isysgui_catg__id
                    AND main.isys_obj_type_2_isysgui_catg__isys_obj_type__id = sub.isys_obj_type_2_isysgui_catg__isys_obj_type__id
            WHERE main.isys_obj_type_2_isysgui_catg__id != sub.isys_obj_type_2_isysgui_catg__id;';

        $this->dao->update($deleteDuplicateQuery);

        $deleteDuplicateQuery = 'DELETE main FROM isys_obj_type_2_isysgui_catg_custom main
            LEFT JOIN (
                SELECT *, COUNT(*) c
                FROM isys_obj_type_2_isysgui_catg_custom
                GROUP BY isys_obj_type_2_isysgui_catg_custom__isys_obj_type__id, isys_obj_type_2_isysgui_catg_custom__isysgui_catg_custom__id
                HAVING c > 1) sub
                    ON main.isys_obj_type_2_isysgui_catg_custom__isysgui_catg_custom__id = sub.isys_obj_type_2_isysgui_catg_custom__isysgui_catg_custom__id
                    AND main.isys_obj_type_2_isysgui_catg_custom__isys_obj_type__id = sub.isys_obj_type_2_isysgui_catg_custom__isys_obj_type__id
            WHERE main.isys_obj_type_2_isysgui_catg_custom__id != sub.isys_obj_type_2_isysgui_catg_custom__id;';

        $this->dao->update($deleteDuplicateQuery);

        return $this;
    }

    /**
     * This method will be used to retrieve uniform category IDs by given constants and IDs mixed together.
     *
     * @return  array
     */
    protected function collectCategoryIDs()
    {
        $categories = [
            C__CMDB__CATEGORY__TYPE_GLOBAL   => [],
            C__CMDB__CATEGORY__TYPE_SPECIFIC => null,
            C__CMDB__CATEGORY__TYPE_CUSTOM   => []
        ];

        // Go sure to include every category only once.
        $this->categories[C__CMDB__CATEGORY__TYPE_GLOBAL] = array_unique($this->categories[C__CMDB__CATEGORY__TYPE_GLOBAL]);
        $this->categories[C__CMDB__CATEGORY__TYPE_CUSTOM] = array_unique($this->categories[C__CMDB__CATEGORY__TYPE_CUSTOM]);

        foreach ($this->categories[C__CMDB__CATEGORY__TYPE_GLOBAL] as $globalCategory) {
            if (is_numeric($globalCategory)) {
                $categories[C__CMDB__CATEGORY__TYPE_GLOBAL][] = $this->dao->convert_sql_id($globalCategory);
            } else {
                $globalCategoryID = $this->dao->get_catg_by_const($globalCategory)
                    ->get_row_value('isysgui_catg__id');

                if ($globalCategoryID > 0) {
                    $categories[C__CMDB__CATEGORY__TYPE_GLOBAL][] = $this->dao->convert_sql_id($globalCategoryID);
                }
            }
        }

        if (!empty($this->categories[C__CMDB__CATEGORY__TYPE_SPECIFIC])) {
            if (is_numeric($this->categories[C__CMDB__CATEGORY__TYPE_SPECIFIC])) {
                $categories[C__CMDB__CATEGORY__TYPE_SPECIFIC] = $this->dao->convert_sql_id($this->categories[C__CMDB__CATEGORY__TYPE_SPECIFIC]);
            } else {
                $specificCategoryID = $this->dao->get_cats_by_const($this->categories[C__CMDB__CATEGORY__TYPE_SPECIFIC])
                    ->get_row_value('isysgui_cats__id');

                if ($specificCategoryID > 0) {
                    $categories[C__CMDB__CATEGORY__TYPE_SPECIFIC] = $this->dao->convert_sql_id($specificCategoryID);
                }
            }
        }

        foreach ($this->categories[C__CMDB__CATEGORY__TYPE_CUSTOM] as $customCategory) {
            if (is_numeric($customCategory)) {
                $categories[C__CMDB__CATEGORY__TYPE_CUSTOM][] = $this->dao->convert_sql_id($customCategory);
            } else {
                $customCategoryID = $this->dao->get_catc_by_const($customCategory)
                    ->get_row_value('isysgui_catg_custom__id');

                if ($customCategoryID > 0) {
                    $categories[C__CMDB__CATEGORY__TYPE_CUSTOM][] = $this->dao->convert_sql_id($customCategoryID);
                }
            }
        }

        return $categories;
    }

    /**
     * Method for assigning global categories.
     *
     * @param array $categories
     * @param array $ciTypes
     *
     * @return bool
     * @throws \isys_exception_dao
     */
    protected function assignGlobalCategories(array $categories, array $ciTypes)
    {
        $values = [];

        foreach ($ciTypes as $ciType) {
            foreach ($categories as $category) {
                $values[] = '(' . $ciType . ', ' . $category . ')';
            }
        }

        if (count($values)) {
            $sql = 'INSERT INTO isys_obj_type_2_isysgui_catg
                (isys_obj_type_2_isysgui_catg__isys_obj_type__id, isys_obj_type_2_isysgui_catg__isysgui_catg__id)
                VALUES ' . implode(',', $values) . ';';

            return $this->dao->update($sql);
        }

        return true;
    }

    /**
     * Method for assigning specific categories.
     *
     * @param integer $category
     * @param array   $ciTypes
     *
     * @return bool
     * @throws \isys_exception_dao
     */
    protected function assignSpecificCategories($category, array $ciTypes)
    {
        $condition = '';

        if (!$this->allCiTypes) {
            $condition = ' WHERE isys_obj_type__id ' . $this->dao->prepare_in_condition($ciTypes);
        }

        if ($category > 0) {
            $sql = 'UPDATE isys_obj_type
                SET isys_obj_type__isysgui_cats__id = ' . $this->dao->convert_sql_id($category) . $condition . ';';

            return $this->dao->update($sql);
        }

        return true;
    }

    /**
     * Method for assigning custom categories.
     *
     * @param array $categories
     * @param array $ciTypes
     *
     * @return bool
     * @throws \isys_exception_dao
     */
    protected function assignCustomCategories(array $categories, array $ciTypes)
    {
        $values = [];

        foreach ($ciTypes as $ciType) {
            foreach ($categories as $category) {
                $values[] = '(' . $ciType . ', ' . $category . ')';
            }
        }

        if (count($values)) {
            $sql = 'INSERT INTO isys_obj_type_2_isysgui_catg_custom
                (isys_obj_type_2_isysgui_catg_custom__isys_obj_type__id, isys_obj_type_2_isysgui_catg_custom__isysgui_catg_custom__id)
                VALUES ' . implode(',', $values) . ';';

            return $this->dao->update($sql);
        }

        return true;
    }

    /**
     * Simple CiTypeCategoryAssigner constructor.
     *
     * @param  \isys_component_database $database
     */
    public function __construct(\isys_component_database $database)
    {
        $this->dao = \isys_cmdb_dao_object_type::instance($database);
    }
}

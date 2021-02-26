<?php

/**
 * i-doit
 *
 * Validation DAO
 *
 * @package     i-doit
 * @subpackage  CMDB_Low-Level_API
 * @author      Leonard Fischer <lfischer@i-doit.org>
 * @version     1.5
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_validation extends isys_cmdb_dao
{
    /**
     * Retrieve contents from isys_validation_config.
     *
     * @param int    $configurationId
     * @param int    $categoryId
     * @param string $categoryType
     *
     * @return isys_component_dao_result
     * @throws isys_exception_database
     * @author Leonard Fischer <lfischer@i-doit.org>
     */
    public function get_data($configurationId = null, $categoryId = null, $categoryType = 'g')
    {
        $sql = 'SELECT * FROM isys_validation_config WHERE TRUE ';

        if ($configurationId !== null) {
            $sql .= 'AND isys_validation_config__id = ' . $this->convert_sql_id($configurationId) . ' ';
        }

        if ($categoryId !== null) {
            if ($categoryType === 'g' || $categoryType === 's') {
                $sql .= 'AND isys_validation_config__category_class = ' . $this->convert_sql_text($this->getClassNameById($categoryType, (int)$categoryId));
            } else {
                $sql .= 'AND isys_validation_config__isysgui_cat' . $categoryType . '__id = ' . $this->convert_sql_id($categoryId);
            }
        }

        return $this->retrieve($sql . ';');
    }

    /**
     * Method for resetting the complete validation configuration.
     *
     * @return bool
     * @throws isys_exception_dao
     * @author Leonard Fischer <lfischer@i-doit.org>
     */
    public function truncate(): bool
    {
        return ($this->update('TRUNCATE isys_validation_config;') && $this->apply_update());
    }

    /**
     * Method for creating a new validation config in the database.
     *
     * @param array $p_data
     *
     * @return bool
     * @throws isys_exception_dao
     * @throws isys_exception_database
     * @author Leonard Fischer <lfischer@i-doit.org>
     */
    public function create(array $p_data)
    {
        $l_json = isys_format_json::encode($p_data['config']);

        $categoryType = 'g';
        $categoryId = $p_data['catg'];

        if ($p_data['cats'] > 0) {
            $categoryType = 's';
            $categoryId = $p_data['cats'];
        } elseif ($p_data['catc'] > 0) {
            $categoryType = 'g_custom';
            $categoryId = $p_data['catc'];
        }

        // Create.
        $l_sql = 'INSERT INTO isys_validation_config SET
			isys_validation_config__isysgui_catg__id = ' . $this->convert_sql_id($p_data['catg']) . ',
			isys_validation_config__isysgui_cats__id = ' . $this->convert_sql_id($p_data['cats']) . ',
			isys_validation_config__isysgui_catg_custom__id = ' . $this->convert_sql_id($p_data['catc']) . ',
			isys_validation_config__category_class = ' . $this->convert_sql_text($this->getClassNameById($categoryType, (int)$categoryId)) . ',
			isys_validation_config__json = ' . $this->convert_sql_text($l_json) . ';';

        return ($this->update($l_sql) && $this->apply_update());
    }

    /**
     * @param string $categoryType
     * @param int    $categoryId
     *
     * @return string
     * @throws isys_exception_database
     */
    private function getClassNameById(string $categoryType, int $categoryId): string
    {
        if ($categoryType !== 'g' && $categoryType !== 's' && $categoryType !== 'g_custom') {
            throw new RuntimeException('You may only pass "g", "g_custom" or "s" as category type.');
        }

        if (!is_numeric($categoryId) || $categoryId <= 0) {
            throw new RuntimeException('The passed category ID has to be a positive int value.');
        }

        $sql = "SELECT isysgui_cat{$categoryType}__class_name AS classname
            FROM isysgui_cat{$categoryType} 
            WHERE isysgui_cat{$categoryType}__id = {$categoryId} 
            LIMIT 1;";

        return (string)$this->retrieve($sql)->get_row_value('classname');
    }
}

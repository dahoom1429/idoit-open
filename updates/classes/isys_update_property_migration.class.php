<?php

/**
 * i-doit - Property migration for the "isys_property_2_cat" table.
 * This is used heavily by the report manager.
 *
 * @package     i-doit
 * @subpackage  Update
 * @author      Leonard Fischer <lfischer@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_update_property_migration extends isys_update
{
    /**
     * @var array
     */
    protected $m_cat_blacklist = [
        'C__CATS__PERSON',
        'C__CATS__ORGANIZATION',    // @see ID-3987
        'C__CATS__PERSON_GROUP'     // @see ID-3987
        //		'C__CATG__OBJECT'
    ];

    /**
     * @var array
     */
    protected $m_cat_data = [];

    /**
     * @var isys_cmdb_dao
     */
    protected $m_dao = null;

    /**
     * @var isys_component_database
     */
    protected $m_db = null;

    /**
     * @var array
     */
    protected $m_migrated = [];

    /**
     * @var array
     */
    protected $m_missing_classes = [];

    /**
     * @var array
     */
    protected $m_skipped = [];

    /**
     * @var array
     */
    protected $m_sql_queries = [];

    /**
     * Method for collecting the category data from the global and specific categories.
     *
     * @param   $p_category_type
     *
     * @return  isys_update_property_migration
     * @author  Leonard Fischer <lfischer@i-doit.org>
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function collect_category_data($p_category_type = null)
    {
        // Get all available categories as resultset.
        if ($p_category_type !== null) {
            switch ($p_category_type) {
                case C__CMDB__CATEGORY__TYPE_GLOBAL:
                    $m_cat_res['g'] = $this->m_dao->get_all_catg();

                    // Reset the category data.
                    $this->m_cat_data['g'] = [];
                    break;
                case C__CMDB__CATEGORY__TYPE_SPECIFIC:
                    $m_cat_res['s'] = $this->m_dao->get_all_cats();

                    // Reset the category data.
                    $this->m_cat_data['s'] = [];
                    break;
                case C__CMDB__CATEGORY__TYPE_CUSTOM:
                    $m_cat_res['g_custom'] = $this->m_dao->get_all_catg_custom();

                    // Reset the category data.
                    $this->m_cat_data['g_custom'] = [];
                    break;
            }
        } else {
            $m_cat_res = [
                'g'        => $this->m_dao->get_all_catg(),
                's'        => $this->m_dao->get_all_cats(),
                'g_custom' => $this->m_dao->get_all_catg_custom()
            ];

            // Reset the category data.
            $this->m_cat_data = [
                'g'        => [],
                's'        => [],
                'g_custom' => []
            ];
        }

        if ($p_category_type === null || $p_category_type == C__CMDB__CATEGORY__TYPE_GLOBAL) {
            $l_class_cache = [];

            if (isset($m_cat_res['g'])) {
                // Iterate through the resultsets and save the content.
                while ($l_row = $m_cat_res['g']->get_row()) {
                    // We filter direcoty-categories, this saves a little amount of resources.
                    if (!in_array($l_row['isysgui_catg__const'], $this->m_cat_blacklist) && substr($l_row['isysgui_catg__const'], -5) != '_ROOT' &&
                        !in_array($l_row['isysgui_catg__class_name'], $l_class_cache) && $l_row['isysgui_catg__type'] != isys_cmdb_dao_category::TYPE_FOLDER) {
                        $l_class_cache[] = $l_row['isysgui_catg__class_name'];
                        $this->m_cat_data['g'][] = [
                            'id'    => $l_row['isysgui_catg__id'],
                            'class' => $l_row['isysgui_catg__class_name'],
                            'const' => $l_row['isysgui_catg__const']
                        ];
                    }
                }
            }
        }

        if ($p_category_type === null || $p_category_type == C__CMDB__CATEGORY__TYPE_SPECIFIC) {
            // For the specific categories we reset our class-cache.
            $l_class_cache = [];

            if (isset($m_cat_res['s'])) {
                while ($l_row = $m_cat_res['s']->get_row()) {
                    // We filter direcoty-categories, this saves a little amount of resources.
                    if (!in_array($l_row['isysgui_cats__const'], $this->m_cat_blacklist) && substr($l_row['isysgui_cats__const'], -5) != '_ROOT' &&
                        !in_array($l_row['isysgui_cats__class_name'], $l_class_cache)) {
                        $l_class_cache[] = $l_row['isysgui_cats__class_name'];
                        $this->m_cat_data['s'][] = [
                            'id'    => $l_row['isysgui_cats__id'],
                            'class' => $l_row['isysgui_cats__class_name'],
                            'const' => $l_row['isysgui_cats__const']
                        ];
                    }
                }
            }
        }

        if ($p_category_type === null || $p_category_type == C__CMDB__CATEGORY__TYPE_CUSTOM) {
            if (isset($m_cat_res['g_custom'])) {
                while ($l_row = $m_cat_res['g_custom']->get_row()) {
                    $l_class_cache[] = $l_row['isysgui_catg_custom__class_name'];
                    $this->m_cat_data['g_custom'][] = [
                        'id'    => $l_row['isysgui_catg_custom__id'],
                        'class' => $l_row['isysgui_catg_custom__class_name'],
                        'const' => $l_row['isysgui_catg_custom__const'],
                    ];
                }
            }
        }

        return $this;
    }

    /**
     * Inside this method, we query all the given SQLs.
     *
     * @return  isys_update_property_migration
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function execute_sql()
    {
        foreach ($this->m_sql_queries as $l_sql) {
            $this->m_db->query($l_sql);
        }

        // Force a commit to fix this in i-doit setup / update.
        $this->m_db->commit();

        return $this;
    }

    /**
     * Method for returning the result.
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function get_results()
    {
        return [
            'skipped'         => $this->m_skipped,
            'migrated'        => $this->m_migrated,
            'missing_classes' => $this->m_missing_classes,
        ];
    }

    /**
     * In this method we prepare the SQL statements for the given categories and return them.
     *
     * @param   string $p_cat_type
     *
     * @return  isys_update_property_migration
     * @author  Leonard Fischer <lfischer@i-doit.org>
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function prepare_sql_queries($p_cat_type, $p_log = true)
    {
        if ($p_log) {
            $l_log = isys_log_migration::get_instance();
        }

        if (is_array($this->m_cat_data[$p_cat_type])) {
            foreach ($this->m_cat_data[$p_cat_type] as $l_cat) {
                if (!class_exists($l_cat['class']) || (!defined($l_cat['const']) && $p_cat_type !== 'g_custom')) {
                    if (isset($l_log) && is_object($l_log)) {
                        $l_log->notice('Skipping class "' . $l_cat['class'] . '"');
                    }
                    $this->m_missing_classes[] = $l_cat['class'];
                    continue;
                }

                /**
                 * @var $l_cat_dao isys_cmdb_dao_category
                 */
                $l_cat_dao = new $l_cat['class']($this->m_db);

                if (is_a($l_cat_dao, 'isys_cmdb_dao_category')) {
                    $l_cat_properties = [];
                    $l_dynamic_properties = [];

                    if (method_exists($l_cat_dao, 'set_catg_custom_id') && $p_cat_type == 'g_custom') {
                        $l_cat_dao->set_catg_custom_id($l_cat['id']);
                    }

                    if (method_exists($l_cat_dao, 'get_properties_ng')) {
                        $l_cat_properties = $l_cat_dao->get_properties();
                    }

                    if (method_exists($l_cat_dao, 'get_dynamic_properties')) {
                        $l_dynamic_properties = $l_cat_dao->get_dynamic_properties();
                    }

                    if ($p_cat_type == 'g_custom') {
                        $l_cat_const = $l_cat['const'];
                    } else {
                        $l_cat_const = $l_cat_dao->get_category_const();
                    }

                    if (is_array($l_cat_properties)) {
                        foreach ($l_cat_properties as $l_prop => $l_property) {
                            // We don't allow numeric keys.
                            if (is_numeric($l_prop)) {
                                if (isset($l_log) && is_object($l_log)) {
                                    $l_log->notice('Skipping class "' . $l_cat['class'] . '", property "' . $l_property[C__PROPERTY__INFO][C__PROPERTY__INFO__DESCRIPTION] .
                                        '"');
                                }
                                $this->m_skipped[$l_cat['class']][] = $l_property[C__PROPERTY__INFO][C__PROPERTY__INFO__TITLE];
                                continue;
                            }

                            // Also the fields have to start with "isys_" and may not contain dots.
                            if (isset($l_property[C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD]) &&
                                (substr($l_property[C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD], 0, 5) != 'isys_' ||
                                    strstr($l_property[C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD], '.'))) {
                                if (isset($l_log) && is_object($l_log)) {
                                    $l_log->notice('Skipping class "' . $l_cat['class'] . '", property "' . $l_property[C__PROPERTY__INFO][C__PROPERTY__INFO__DESCRIPTION] .
                                        '"');
                                }
                                $this->m_skipped[$l_cat['class']][] = $l_property[C__PROPERTY__INFO][C__PROPERTY__INFO__TITLE];
                                continue;
                            }

                            if (isset($l_log) && is_object($l_log)) {
                                if (!isset($l_property[C__PROPERTY__INFO][C__PROPERTY__INFO__DESCRIPTION])) {
                                    $l_log->notice('Class: "' . $l_cat['class'] . '", property key "' . $l_prop . ' has no info."');
                                    $l_log->notice('Migrating class "' . $l_cat['class'] . '", property key "' . $l_prop . '"');
                                } else {
                                    $l_log->notice('Migrating class "' . $l_cat['class'] . '", property "' . $l_property[C__PROPERTY__INFO][C__PROPERTY__INFO__DESCRIPTION] .
                                        '"');
                                }
                            }

                            $this->m_migrated[$l_cat['class']][] = $l_prop;

                            $l_provides = 0;

                            if (isset($l_property[C__PROPERTY__PROVIDES])) {
                                if ($l_property[C__PROPERTY__PROVIDES] instanceof \idoit\Component\Property\Configuration\PropertyProvides) {
                                    $l_provides = $l_property[C__PROPERTY__PROVIDES]->getProvidesBit();
                                } else {
                                    foreach ($l_property[C__PROPERTY__PROVIDES] as $l_bit => $l_provided) {
                                        if ($l_provided) {
                                            $l_provides += $l_bit;
                                        }
                                    }
                                }
                            }

                            if (!isset($l_property[C__PROPERTY__INFO][C__PROPERTY__INFO__TITLE])) {
                                //echo $l_cat['class'] . "<br>";
                                //echo $l_prop . "<br>";
                            }

                            $this->m_sql_queries[] = "INSERT INTO isys_property_2_cat (
                                isys_property_2_cat__isysgui_cat" . $p_cat_type . "__id,
                                isys_property_2_cat__cat_const,
                                isys_property_2_cat__prop_type,
                                isys_property_2_cat__prop_key,
                                isys_property_2_cat__prop_title,
                                isys_property_2_cat__prop_provides) VALUES (" . $l_cat['id'] . ", " . $l_cat_dao->convert_sql_text($l_cat_const) . ", " .
                                C__PROPERTY_TYPE__STATIC . ", " . "'" . $l_prop . "', " . "" .
                                $l_cat_dao->convert_sql_text($l_property[C__PROPERTY__INFO][C__PROPERTY__INFO__TITLE]) . ", " . $l_provides . ");";
                        }
                    }

                    if (isset($l_dynamic_properties) && is_array($l_dynamic_properties)) {
                        foreach ($l_dynamic_properties as $l_prop => $l_property) {
                            $l_provides = 0;

                            if (isset($l_property[C__PROPERTY__PROVIDES])) {
                                if ($l_property[C__PROPERTY__PROVIDES] instanceof \idoit\Component\Property\Configuration\PropertyProvides) {
                                    $l_provides = $l_property[C__PROPERTY__PROVIDES]->getProvidesBit();
                                } else {
                                    foreach ($l_property[C__PROPERTY__PROVIDES] as $l_bit => $l_provided) {
                                        if ($l_provided) {
                                            $l_provides += $l_bit;
                                        }
                                    }
                                }
                            }

                            $this->m_sql_queries[] = "INSERT INTO isys_property_2_cat (
                                isys_property_2_cat__isysgui_cat" . $p_cat_type . "__id,
                                isys_property_2_cat__cat_const,
                                isys_property_2_cat__prop_type,
                                isys_property_2_cat__prop_key,
                                isys_property_2_cat__prop_title,
                                isys_property_2_cat__prop_provides) VALUES (" . $l_cat['id'] . ", " . $l_cat_dao->convert_sql_text($l_cat_const) . ", " .
                                C__PROPERTY_TYPE__DYNAMIC . ", " . "'" . $l_prop . "', " . "'" . $l_property[C__PROPERTY__INFO][C__PROPERTY__INFO__TITLE] . "', " .
                                $l_provides . ");";
                        }
                    }
                }
            }
        }

        return $this;
    }

    /**
     * Method for reseting the "isys_property_2_cat" table and clearing all prepared SQL statements.
     *
     * @return  isys_update_property_migration
     * @author  Leonard Fischer <lfischer@i-doit.org>
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function reset_property_table($p_category_type = null)
    {
        // Here we truncate the isys_property_2_cat table.
        $l_delete_query = '';
        if ($p_category_type !== null) {
            switch ($p_category_type) {
                case C__CMDB__CATEGORY__TYPE_GLOBAL:
                    $l_delete_query = 'DELETE FROM isys_property_2_cat WHERE isys_property_2_cat__isysgui_catg__id IS NOT NULL;';
                    break;
                case C__CMDB__CATEGORY__TYPE_SPECIFIC:
                    $l_delete_query = 'DELETE FROM isys_property_2_cat WHERE isys_property_2_cat__isysgui_cats__id IS NOT NULL;';
                    break;
                case C__CMDB__CATEGORY__TYPE_CUSTOM:
                    $l_delete_query = 'DELETE FROM isys_property_2_cat WHERE isys_property_2_cat__isysgui_catg_custom__id IS NOT NULL;';
                    break;
            }
        } else {
            $l_delete_query = 'DELETE FROM isys_property_2_cat;';
        }

        $this->m_db->query($l_delete_query);

        if ($p_category_type !== null) {
            $l_increment = array_pop($this->m_db->fetch_row_assoc($this->m_db->query('SELECT MAX(`isys_property_2_cat__id`)+1 AS cnt FROM `isys_property_2_cat`')));
            $l_increment = ($l_increment > 0) ? $l_increment : 1;
            $this->m_db->query('ALTER TABLE isys_property_2_cat AUTO_INCREMENT = ' . $l_increment);
        } else {
            $this->m_db->query('ALTER TABLE isys_property_2_cat AUTO_INCREMENT = 1;');
        }

        // Also we reset all our SQL's.
        $this->m_sql_queries = [];

        return $this;
    }

    /**
     * Method for setting the database component.
     *
     * @param   isys_component_database $p_db_component
     *
     * @return  isys_update_property_migration
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function set_database($p_db_component)
    {
        unset($this->m_db);
        $this->m_db = $p_db_component;
        $this->m_dao = new isys_cmdb_dao($p_db_component);

        return $this;
    }
}

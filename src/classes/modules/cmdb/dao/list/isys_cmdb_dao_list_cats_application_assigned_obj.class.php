<?php

/**
 * i-doit
 *
 * DAO: list for cluster members
 *
 * @package     i-doit
 * @subpackage  CMDB_Category_lists
 * @author      Dennis Stuecken <dstuecken@synetics.de>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_list_cats_application_assigned_obj extends isys_component_dao_category_table_list
{
    /**
     * @var  isys_cmdb_dao_category_g_relation
     */
    protected $m_dao_relation;

    /**
     * Return constant of category.
     *
     * @return  integer
     */
    public function get_category()
    {
        return defined_or_default('C__CATS__APPLICATION_ASSIGNED_OBJ');
    }

    /**
     * Return constant of category type.
     *
     * @return  integer
     */
    public function get_category_type()
    {
        return C__CMDB__CATEGORY__TYPE_SPECIFIC;
    }

    /**
     * Retrieve data for catg maintenance list view.
     *
     * @param   string  $p_str
     * @param   integer $p_objID
     * @param   integer $p_cRecStatus
     *
     * @return  isys_component_dao_result
     */
    public function get_result($p_str = null, $p_objID, $p_cRecStatus = null)
    {
        return isys_cmdb_dao_category_g_application::instance($this->m_db)
            ->get_assigned_objects_and_relations(
                null,
                $p_objID,
                empty($p_cRecStatus) ? $this->get_rec_status() : $p_cRecStatus,
                " AND main.isys_obj__status = " . C__RECORD_STATUS__NORMAL
            );
    }

    /**
     * @param  array &$p_row
     */
    public function modify_row(&$p_row)
    {
        $l_quick_info = new isys_ajax_handler_quick_info;

        $l_relation_type = $this->m_dao_relation->get_relation_type($p_row['isys_catg_relation_list__isys_relation_type__id'])
            ->get_row();

        $p_row["main_obj_title"] = $l_quick_info->get_quick_info($p_row["main_obj_id"], $p_row["main_obj_title"], C__LINK__OBJECT);
        $p_row["rel_obj_title"] = $l_quick_info->get_quick_info($p_row["rel_obj_id"], $p_row['slave_title'] . ' ' . isys_application::instance()->container->get('language')
                ->get($l_relation_type['isys_relation_type__slave']) . ' ' . $p_row['master_title'], C__LINK__OBJECT);

        if ($p_row['isys_cats_app_variant_list__title'] != '' && $p_row['isys_cats_app_variant_list__variant'] != '') {
            $p_row['isys_cats_app_variant_list__variant'] .= ' (' . $p_row['isys_cats_app_variant_list__title'] . ')';
        }

        // Find the assigned license.
        if ($p_row['isys_catg_application_list__isys_cats_lic_list__id'] > 0) {
            $l_row = isys_factory_cmdb_category_dao::get_instance('isys_cmdb_dao_category_s_lic', $this->m_db)
                ->get_data($p_row['isys_catg_application_list__isys_cats_lic_list__id'])
                ->get_row();

            $p_row["assigned_license"] = $l_quick_info->get_quick_info($l_row["isys_obj__id"], $l_row["isys_obj__title"], C__LINK__OBJECT);
        }

        if (!empty($p_row['isys_catg_version_list__title'])) {
            $p_row['assigned_version'] = $p_row['isys_catg_version_list__title'] .
                (!empty($p_row['isys_catg_version_list__hotfix']) ? ' (' . $p_row['isys_catg_version_list__hotfix'] . ')' : '');
        }

        $p_row['version_sort'] = preg_replace_callback(
            '/\d+/',
            static function ($matches) {
                return str_pad($matches[0], 6, '0', STR_PAD_LEFT);
            },
            $p_row['assigned_version']
        );
    }

    /**
     * Returns array with table headers.
     *
     * @return  array
     */
    public function get_fields()
    {
        return [
            'rel_obj_title'                       => 'LC__CATS__APPLICATION_ASSIGNMENT__INSTALLATION_INSTANCE',
            'main_obj_title'                      => 'LC__UNIVERSAL__INSTALLED_ON',
            'assigned_license'                    => 'LC__CMDB__CATG__LIC_ASSIGN__LICENSE',
            'assigned_version'                    => 'LC__CATG__VERSION_TITLE_AND_PATCHLEVEL',
            'isys_cats_app_variant_list__variant' => 'LC__CMDB__CATS__APPLICATION_VARIANT__VARIANT',
            'version_sort'                        => false
        ];
    }

    /**
     * Construct the DAO object.
     *
     * @param  isys_component_database $p_db
     */
    public function __construct($p_db)
    {
        $this->m_dao_relation = new isys_cmdb_dao_category_g_relation($p_db);

        parent::__construct($p_db);
    }

    /**
     * Order condition
     *
     * @param string $p_column
     * @param string $p_direction
     *
     * @return string
     */
    public function get_order_condition($p_column, $p_direction)
    {
        if ($p_column == 'assigned_version') {
            $p_column = 'version_sort';
        }

        return $p_column . " " . $p_direction;
    }
}

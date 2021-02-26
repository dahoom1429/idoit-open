<?php

/**
 * i-doit
 *
 * DAO: ObjectType list for Emergency plans
 *
 * @package     i-doit
 * @subpackage  CMDB_Category_lists
 * @author      Andre Wösten <awoesten@i-doit.org>
 * @author      Leonard Fischer <lfischer@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_list_catg_application extends isys_component_dao_category_table_list implements isys_cmdb_dao_list_interface
{
    /**
     * Return constant of category
     *
     * @return  integer
     * @author  Niclas Potthast <npotthast@i-doit.org>
     */
    public function get_category()
    {
        return defined_or_default('C__CATG__APPLICATION');
    }

    /**
     * Return constant of category type
     *
     * @return  integer
     * @author  Niclas Potthast <npotthast@i-doit.org>
     */
    public function get_category_type()
    {
        return C__CMDB__CATEGORY__TYPE_GLOBAL;
    }

    /**
     * Gets result list.
     *
     * @param   string  $p_str
     * @param   integer $p_obj_id
     * @param   integer $p_record_status
     *
     * @return  isys_component_dao_result
     */
    public function get_result($p_str = null, $p_obj_id, $p_record_status = null)
    {
        $l_status = $p_record_status ?: $this->get_rec_status();
        $l_object = $p_obj_id ?: $this->m_cat_dao->get_object_id();

        $l_sql = 'SELECT isys_catg_application_list__id, isys_obj__title, isys_obj_type__title, isys_application_manufacturer__title, isys_cats_app_variant_list__variant,
				isys_catg_application_list__bequest_nagios_services, isys_catg_application_list__isys_cats_lic_list__id, isys_catg_application_list__isys_catg_version_list__id, isys_catg_accounting_list__inventory_no, isys_catg_version_list__title, isys_catg_version_list__hotfix, isys_catg_application_priority__title
		 	FROM isys_catg_application_list
			LEFT JOIN isys_catg_application_priority ON isys_catg_application_priority__id = isys_catg_application_list__isys_catg_application_priority__id
			LEFT JOIN isys_connection ON isys_connection__id = isys_catg_application_list__isys_connection__id
			LEFT JOIN isys_obj ON isys_connection__isys_obj__id = isys_obj__id
			LEFT JOIN isys_obj_type ON isys_obj__isys_obj_type__id = isys_obj_type__id
			LEFT JOIN isys_cats_application_list ON isys_cats_application_list__isys_obj__id = isys_obj__id
			LEFT JOIN isys_application_manufacturer ON isys_cats_application_list__isys_application_manufacturer__id = isys_application_manufacturer__id
			LEFT JOIN isys_cats_app_variant_list ON isys_cats_app_variant_list__id = isys_catg_application_list__isys_cats_app_variant_list__id
			LEFT JOIN isys_catg_accounting_list ON isys_catg_accounting_list__isys_obj__id = isys_obj__id
			LEFT JOIN isys_catg_version_list ON isys_catg_version_list__id = isys_catg_application_list__isys_catg_version_list__id
			WHERE TRUE';

        if ($l_object) {
            $l_sql .= ' AND isys_catg_application_list__isys_obj__id = ' . $this->convert_sql_int($l_object);
        }

        if ($l_status) {
            $l_sql .= ' AND isys_catg_application_list__status = ' . $this->convert_sql_int($l_status);
        }

        return $this->retrieve($l_sql);
    }

    /**
     * Modify row method.
     *
     * @param   array $p_row
     *
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function modify_row(&$p_row)
    {
        global $g_dirs;

        if ($p_row['isys_catg_application_list__isys_cats_lic_list__id'] > 0) {
            $l_licence = isys_cmdb_dao_category_s_lic::instance($this->m_db)
                ->get_data($p_row['isys_catg_application_list__isys_cats_lic_list__id'])
                ->get_row();

            if ((int)$l_licence['isys_obj__status'] === C__RECORD_STATUS__NORMAL) {
                $p_row['assigned_licence'] = $l_licence['isys_obj__title'] . ' (' . $l_licence['isys_cats_lic_list__key'] . ')';
            }
        }

        if ($p_row['isys_catg_application_list__isys_catg_version_list__id'] > 0) {
            $p_row['assigned_version'] = $p_row['isys_catg_version_list__title'] .
                (!empty($p_row['isys_catg_version_list__hotfix']) ? ' (' . $p_row['isys_catg_version_list__hotfix'] . ')' : '');
        }

        if ($p_row['isys_catg_application_list__bequest_nagios_services'] > 0) {
            $p_row['isys_catg_application_list__bequest_nagios_services'] = '<img src="' . $g_dirs['images'] .
                'icons/silk/bullet_green.png" class="vam mr5" /><span class="vam text-green">' . isys_application::instance()->container->get('language')
                    ->get('LC__UNIVERSAL__YES') . '</span>';
        } else {
            $p_row['isys_catg_application_list__bequest_nagios_services'] = '<img src="' . $g_dirs['images'] .
                'icons/silk/bullet_red.png" class="vam mr5" /><span class="vam text-red">' . isys_application::instance()->container->get('language')
                    ->get('LC__UNIVERSAL__NO') . '</span>';
        }

        if ($p_row['isys_cats_app_variant_list__variant'] != '' && $p_row['isys_cats_app_variant_list__title'] != '') {
            $p_row['isys_cats_app_variant_list__variant'] .= ' (' . $p_row['isys_cats_app_variant_list__title'] . ')';
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
     * Method for retrieving the field-names.
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function get_fields()
    {
        return [
            'isys_obj_type__title'                                => 'LC__CMDB__CATG__APPLICATION_TYPE',
            'isys_application_manufacturer__title'                => 'LC__CMDB__CATS__APPLICATION_MANUFACTURER',
            'isys_obj__title'                                     => 'LC__CMDB__CATG__APPLICATION',
            'isys_catg_application_priority__title'               => 'LC__CATG__APPLICATION_PRIORITY',
            'assigned_licence'                                    => 'LC__CMDB__CATG__LIC_ASSIGN__LICENSE',
            'assigned_version'                                    => 'LC__CATG__VERSION_TITLE_AND_PATCHLEVEL',
            'isys_cats_app_variant_list__variant'                 => 'LC__CMDB__CATS__APPLICATION_VARIANT__VARIANT',
            'isys_catg_accounting_list__inventory_no'             => 'LC__CMDB__CATG__ACCOUNTING_INVENTORY_NO',
            'isys_catg_application_list__bequest_nagios_services' => 'LC__CMDB__CATG__APPLICATION_BEQUEST_NAGIOS_SERVICES',
            'version_sort'                                        => false
        ];
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

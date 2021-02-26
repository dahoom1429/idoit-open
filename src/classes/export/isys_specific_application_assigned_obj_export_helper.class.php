<?php

/**
 * i-doit
 *
 * Export helper for global category hostaddress
 *
 * @package     i-doit
 * @subpackage  Export
 * @author      Van Quyen Hoang <qhoang@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_specific_application_assigned_obj_export_helper extends isys_export_helper
{
    /**
     * Export Helper for property assigned_variant for specific category application installation
     *
     * @param $p_value
     *
     * @return array
     */
    public function applicationAssignedVariant($p_value)
    {
        if (!empty($p_value)) {
            $l_dao = isys_cmdb_dao_category_s_application_variant::instance($this->m_database);
            $l_data = $l_dao->get_data($p_value)
                ->get_row();

            return [
                'id'      => $p_value,
                'title'   => $l_data['isys_cats_app_variant_list__title'],
                'type'    => 'C__CATS__APPLICATION_VARIANT',
                'variant' => $l_data['isys_cats_app_variant_list__variant']
            ];
        }

        return null;
    }

    /**
     * Import Helper for property assigned_variant for specific category application installation
     *
     * @param $value
     *
     * @return array
     */
    public function applicationAssignedVariant_import($value)
    {
        $data = $value;
        if (is_array($value[C__DATA__VALUE])) {
            $data = $value[C__DATA__VALUE];
        }

        $category = defined_or_default('C__CATS__APPLICATION_VARIANT');

        if ($category && isset($data['id']) && array_key_exists($data['id'], $this->m_category_data_ids[C__CMDB__CATEGORY__TYPE_SPECIFIC][$category])) {
            return $this->m_category_data_ids[C__CMDB__CATEGORY__TYPE_SPECIFIC][$category][$data['id']];
        }

        return null;
    }

    /**
     * Import helper for application version.
     *
     * @param   integer $value
     *
     * @return  array
     * @throws  isys_exception_general
     */
    public function applicationAssignedVersion($value)
    {
        if (!empty($value)) {
            $dao = isys_cmdb_dao_category_g_version::instance($this->m_database);
            $data = $dao->get_data($value)
                ->get_row();

            return [
                'id'      => $value,
                'title'   => $data['isys_catg_version_list__title'],
                'type'    => 'C__CATG__VERSION'
            ];
        }

        return null;
    }

    /**
     * Import Helper for property assigned_version for global category application
     *
     * @param   array $value
     *
     * @return  mixed
     */
    public function applicationAssignedVersion_import($value)
    {
        $data = $value;
        if (is_array($value[C__DATA__VALUE])) {
            $data = $value[C__DATA__VALUE];
        }

        $category = defined_or_default('C__CATG__VERSION');

        if ($category && isset($data['id']) && array_key_exists($data['id'], $this->m_category_data_ids[C__CMDB__CATEGORY__TYPE_GLOBAL][$category])) {
            return $this->m_category_data_ids[C__CMDB__CATEGORY__TYPE_GLOBAL][$category][$data['id']];
        }

        return null;
    }
}

<?php

/**
 * i-doit
 *
 * DAO: ObjectType list Licences
 *
 * @package     i-doit
 * @subpackage  CMDB_Category_lists
 * @author      Dennis Stücken <dstuecken@synetics.de>
 * @version     Niclas Potthast <npotthast@i-doit.org>
 * @version     Leonard Fischer <lfischer@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_list_cats_lic extends isys_component_dao_category_table_list
{
    /**
     * Return constant of category.
     *
     * @return  integer
     */
    public function get_category()
    {
        return defined_or_default('C__CATS__LICENCE_LIST');
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
     * Get result method.
     *
     * @param   string  $p_table
     * @param   integer $p_object_id
     * @param   null    $p_cRecStatus
     *
     * @return  isys_component_dao_result
     * @throws  isys_exception_database
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function get_result($p_table = null, $p_object_id, $p_cRecStatus = null)
    {
        $l_cRecStatus = empty($p_cRecStatus) ? $this->get_rec_status() : $p_cRecStatus;

        $l_sql = 'SELECT *, (isys_cats_lic_list__cost * isys_cats_lic_list__amount) AS cost_sum, isys_cats_lic_list__cost AS cost
			FROM isys_cats_lic_list
			WHERE isys_cats_lic_list__isys_obj__id = ' . $this->convert_sql_id($p_object_id) . '
			AND isys_cats_lic_list__status = ' . $this->convert_sql_int($l_cRecStatus) . ';';

        return $this->retrieve($l_sql);
    }

    /**
     * Modify method, will be called for each row.
     *
     * @globals  array        $g_dirs
     *
     * @param    array &$p_arrRow
     *
     * @throws isys_exception_dao
     */
    public function modify_row(&$p_arrRow)
    {
        global $g_dirs;

        $language = isys_application::instance()->container->get('language');
        $locale = isys_application::instance()->container->get('locales');

        if ($p_arrRow['cost_sum'] != '0') {
            $p_arrRow['cost'] = $p_arrRow['cost_sum'];
        }

        $l_licence_dao = new isys_cmdb_dao_licences($this->m_db, $p_arrRow['isys_cats_lic_list__isys_obj__id']);

        $l_lic_info = '';
        $l_lic_in_use = $l_licence_dao->get_licences_in_use($this->get_rec_status(), $p_arrRow['isys_cats_lic_list__id'])
            ->num_rows();
        $l_lic_available = (int)$p_arrRow['isys_cats_lic_list__amount'];

        if ($l_lic_available > 0) {
            // Wenn more than 90% of the licences are used, mark the key with a yellow "!" icon.
            if (($l_lic_in_use / $l_lic_available) >= 0.9) {
                $l_lic_info = ' <img src="' . $g_dirs['images'] . 'icons/infoicon/warning.png" class="vam" title="' . $language->get('LC__CMDB__CATS__LICENCE_INFO__KEYS_ARE_SOON_EXHAUSTED') . '" />';
            }

            // Wenn 100% or more of the licences are used, mark the key with a red "!" icon.
            if (($l_lic_in_use / $l_lic_available) > 1) {
                $l_lic_info = ' <img src="' . $g_dirs['images'] . 'icons/infoicon/error.png" class="vam" title="' . $language->get('LC__CMDB__CATS__LICENCE_INFO__KEYS_ARE_EXHAUSTED') . '" />';
            }
        }

        if (empty($p_arrRow['isys_cats_lic_list__type'])) {
            $p_arrRow['isys_cats_lic_list__type'] = isys_tenantsettings::get('gui.empty_value', '-');
        } else {
            $l_lic_types = isys_cmdb_dao_category_s_lic::instance($this->m_db)->callback_property_type(isys_request::factory());
            $p_arrRow['isys_cats_lic_list__type'] = $language->get($l_lic_types[$p_arrRow['isys_cats_lic_list__type']]);
        }

        $p_arrRow['licence_in_use'] = $l_lic_in_use . ' / ' . $l_lic_available . $l_lic_info;
        $p_arrRow['isys_cats_lic_list__start'] = '<span data-sort="' . $p_arrRow['isys_cats_lic_list__start'] . '">' .
            $locale->fmt_date($p_arrRow['isys_cats_lic_list__start'], true) . '</span>';
        $p_arrRow['isys_cats_lic_list__expire'] = '<span data-sort="' . $p_arrRow['isys_cats_lic_list__expire'] . '">' .
            $locale->fmt_date($p_arrRow['isys_cats_lic_list__expire'], true) . '</span>';
        $p_arrRow['cost'] = $locale->fmt_monetary($p_arrRow['cost']);
    }

    /**
     * Method for returning the table header..
     *
     * @return  array
     */
    public function get_fields()
    {
        return [
            'isys_cats_lic_list__key'    => 'LC__CMDB__CATS__LICENCE_KEY',
            'isys_cats_lic_list__type'   => 'LC__CMDB__CATS__LICENCE_TYPE',
            'licence_in_use'             => 'LC__CMDB__CATS__LICENCE_IN_USE',
            'isys_cats_lic_list__start'  => 'LC__CMDB__CATS__LICENCE_START',
            'isys_cats_lic_list__expire' => 'LC__CMDB__CATS__LICENCE_EXPIRE',
            'cost'                       => 'LC__CMDB__CATS__LICENCE_COST'
        ];
    }
}

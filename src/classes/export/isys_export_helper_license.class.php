<?php

/**
 * i-doit
 *
 * Export helper for licenses
 *
 * @package     i-doit
 * @subpackage  Export
 * @author      Selcuk Kekec <skekec@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_export_helper_license extends isys_export_helper
{
    /**
     * no import function is needed it is only used for the print view (specific category licences)
     *
     * @author Van Quyen Hoang <qhoang@i-doit.org>
     *
     * @param $p_id
     *
     * @return null
     */
    public function licence_property_overall_costs($p_id)
    {
        $l_dao = isys_cmdb_dao_category_s_lic::instance($this->m_database);

        $l_res = $l_dao->get_data($p_id);
        if ($l_res->num_rows() > 0) {
            $l_row = $l_res->get_row();
            $l_arr['title'] = isys_locale::get_instance()
                ->fmt_monetary($l_row['isys_cats_lic_list__amount'] * $l_row['isys_cats_lic_list__cost']);

            return $l_arr;
        }

        return null;
    }

    /**
     * no import function is needed it is only used for the print view (specific category licences)
     *
     * @author Van Quyen Hoang <qhoang@i-doit.org>
     *
     * @param $p_id
     *
     * @return null
     */
    public function licence_property_lic_not_in_use($p_id)
    {
        $l_dao = isys_cmdb_dao_category_s_lic::instance($this->m_database);

        $l_res = $l_dao->get_data($p_id);
        if ($l_res->num_rows() > 0) {
            $l_row = $l_res->get_row();
            $l_arr['title'] = $l_dao->dynamic_property_callback_free_licenses($l_row);

            return $l_arr;
        }

        return null;
    }

    /**
     * no import function is needed it is only used for the print view (specific category licences)
     *
     * @author Selcuk Kekec <skekec@i-doit.org>
     *
     * @param $p_id
     *
     * @return null
     */
    public function licence_property_used_licence($p_id)
    {
        $l_dao = isys_cmdb_dao_category_s_lic::instance($this->m_database);

        $l_res = $l_dao->get_data($p_id);
        if ($l_res->num_rows() > 0) {
            $l_row = $l_res->get_row();
            $l_arr['title'] = $l_dao->dynamic_property_callback_used_licenses($l_row);

            return $l_arr;
        }

        return null;
    }
}

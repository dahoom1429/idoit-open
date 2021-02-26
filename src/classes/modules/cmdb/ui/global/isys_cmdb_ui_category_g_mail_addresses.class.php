<?php

/**
 * i-doit
 *
 * UI: global category for mail addresses
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_ui_category_g_mail_addresses extends isys_cmdb_ui_category_global
{
    /**
     * @param   isys_cmdb_dao_category_g_mail_addresses $p_cat
     *
     * @return  void
     * @throws  isys_exception_cmdb
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function process(isys_cmdb_dao_category $p_cat)
    {
        $this->fill_formfields($p_cat, $l_rules, $p_cat->get_general_data());

        if (!isys_glob_is_edit_mode() && isset($l_rules['C__CMDB__CATG__MAIL_ADDRESSES__TITLE']['p_strValue']) &&
            !empty($l_rules['C__CMDB__CATG__MAIL_ADDRESSES__TITLE']['p_strValue'])) {
            $l_address = filter_var($l_rules['C__CMDB__CATG__MAIL_ADDRESSES__TITLE']['p_strValue'], FILTER_VALIDATE_EMAIL);

            if ($l_address) {
                $l_rules['C__CMDB__CATG__MAIL_ADDRESSES__TITLE']['p_strValue'] = '<a target="_blank" href="mailto:' . $l_address . '">' . $l_address . '</a>';
            }
        }

        // Apply rules.
        $this->get_template_component()
            ->smarty_tom_add_rules("tom.content.bottom.content", $l_rules);
    }
}
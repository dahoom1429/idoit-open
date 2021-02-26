<?php

/**
 * i-doit
 *
 * SNMP
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Dennis Stuecken <dstuecken@i-doit.org>
 * @version     0.9
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_ui_category_g_snmp extends isys_cmdb_ui_category_global
{
    /**
     * Process method.
     *
     * @param  isys_cmdb_dao_category_g_snmp $p_cat
     */
    public function process(isys_cmdb_dao_category $p_cat)
    {
        $l_catdata = $p_cat->get_general_data();

        isys_component_template_navbar::getInstance()
            ->set_active(false, C__NAVBAR_BUTTON__PRINT)
            ->set_visible(false, C__NAVBAR_BUTTON__PRINT);

        $l_primary_ip = isys_cmdb_dao_category_g_ip::instance($this->m_database_component)
            ->get_primary_ip($_GET[C__CMDB__GET__OBJECT])
            ->get_row_value('isys_cats_net_ip_addresses_list__title');

        $l_rules['C__CATG__SNMP_COMMUNITY']['p_strSelectedID'] = $l_catdata['isys_catg_snmp_list__isys_snmp_community__id'];
        $l_rules['C__CMDB__CAT__COMMENTARY_' . $p_cat->get_category_type() . $p_cat->get_category_id()]['p_strValue'] = $l_catdata['isys_catg_snmp_list__description'];

        if ($l_primary_ip !== null) {
            $l_has_primary = true;
            $l_rules['C__CATG__SNMP_HOSTADDRESS']['p_strValue'] = $l_primary_ip;

            try {
                $this->get_template_component()
                    ->assignByRef('snmp', new isys_library_snmp($l_primary_ip, $l_catdata['isys_snmp_community__title']));
            } catch (Exception $e) {
                isys_notify::warning($e->getMessage(), ['life' => 25]);
            }
        } else {
            $l_has_primary = false;

            $l_rules['C__CATG__SNMP_HOSTADDRESS']['p_strValue'] = 'No primary host address defined.';
        }
        $l_oids = unserialize($l_catdata['isys_catg_snmp_list__oids']);
        $countOids = is_countable($l_oids) ? count($l_oids) : 0;

        // Apply rules
        $this->get_template_component()
            ->assign('has_primary', $l_has_primary)
            ->assign('oids', $l_oids)
            ->assign('oid_count', $countOids)
            ->smarty_tom_add_rules('tom.content.bottom.content', $l_rules);
    }
}
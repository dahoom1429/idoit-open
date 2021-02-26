<?php

/**
 * i-doit
 *
 * UI: global category for jdisc custom attribute
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_ui_category_g_jdisc_discovery extends isys_cmdb_ui_category_global
{
    public function process(isys_cmdb_dao_category $p_cat)
    {
        /**
         * @var $l_module isys_module_jdisc
         * @var $l_dao_ip isys_cmdb_dao_category_g_ip
         */
        $l_module = isys_factory::get_instance('isys_module_jdisc', $p_cat->get_database_component());
        $l_dao_ip = isys_cmdb_dao_category_g_ip::factory($p_cat->get_database_component());
        $language = isys_application::instance()->container->get('language');

        $l_primary_address = $l_dao_ip->get_primary_ip_as_string($_GET[C__CMDB__GET__OBJECT]);

        // @see ID-5025 have to use the fqdn instead of the hostname
        $primaryIpArr = $l_dao_ip->get_primary_ip_by_object_id($_GET[C__CMDB__GET__OBJECT])
            ->get_row();
        if ($primaryIpArr['isys_catg_ip_list__id'] > 0) {
            $primaryIpFqdn = $l_dao_ip->dynamic_property_callback_hostname_fqdn($primaryIpArr);
        }
        $primaryFqdn = (strpos($primaryIpFqdn, ',') ? current(explode(',', $primaryIpFqdn)) : $primaryIpFqdn);
        $l_template = isys_application::instance()->container->get('template');
        $l_jdisc_servers = $l_module->get_jdisc_servers_as_array();
        $l_selected_jdisc_server = $l_module->get_jdisc_server_by_profile($_GET[C__CMDB__GET__OBJECTTYPE]);

        $l_rules['C__CMDB__CATG__JDISC_DISCOVERY__SERVER']['p_arData'] = $l_jdisc_servers;
        if ($l_selected_jdisc_server) {
            $l_rules['C__CMDB__CATG__JDISC_DISCOVERY__SERVER']['p_strSelectedID'] = $l_selected_jdisc_server['isys_jdisc_db__id'];
        }

        $l_rules['C__CMDB__CATG__JDISC_DISCOVERY__MODE']['p_strSelectedID'] = '2';
        $l_rules['C__CMDB__CATG__JDISC_DISCOVERY__MODE']['p_arData'] = [
            '2'   => 'LC__MODULE__JDISC__IMPORT__MODE_UPDATE',
            '2_1'  => 'LC__MODULE__JDISC__IMPORT__MODE_UPDATE_NEW_DISCOVERED',
            '3'   => 'LC__MODULE__JDISC__IMPORT__MODE_OVERWRITE',
            '3_1'   => 'LC__MODULE__JDISC__IMPORT__MODE_OVERWRITE_NEW_DISCOVERED',
        ];

        $l_rules['C__CMDB__CATG__JDISC_DISCOVERY__IP_CONFLICTS']['p_arData'] = get_smarty_arr_YES_NO();
        $l_rules['C__CMDB__CATG__JDISC_DISCOVERY__IP_CONFLICTS']['p_strSelectedID'] = 0;

        $l_rules['C__CMDB__CATG__JDISC_DISCOVERY__TARGET_TYPE']['p_arData'] = [
            isys_cmdb_dao_category_g_jdisc_discovery::C__JDISC_DISCOVERY__TARGET_TYPE__IP => $language->get('LC__CATG__IP_ADDRESS'),
            isys_cmdb_dao_category_g_jdisc_discovery::C__JDISC_DISCOVERY__TARGET_TYPE__FQDN => 'FQDN'
        ];
        $l_rules['C__CMDB__CATG__JDISC_DISCOVERY__TARGET_TYPE']['p_strSelectedID'] = isys_cmdb_dao_category_g_jdisc_discovery::C__JDISC_DISCOVERY__TARGET_TYPE__IP;
        $l_rules['C__CMDB__CATG__JDISC_DISCOVERY__TARGET_TYPE']['p_bInfoIconSpacer'] = 1;

        $l_template
            ->activate_editmode()
            ->assign('object_id', $_GET[C__CMDB__GET__OBJECT])
            ->assign('objectTypeID', $_GET[C__CMDB__GET__OBJECTTYPE])
            ->assign('is_jedi', $l_module->is_jedi())
            ->assign('primary_ip', $l_primary_address)
            ->assign('primary_hostname', $primaryFqdn)
            ->assign('ip_unique_check', (isys_tenantsettings::get('cmdb.unique.ip-address')) ? '0' : '1');
        if (!isys_tenantsettings::get('cmdb.unique.ip-address')) {
            $l_template->assign('ip_overwrite_warning', $language->get('LC__MODULE__JDISC__IMPORT__OVERWRITE_IP_ADDRESSES__DESCRIPTION_ACTIVATED'));
        } else {
            $l_template->assign('ip_overwrite_info', $language->get('LC__MODULE__JDISC__IMPORT__OVERWRITE_IP_ADDRESSES__DESCRIPTION_DEACTIVATED'));
        }

        $l_template->smarty_tom_add_rules("tom.content.bottom.content", $l_rules);

        isys_component_template_navbar::getInstance()
            ->set_active(false, C__NAVBAR_BUTTON__EDIT)
            ->set_visible(false, C__NAVBAR_BUTTON__EDIT);

        $this->deactivate_commentary();
    }

    /**
     * Sets the template file (*.tpl).
     *
     * @param   string $p_template
     *
     * @return  isys_cmdb_ui_category
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function set_template($p_template)
    {
        global $g_dirs;
        $this->m_template_file = $g_dirs["class"] . "/modules/jdisc/templates/content/bottom/content/" . $p_template;

        return $this;
    }

    public function __construct(isys_component_template &$p_template)
    {
        global $g_dirs;
        parent::__construct($p_template);
        $this->set_template($g_dirs["class"] . "/modules/jdisc/templates/content/bottom/content/catg__jdisc_discoverys.tpl");
    }
}

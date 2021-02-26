<?php

/**
 * i-doit
 * CMDB Drive: Global category
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Niclas Potthast <npotthast@i-doit.org>
 * @author      Dennis Blümer <dbluemer@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_ui_category_g_ldevclient extends isys_cmdb_ui_category_global
{
    /**
     * Show the detail-template for global category formfactor.
     *
     * @global  array                               $index_includes
     *
     * @param   isys_cmdb_dao_category_g_ldevclient $p_cat
     *
     * @author  Dennis Blümer <dbluemer@i-doit.org>
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function process(isys_cmdb_dao_category $p_cat)
    {
        $l_rules = [];
        $l_catdata = $p_cat->get_general_data();

        $this->fill_formfields($p_cat, $l_rules, $l_catdata);

        // Determine path binded to SAN-Pool.
        $l_str_out2 = [];
        $l_resFCPorts = $p_cat->get_paths($l_catdata['isys_catg_ldevclient_list__id']);

        if ($l_resFCPorts > 0) {
            while ($l_rowFCPorts = $l_resFCPorts->get_row()) {
                $l_str_out2[] = $l_rowFCPorts['isys_catg_fc_port_list__id'];
            }
        }

        $l_rules['C__CATG__LDEVCLIENT_PATHS']['p_strValue'] = implode(',', $l_str_out2);
        $l_rules['C__CATG__LDEVCLIENT_PATHS']['p_strPrim'] = $l_catdata['isys_catg_ldevclient_list__primary_path'];
        $l_rules['C__CATG__LDEVCLIENT_TITLE']['p_strValue'] = $l_catdata['isys_catg_ldevclient_list__title'];
        $l_rules['C__CATG__LDEVCLIENT_SANPOOL']['p_strSelectedID'] = $l_catdata['isys_catg_ldevclient_list__isys_catg_sanpool_list__id'];
        $l_rules['C__CATG__LDEVCLIENT_MULTIPATH']['p_strSelectedID'] = $l_catdata['isys_catg_ldevclient_list__isys_ldev_multipath__id'];
        $l_rules['C__CMDB__CAT__COMMENTARY_' . $p_cat->get_category_type() . $p_cat->get_category_id()]['p_strValue'] = $l_catdata['isys_catg_ldevclient_list__description'];

        if (!$p_cat->get_validation()) {
            $l_rules = isys_glob_array_merge($l_rules, $p_cat->get_additional_rules());
        }

        $this->get_template_component()
            ->smarty_tom_add_rules('tom.content.bottom.content', $l_rules);
    }
}
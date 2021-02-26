<?php

/**
 * i-doit
 *
 * UI: global category for custom identifiers
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 * @author      Selcuk Kekec <skekec@i-doit.com>
 */
class isys_cmdb_ui_category_g_identifier extends isys_cmdb_ui_category_global
{
    /**
     * Process ui rules
     *
     * @param \isys_cmdb_dao_category $p_cat
     *
     * @return array
     * @throws \isys_exception_dao_cmdb
     * @author Selcuk Kekec <skekec@i-doit.com>
     */
    public function process(isys_cmdb_dao_category $p_cat)
    {
        // Get rules
        $l_rules = parent::process($p_cat);

        // Check for empty datetime information
        if ($l_rules['C__CMDB__CATG__IDENTIFIER__LAST_EDITED']['p_strValue'] === '1970-01-01 01:00:00') {
            /**
             * This routine should not be necessary if smarty component takes care about
             * provided strings like '1970-01-01 01:00:00'. Because a change is fairly undeterministic
             * we handle it like this at the moment.
             */
            $l_rules['C__CMDB__CATG__IDENTIFIER__LAST_EDITED']['p_strValue'] = null;
        }

        // Reset rules
        $this->get_template_component()
            ->smarty_tom_add_rules('tom.content.bottom.content', $l_rules);

        return $l_rules;
    }

}
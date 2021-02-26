<?php

/**
 * CMDB UI: Global category service
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Van Quyen Hoang <qhoang@i-doit.org>
 * @version     1.5
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_ui_category_g_service extends isys_cmdb_ui_category_global
{
    /**
     * Show the detail-template for specific category net.
     *
     * @param   isys_cmdb_dao_category_g_service $p_cat
     *
     * @return  array|void
     * @author  Van Quyen Hoang <qhoang@i-doit.de>
     */
    public function process(isys_cmdb_dao_category $p_cat)
    {
        $this->fill_formfields($p_cat, $l_rules, $p_cat->get_general_data());

        /* @var  $l_dao_contact  isys_cmdb_dao_category_g_contact */
        $l_dao_contact = isys_cmdb_dao_category_g_contact::factory($p_cat->get_database_component());

        $l_role_title = isys_application::instance()->container->get('language')
            ->get($l_dao_contact->get_contact_tag_data(defined_or_default('C__CONTACT_TYPE__SERVICE_MANAGER'))
                ->get_row_value('isys_contact_tag__title'));

        $l_contacts_res = $l_dao_contact->get_contact_objects_by_tag($_GET[C__CMDB__GET__OBJECT], defined_or_default('C__CONTACT_TYPE__SERVICE_MANAGER'));

        if ($_GET[C__CMDB__GET__OBJECT] && $l_contacts_res->num_rows() > 0) {
            $l_contacts = [];
            $l_quicklink = new isys_ajax_handler_quick_info();

            while ($l_row = $l_contacts_res->get_row()) {
                $l_contacts[] = $l_quicklink->get_quick_info($l_row['isys_obj__id'], $l_row['isys_obj__title'], C__LINK__OBJECT);
            }

            $this->get_template_component()
                ->assign('contacts', '<ul class="fl"><li>' . implode('</li><li>', $l_contacts) . '</li></ul>');
        } else {
            // No contacts found with role service manager
            $this->get_template_component()
                ->assign('contacts', '<img class="vam" src="images/icons/infobox/blue.png"><span class="ml5">' .
                    sprintf(isys_application::instance()->container->get('language')
                        ->get('LC__CMDB__CATG__SERVICE__NO_CONTACTS_FOUND'), $l_role_title) . '</span>');
        }

        $l_alias = [];

        if (isset($_GET[C__CMDB__GET__OBJECT])) {
            $l_assigned_service_aliase = $p_cat->get_assigned_service_aliase($_GET[C__CMDB__GET__OBJECT]);

            while ($l_row_service_alias = $l_assigned_service_aliase->get_row()) {
                $l_alias[] = (int)$l_row_service_alias['isys_service_alias__id'];
            }
        }

        // Service aliase
        $l_rules["C__CMDB__CATG__SERVICE__ALIAS"] = [
            'p_strTable'      => 'isys_service_alias',
            'placeholder'     => isys_application::instance()->container->get('language')
                ->get('LC__CMDB__CATG__SERVICE__ALIASE'),
            'p_onComplete'    => "idoit.callbackManager.triggerCallback('cmdb-catg-service-alias-update', selected);",
            'p_strSelectedID' => implode(',', $l_alias),
            'multiselect'     => true
        ];

        // Apply rules.
        $this->get_template_component()
            ->smarty_tom_add_rules("tom.content.bottom.content", $l_rules);
    }
}

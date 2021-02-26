<?php

/**
 * i-doit
 *
 * CMDB UI: global category for the ticketing connector
 *
 * @package    i-doit
 * @subpackage CMDB_Categories
 * @author     Steven Bohm <sbohm@synetics.de>
 * @author     Selcuk Kekec <skekec@synetics.de>
 * @author     Benjamin Heisig <bheisig@synetics.de>
 * @author     Dennis Stücken <dstuecken@synetics.de>
 * @copyright  synetics GmbH
 * @license    http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_ui_category_g_virtual_tickets extends isys_cmdb_ui_category_g_virtual
{
    /**
     * Gets tickets by object identifier.
     *
     * @param   isys_connector_ticketing $connector
     * @param   integer                  $objectId
     *
     * @return  array
     */
    private static function get_tickets($connector, $objectId)
    {
        $return = [];
        $objectTickets = $connector->get_tickets_by_cmdb_object($objectId);

        // String indicating "Value is unspecified"
        $unspecifiedString = '<i>' . isys_application::instance()->container->get('language')->get('LC__UNIVERSAL__NOT_SPECIFIED') . '</i>';

        foreach ($objectTickets as $ticket) {
            $subject = $ticket['subject'] ?: $unspecifiedString;
            $return[$ticket['id']] = [
                'subject'        => $subject,
                'created'        => $ticket['created'] ?: $unspecifiedString,
                'owner'          => $ticket['owner'] ?: $unspecifiedString,
                'requestor'      => $ticket['requestors'] ?: $unspecifiedString,
                'starts'         => $ticket['start_time'] ?: $ticket['starts'] ?: $unspecifiedString,
                'started'        => $ticket['started'],
                'lastupdated'    => $ticket['last_updated'] ?: $ticket['lastupdated'] ?: $unspecifiedString,
                'priority'       => $ticket['priority'] ?: $unspecifiedString,
                'queue'          => $ticket['queue'] ?: $unspecifiedString,
                'status'         => $ticket['status'] ?: $unspecifiedString,
                'customcategory' => $ticket['custom_fields']['kategorie'] ?: $unspecifiedString,
                'customobjects'  => substr($ticket['custom_fields']['i-doit objects'], 1, -1),
                'custompriority' => $ticket['custom_fields']['priority'] ?: $unspecifiedString,
                'link'           => $connector->get_ticket_url($ticket['id']),
                'subjectsort'   => self::prepareSubjectSort($subject)
            ];
        }

        return $return;
    }

    /**
     * Processes view/edit mode.
     *
     * @param isys_cmdb_dao_category $p_cat Category's DAO
     */
    public function process(isys_cmdb_dao_category $p_cat)
    {
        $database = isys_application::instance()->container->get('database');
        $language = isys_application::instance()->container->get('language');

        isys_component_template_navbar::getInstance()
            ->set_active(false, C__NAVBAR_BUTTON__EDIT)
            ->set_active(false, C__NAVBAR_BUTTON__NEW);

        try {
            $l_tickets = [];
            $l_dao_tts = isys_tts_dao::instance($database);
            $l_dao = isys_cmdb_dao_category_g_assigned_logical_unit::instance($database);
            $ticketConnector = $l_dao_tts->get_connector();

            // Get ticket over AJAX.
            if (isset($_POST['get_ticket'])) {
                $l_ticket = $ticketConnector->get_ticket($_POST['get_ticket']);
                $l_ticket['link'] = $ticketConnector->get_ticket_url($l_ticket['id']);
                echo isys_format_json::encode($l_ticket);
            }

            $l_workstation = null;
            $objectId = (int)$_GET[C__CMDB__GET__OBJECT];

            if ($objectId > 0) {
                //Check Objecttype of our object
                $objectRow = $l_dao->get_object_by_id($objectId)
                    ->get_row();

                if (isys_tenantsettings::get('cmdb.logical-location.object-type-filter', 1)) {
                    $check = $objectRow['isys_obj__isys_obj_type__id'] != defined_or_default('C__OBJTYPE__WORKSTATION');
                } else {
                    $workstationObjectTypeIds = $l_dao->getObjTypeIdsByCategory('C__CATG__ASSIGNED_LOGICAL_UNIT');
                    $check = !in_array($objectRow['isys_obj__isys_obj_type__id'], $workstationObjectTypeIds);
                }

                if ($check) {
                    $l_tickets = self::get_tickets($ticketConnector, $objectId);
                } else {
                    $l_workstation = [
                        'object_id'    => $objectRow['isys_obj__id'],
                        'object_title' => $objectRow['isys_obj__title'],
                        'object_type'  => $language->get($objectRow['isys_obj_type__title']),
                        'tickets'      => self::get_tickets($ticketConnector, $objectId)
                    ];

                    // Retrieve workstation components.
                    $componentsResult = $l_dao->get_selected_objects($objectId);

                    while ($componentRow = $componentsResult->get_row()) {
                        $l_workstation['components'][] = [
                            'object_id'    => $componentRow['isys_obj__id'],
                            'object_title' => $componentRow['isys_obj__title'],
                            'object_type'  => $language->get($componentRow['isys_obj_type__title']),
                            'tickets'      => self::get_tickets($ticketConnector, (int)$componentRow['isys_obj__id'])
                        ];
                    }
                }
            }

            if (is_array($l_tickets)) {
                foreach ($l_tickets as $l_index => $l_log_unit) {
                    if (isset($l_log_unit['tickets'][0])) {
                        unset($l_tickets[$l_index]);
                    }
                }
            }

            // Deactivate commentary.
            $this->deactivate_commentary();

            // Assign smarty parameters.
            $this->get_template_component()
                ->assign('tickets', $l_tickets)
                ->assign('workstation', $l_workstation)
                ->assign('ticket_new_url', $ticketConnector->create_new_ticket_url($objectId))
                ->assign('ajax_url', '?' . http_build_query($_GET, null, '&') . '&call=category');
        } catch (isys_exception_general $e) {
            $this->get_template_component()
                ->assign('tts_processing_error', $e->getMessage());
        }
    }

    /**
     * @param $subject
     *
     * @return string
     */
    private static function prepareSubjectSort($subject)
    {
        $return = '';
        preg_match_all('~.*?(\d+).*?~', $subject, $l_match);
        foreach ($l_match[1] as $position => $match) {
            $return .= str_replace($match, '', $l_match[0][$position]) . str_pad($match, 10, "0", STR_PAD_LEFT);
        }
        return $return;
    }
}

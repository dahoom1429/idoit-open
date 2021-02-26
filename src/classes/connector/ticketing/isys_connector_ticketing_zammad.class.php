<?php
/**
 * i-doit - Documentation and CMDB solution for IT environments
 *
 * This file is part of the i-doit framework. Modify at your own risk.
 *
 * Please visit http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3 for a full copyright and license information.
 *
 * @version     1.8.1
 * @package     i-doit
 * @author      synetics GmbH
 * @copyright   synetics GmbH
 * @url         http://www.i-doit.com
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */

/**
 * i-doit
 *
 * Zammad ticketing connector
 *
 * @package    i-doit
 * @subpackage Connector
 * @author     Christian Wally <cw@panic.at>
 * @copyright  don’t panic it-services og
 * @license    http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_connector_ticketing_zammad extends isys_connector_ticketing
{

    /**
     * HTTP Protocol
     *
     * @var isys_protocol_http
     */
    protected $m_protocol = null;

    /**
     * Response map
     * This mapping maps Zammad response values to an internally defined i-doit format.
     *
     * @var array
     */
    protected $m_response_map = [
        'id'                      => parent::C__FIELD__ID,
        'Queue'                   => parent::C__FIELD__QUEUE,
        'caller_id_friendlyname'  => parent::C__FIELD__OWNER,
        'caller_id_friendlyname2' => parent::C__FIELD__CREATOR,
        'status'                  => parent::C__FIELD__STATUS,
        'title'                   => parent::C__FIELD__SUBJECT,
        'priority'                => parent::C__FIELD__PRIORITY,
        'priority2'               => parent::C__FIELD__INITIALPRIORITY,
        'priority3'               => parent::C__FIELD__FINALPRIORITY,
        'Requestors'              => parent::C__FIELD__REQUESTORS,
        'Cc'                      => parent::C__FIELD__CC,
        'AdminCc'                 => parent::C__FIELD__ADMINCC,
        'start_date'              => parent::C__FIELD__CREATED,
        'start_date2'             => parent::C__FIELD__STARTS,
        'start_date3'             => parent::C__FIELD__STARTED,
        'Due'                     => parent::C__FIELD__DUE,
        'Resolved'                => parent::C__FIELD__RESOLVED,
        'Told'                    => parent::C__FIELD__TOLD,
        'last_update'             => parent::C__FIELD__LASTUPDATED,
        'TimeEstimated'           => parent::C__FIELD__TIMEESTIMATED,
        'TimeWorked'              => parent::C__FIELD__TIMEWORKED,
        'TimeLeft'                => parent::C__FIELD__TIMELEFT
    ];

    /**
     * @var string
     */
    private $m_rt_base_url = null;

    /**
     * @var string
     */
    //'/api/v1/tickets/search?query=preferences.idoit_ci_ids%3A6&limit=10&expand=true'
    //private $m_ticket_url_pattern = '/pages/UI.php?operation=details&class=UserRequest&id=%s&c[org_id]=1&c[menu]=UserRequest';
    private $m_ticket_url_pattern = '#ticket/zoom/%s';

    /**
     * Base URL of the Rest interface
     *
     * @var string
     */
    private $m_url_base = '/api/v1/tickets/search';

    /**
     * Set the ticketsystem base url
     *
     * @param int $p_object_id Object identifier
     *
     * @return string
     *
     * @todo change method name
     */
    public function create_new_ticket_url($p_object_id)
    {
        return [
            'url' => $this->m_rt_base_url . '/#ticket/create/query/idoit_object_ids=' . $p_object_id
        ];
    }

    public function get_attachment_content($p_attachment_id)
    {
    }

    public function get_attachments($p_ticket_id)
    {
    }

    public function get_comments($p_ticket_id)
    {
    }

    /**
     * Get ticket history by ticket id
     *
     * @param string|int $p_ticket_id
     * @param string     $p_history_type
     *
     * @return array
     */
    public function get_history($p_ticket_id, $p_history_type = null)
    {
        if (!empty($p_ticket_id)) {
            return $this->generic_request('ticket/' . $p_ticket_id . '/history?format=l');
        }

        return false;
    }

    public function get_queue($p_queue_id = null)
    {
    }

    /**
     * Returns tickets (or only one ticket by id)
     *
     * @param int $p_ticket_id
     *
     * @return string
     */
    public function get_ticket($p_ticket_id)
    {
        /**
         * TODO: Needed for workstations
         */
    }

    /**
     * @param $p_ticketID
     *
     * @return string
     */
    public function get_ticket_url($p_ticketID)
    {
        $port = (int)$this->m_protocol->get_port();
        $urlPort = '';

        if ($port && $port !== 80) {
            $urlPort = ':' . $port;
        }

        return $this->m_protocol->get_host() . $urlPort . sprintf($this->m_ticket_url_pattern, $p_ticketID);
    }

    public function get_tickets($p_tickets)
    {
        // Not used in code
    }

    /**
     * Fetches tickets by CMDB object identifier
     *
     * @param int $p_object_id Object identifier
     *
     * @return array
     */
    public function get_tickets_by_cmdb_object($p_object_id)
    {
        // Build parameter array
        $l_params = [
            //ToDo: change comments and comment out
            'query'  => 'preferences.idoit.object_ids:' . $p_object_id,
            'limit'  => 10,
            'expand' => true
        ];

        // Perform a generic request and get the desired tickets for the ci
        return $this->generic_request($l_params);
    }

    /**
     * Get ticket(s) by status
     *
     * @param mixed $p_status
     *
     * @return string
     */
    public function get_tickets_by_status($p_status)
    {
        // Not used in code
    }

    public function get_users($p_user_id = null)
    {
    }

    public function login($p_user, $p_pass)
    {
    }

    public function logout()
    {
    }

    public function search($p_params)
    {
    }

    /**
     * Fetches tickets by a list of custom fields.
     *
     * @param array  $p_custom_fields Custom fields. Associative array with cf
     *                                names as keys and the destinated values as values.
     * @param string $p_order_by      (optional)
     *                                By this parameter you can change the sort field and order of the search result. To sort a list
     *                                ascending just put a + before the fieldname, otherwise a -. Eg: -Created (will put the newest
     *                                tickets at the beginning). Defaults to null (no ordering).
     *
     * @return array
     */
    public function get_tickets_by_custom_field($p_custom_fields, $p_order_by = null)
    {
        // Not needed
    }

    /**
     * Get ticket links
     *
     * @param int $p_ticket_id
     */
    public function get_ticket_links($p_ticket_id)
    {
        // Not used in code
    }

    /**
     * Get request (e.g. get available tickets)
     *
     * @param array $p_params
     *
     * @return string
     */
    private function request($p_params = [])
    {
        return $this->m_protocol->post(
            null,
            $p_params
//            [
//                'version'   => '1.0',
//                'auth_user' => $this->m_user,
//                'auth_pwd'  => $this->m_pass,
//                'json_data' => isys_format_json::encode($p_params)
//            ]
        );
    }

    /**
     * Formats a Zammad result
     *
     * @param string $p_raw_data
     *
     * @return array
     * @throws isys_exception_general
     */
    private function format_response($p_raw_data)
    {
        // Check for a valid json response first
        if (isys_format_json::is_json($p_raw_data)) {
            // Encode charset and notation
            $p_raw_data = isys_format_json::decode($p_raw_data);
            $l_result = [];
            foreach ($p_raw_data as $l_value) {
                $l_result[$l_value['id']]['id'] = $l_value['id'];
                $l_result[$l_value['id']]['subject'] = $l_value['title'];
                $l_result[$l_value['id']]['created'] = $l_value['created_at'];
                $l_result[$l_value['id']]['starts'] = $l_value['created_at'];
                $l_result[$l_value['id']]['owner'] = $l_value['owner'];
                $l_result[$l_value['id']]['requestors'] = $l_value['customer'];
                $l_result[$l_value['id']]['lastupdated'] = $l_value['updated_at'];
                $l_result[$l_value['id']]['status'] = $l_value['state'];
                $l_result[$l_value['id']]['queue'] = $l_value['group'];
                $l_result[$l_value['id']]['priority'] = $l_value['priority'];
                if (is_array($l_value['preferences']['idoit_ci_ids'])) {
                    $l_result[$l_value['id']]['custom_fields']['i-doit objects'] = implode(' ', $l_value['preferences']['idoit_ci_ids']);
                } else {
                    $l_result[$l_value['id']]['custom_fields']['i-doit objects'] = $l_value['preferences']['idoit_ci_ids'];
                }
            }
        } else {
            throw new isys_exception_general('Zammad API response is not valid.');
        }

        return $this->map_response($l_result);
    }

    /**
     * Prepare a generic post request
     *
     * @param $p_params
     *
     * @return string
     * @throws isys_exception_general
     * @internal param string $p_url
     */
    private function generic_request($p_params)
    {
        $l_raw_data = $this->request($p_params);

        if ($l_raw_data) {
            return $this->format_response($l_raw_data);
        }

        return [];
    }

    /**
     * Injects the used protocol and sets RT's base url.
     *
     * @param isys_protocol $p_protocol
     */
    public function __construct($p_protocol)
    {
        parent::__construct($p_protocol);

        $this->m_ticket_url_pattern = $this->m_protocol->get_base_url() . $this->m_ticket_url_pattern;
        $this->m_rt_base_url = $this->m_protocol->get_host() . $this->m_protocol->get_base_url();
        $this->m_protocol->attach_base_url($this->m_url_base);
    }

    /**
     * Sets the password
     *
     * @param string $p_user
     *
     * @return isys_connector_ticketing
     */
    public function set_pass($p_user)
    {
        $this->m_protocol->set_pass($p_user);

        return $this;
    }

    /**
     * Sets the user
     *
     * @param string $p_user
     *
     * @return isys_connector_ticketing
     */
    public function set_user($p_user)
    {
        $this->m_protocol->set_user($p_user);

        return $this;
    }
}

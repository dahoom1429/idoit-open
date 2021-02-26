<?php

/**
 * i-doit
 *
 * Request Tracker (RT) ticketing connector
 *
 * Documentation: http://requesttracker.wikia.com/wiki/REST
 *
 * @package    i-doit
 * @subpackage Connector
 * @author     Steven Bohm <sbohm@synetics.de>
 * @author     Dennis Stücken <dstuecken@synetics.de>
 * @copyright  synetics GmbH
 * @license    http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_connector_ticketing_rt extends isys_connector_ticketing
{

    /**
     * Some RT constants
     */
    const C__ENTRYTYPE__CREATE             = 'Create';
    const C__ENTRYTYPE__CUSTOMFIELD        = 'CustomField';
    const C__ENTRYTYPE__EMAILRECORD        = 'EmailRecord';
    const C__ENTRYTYPE__STATUS             = 'Status';
    const C__ENTRYTYPE__COMMENTEMAILRECORD = 'CommentEmailRecord';
    const C__ENTRYTYPE__CORRESPOND         = 'Correspond';
    const C__ENTRYTYPE__COMMENT            = 'Comment';
    const C__ENTRYTYPE__PRIORITY           = 'Priority';
    const C__ENTRYTYPE__GIVE               = 'Give';
    const C__ENTRYTYPE__STEAL              = 'Steal';
    const C__ENTRYTYPE__TAKE               = 'Take';
    const C__ENTRYTYPE__UNTAKE             = 'Untake';
    const C__ENTRYTYPE__ADDWATCHER         = 'AddWatcher';
    const C__ENTRYTYPE__DELETEWATCHER      = 'DeleteWatcher';
    const C__ENTRYTYPE__ADDLINK            = 'AddLink';
    const C__ENTRYTYPE__DELETELINK         = 'DeleteLink';
    const C__ENTRYTYPE__ADDREMINDER        = 'AddReminder';
    const C__ENTRYTYPE__OPENREMINDER       = 'OpenReminder';
    const C__ENTRYTYPE__RESOLVEREMINDER    = 'ResolveReminder';
    const C__ENTRYTYPE__SET                = 'Set';
    const C__ENTRYTYPE__FORCE              = 'Force';
    const C__ENTRYTYPE__SUBJECT            = 'Subject';
    const C__ENTRYTYPE__TOLD               = 'Told';
    const C__ENTRYTYPE__PURGETRANSACTION   = 'PurgeTransaction';
    const C__ENTRYTYPE__STARTS             = 'Starts';
    /**
     * Possible ticket status
     */
    const C__TICKETSTATUS__NEW      = 'new';
    const C__TICKETSTATUS__OPEN     = 'open';
    const C__TICKETSTATUS__STALLED  = 'stalled';
    const C__TICKETSTATUS__RESOLVED = 'resolved';
    const C__TICKETSTATUS__REJECTED = 'rejected';
    const C__TICKETSTATUS__DELETED  = 'deleted';

    protected $m_mandator = 'i-doit tenant';

    /**
     * Custom field for object id reference inside RT
     *
     * @var string
     */
    protected $m_object_field = 'i-doit objects';

    /**
     * HTTP Protocol
     *
     * @var isys_protocol_http
     */
    protected $m_protocol = null;

    /**
     * Response map
     * This mapping maps RT response values to an internally defined i-doit format.
     *
     * @var array
     */
    protected $m_response_map = [
        'Id'              => parent::C__FIELD__ID,
        'Queue'           => parent::C__FIELD__QUEUE,
        'Owner'           => parent::C__FIELD__OWNER,
        'Creator'         => parent::C__FIELD__CREATOR,
        'Status'          => parent::C__FIELD__STATUS,
        'Subject'         => parent::C__FIELD__SUBJECT,
        'Priority'        => parent::C__FIELD__PRIORITY,
        'InitialPriority' => parent::C__FIELD__INITIALPRIORITY,
        'FinalPriority'   => parent::C__FIELD__FINALPRIORITY,
        'Requestors'      => parent::C__FIELD__REQUESTORS,
        'Cc'              => parent::C__FIELD__CC,
        'AdminCc'         => parent::C__FIELD__ADMINCC,
        'Created'         => parent::C__FIELD__CREATED,
        'Starts'          => parent::C__FIELD__STARTS,
        'Started'         => parent::C__FIELD__STARTED,
        'Due'             => parent::C__FIELD__DUE,
        'Resolved'        => parent::C__FIELD__RESOLVED,
        'Told'            => parent::C__FIELD__TOLD,
        'LastUpdated'     => parent::C__FIELD__LASTUPDATED,
        'TimeEstimated'   => parent::C__FIELD__TIMEESTIMATED,
        'TimeWorked'      => parent::C__FIELD__TIMEWORKED,
        'TimeLeft'        => parent::C__FIELD__TIMELEFT
    ];

    /**
     * Base URL of the Rest interface
     *
     * @var string
     */
    private $m_rt_base = '/REST/1.0/';

    /**
     * @var string
     */
    private $m_rt_base_url = null;

    /**
     * @var string
     */
    private $m_ticket_url_pattern = '/Ticket/Display.html?id=%s';

    /**
     * Get ticket links
     *
     * @param int $p_ticket_id
     */
    public function get_ticket_links($p_ticket_id)
    {
        return $this->generic_request('ticket/' . (int)$p_ticket_id . '/links/show');
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
        $l_queries = [];
        foreach ($p_custom_fields as $l_key => $l_value) {
            $l_queries[] = "'CF.{" . $l_key . "}' = '" . $l_value . "'";
        }
        $l_order_condition = '';
        $l_format_condition = '';
        if (isset($p_order_by)) {
            $l_order_condition = '&orderby=' . $p_order_by;
        }
        $l_format_condition = '&format=l';

        $l_url = 'search/ticket?query=' . urlencode(implode(' AND ', $l_queries)) . $l_order_condition . $l_format_condition;
        $l_request = $this->generic_request($l_url);

        if (!is_array($l_request) || count($l_request) == 0 || (is_array($l_request[0]) && isset($l_request[0]['no matching results.']))) {
            return [];
        }

        return $l_request;
    }

    /**
     * Set the ticketsystem base url.
     *
     * @param   integer $p_object_id Object identifier
     *
     * @return  string
     * @throws  isys_exception_general
     * @todo change method name
     */
    public function create_new_ticket_url($p_object_id)
    {
        global $g_comp_session;

        $l_queueSettings = explode(',', isys_settings::get('tts.rt.queues', 'General'));

        if (!is_array($l_queueSettings)) {
            throw new isys_exception_general('There are no RT queues configured.');
        }

        $l_queues = [];
        foreach ($l_queueSettings as $l_queue) {
            $l_queues[$l_queue] = $l_queue;
        }

        $l_ticket_new_url = [
            'url'          => str_replace('/REST/1.0/', '', $this->m_protocol->get_url()) . "/Ticket/Create.html?IDoitTenant=" . $g_comp_session->get_mandator_id() .
                "&IDoitObjects=" . $p_object_id . "&Queue=",
            'use_queue'    => 1,
            'select_queue' => $l_queues
        ];

        return $l_ticket_new_url;
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
        if (isset($p_ticket_id) && $p_ticket_id > 0) {
            return $this->generic_request('ticket/' . (int)$p_ticket_id . '/show');
        }
    }

    /**
     * @param $p_ticketID
     *
     * @return string
     */
    public function get_ticket_url($p_ticketID)
    {
        $port = (int) $this->m_protocol->get_port();
        $urlPort = '';

        if ($port && $port !== 80) {
            $urlPort = ':' . $port;
        }

        return $this->m_protocol->get_host() . $urlPort . sprintf($this->m_ticket_url_pattern, $p_ticketID);
    }

    public function get_tickets($p_tickets)
    {
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
        global $g_comp_session;

        $l_custom_fields = [
            $this->m_object_field => $p_object_id,
            $this->m_mandator     => $g_comp_session->get_mandator_id()
        ];

        return $this->get_tickets_by_custom_field($l_custom_fields);
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
        if (is_string($p_status)) {
            return $this->generic_request('search/ticket?query=Status=\'' . addslashes($p_status) . '\'');
        } elseif (is_array($p_status)) {
            $l_query = '';
            foreach ($p_status as $l_status) {
                $l_query .= 'Status=\'' . addslashes($l_status) . '\' AND ';
            }

            return $this->generic_request('search/ticket?query=' . substr($l_query, 0, strlen($l_query) - 4));
        }
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
     * @param       $p_queue
     * @param       $p_requestor
     * @param       $p_subject
     * @param       $p_cc
     * @param       $p_admincc
     * @param       $p_owner
     * @param       $p_status
     * @param       $p_priority
     * @param       $p_initial_priority
     * @param       $p_final_priority
     * @param       $p_estimated_time
     * @param       $p_starts
     * @param       $p_due
     * @param       $p_text
     * @param array $p_customfields
     *
     * @return string
     */
    public function create_ticket(
        $p_queue,
        $p_requestor,
        $p_subject,
        $p_cc,
        $p_admincc,
        $p_owner,
        $p_status,
        $p_priority,
        $p_initial_priority,
        $p_final_priority,
        $p_estimated_time,
        $p_starts,
        $p_due,
        $p_text,
        $p_customfields = []
    ) {
        $l_contentArr = [];

        if (is_array($p_queue)) {
            $l_post = $p_queue;
            $l_contentArr['id'] = 'ticket/new';
            foreach ($this->m_response_map as $l_rt_key => $l_const) {
                if (isset($l_post[$l_const]) && !empty($l_post[$l_const])) {
                    $l_contentArr[$l_rt_key] = $l_post[$l_const];
                }
            }
        } else {
            $l_contentArr['id'] = 'ticket/new';
            $l_contentArr['Queue'] = $p_queue;
            $l_contentArr['Requestor'] = $p_requestor;
            $l_contentArr['Subject'] = $p_subject;
            $l_contentArr['Cc'] = $p_cc;
            $l_contentArr['AdminCc'] = $p_admincc;
            $l_contentArr['Owner'] = $p_owner;
            $l_contentArr['Status'] = $p_status;
            $l_contentArr['Priority'] = $p_priority;
            $l_contentArr['InitialPriority'] = $p_initial_priority;
            $l_contentArr['FinalPriority'] = $p_final_priority;
            $l_contentArr['TimeEstimated'] = $p_estimated_time;
            $l_contentArr['Starts'] = $p_starts;
            $l_contentArr['Due'] = $p_due;
            $l_contentArr['Text'] = $p_text;
        }

        //Add customfields
        if (is_array($p_customfields)) {
            foreach ($p_customfields as $l_key => $l_value) {
                $l_contentArr["CF.{" . $l_key . "}"] = $l_value;
            }
        }

        if (!empty($l_contentArr)) {
            return $this->submit('ticket/new', $l_contentArr);
        }
    }

    /**
     * @param       $p_id
     * @param       $p_queue
     * @param       $p_requestor
     * @param       $p_subject
     * @param       $p_cc
     * @param       $p_admincc
     * @param       $p_owner
     * @param       $p_status
     * @param       $p_priority
     * @param       $p_initial_priority
     * @param       $p_final_priority
     * @param       $p_estimated_time
     * @param       $p_starts
     * @param       $p_due
     * @param       $p_text
     * @param array $p_customfields
     *
     * @return string
     */
    public function edit_ticket(
        $p_id,
        $p_queue,
        $p_requestor,
        $p_subject,
        $p_cc,
        $p_admincc,
        $p_owner,
        $p_status,
        $p_priority,
        $p_initial_priority,
        $p_final_priority,
        $p_estimated_time,
        $p_starts,
        $p_due,
        $p_text,
        $p_customfields = []
    ) {
        $l_contentArr = [];

        if (!empty($p_id)) {
            if (is_array($p_queue)) {
                $l_post = $p_queue;
                $l_contentArr['id'] = 'ticket/new';
                foreach ($this->m_response_map as $l_rt_key => $l_const) {
                    if (isset($l_post[$l_const]) && !empty($l_post[$l_const])) {
                        $l_contentArr[$l_rt_key] = $l_post[$l_const];
                    }
                }
            } else {
                $l_contentArr['id'] = $p_id;
                $l_contentArr['Queue'] = $p_queue;
                $l_contentArr['Requestor'] = $p_requestor;
                $l_contentArr['Subject'] = $p_subject;
                $l_contentArr['Cc'] = $p_cc;
                $l_contentArr['AdminCc'] = $p_admincc;
                $l_contentArr['Owner'] = $p_owner;
                $l_contentArr['Status'] = $p_status;
                $l_contentArr['Priority'] = $p_priority;
                $l_contentArr['InitialPriority'] = $p_initial_priority;
                $l_contentArr['FinalPriority'] = $p_final_priority;
                $l_contentArr['TimeEstimated'] = $p_estimated_time;
                $l_contentArr['Starts'] = $p_starts;
                $l_contentArr['Due'] = $p_due;
                $l_contentArr['Text'] = $p_text;
            }

            //Add customfields
            if (is_array($p_customfields)) {
                foreach ($p_customfields as $l_key => $l_value) {
                    $l_contentArr["CF.{" . $l_key . "}"] = $l_value;
                }
            }

            if (!empty($l_contentArr)) {
                return $this->submit('ticket/' . (int)$p_id . '/edit', $l_contentArr);
            }
        }
    }

    /**
     * Get request (e.g. get available tickets)
     *
     * @param string $p_url
     * @param array  $p_params
     *
     * @return string
     */
    private function get($p_url, $p_params = [])
    {
        return $this->m_protocol->get($p_url, array_merge($p_params, [
            'user' => $this->m_user,
            'pass' => $this->m_pass
        ]));
    }

    /**
     * Post (e.g. create tickets)
     *
     * @param string $p_url
     * @param array  $p_params
     *
     * @return string
     */
    private function submit($p_url, $p_params = [])
    {
        $l_content = '';
        if (is_array($p_params)) {
            foreach ($p_params as $l_key => $l_value) {
                if (!empty($l_value)) {
                    $l_content .= $l_key . ': ' . $l_value . "\n";
                }
            }
        } else {
            $l_content = $p_params;
        }

        $p_url .= strpos($p_url, '?') === 0 ? '&' : '?';
        $p_url .= 'user=' . $this->m_user . '&pass=' . $this->m_pass;

        return $this->m_protocol->get($p_url, [
            'content' => $l_content
        ]);
    }

    /**
     * Formats an RT result
     *
     * @param string $p_raw_data
     *
     * @return array
     * @throws isys_exception_general
     */
    private function format_response($p_raw_data)
    {
        $l_result = [];

        $l_blocks = explode("\n\n", $p_raw_data);

        if (count($l_blocks) === 0) {
            throw new isys_exception_general('RT response invalid.');
        }

        // Iterate through each block:
        $l_count = 0;
        foreach ($l_blocks as $l_block) {
            // Check response whether it's valid:
            if ($l_count === 0) {
                $l_register = [];
                if (preg_match('/^RT\/([\.0-9]+) ([0-9][0-9][0-9]) (.*?)$/i', $l_block, $l_register)) {
                    $l_version = null;
                    $l_status_code = null;
                    $l_status = null;
                    list(, $l_version, $l_status_code, $l_status) = $l_register;

                    /* Check for some errors */
                    if ($l_status_code != 200) {
                        throw new isys_exception_general('Could not send request to RT version ' . $l_version . ': ' . $l_status . ' (Status code: ' . $l_status_code . ')');
                    }
                } else {
                    throw new isys_exception_general('RT response header is invalid.');
                }

                $l_count++;
                continue;
            }

            // Ignore ticket separator:
            if ($l_block === '--') {
                continue;
            }

            // Handle tickets:
            $l_ticket_properties = explode("\n", $l_block);

            $l_ticket = [];
            $l_ticket_id = 0;

            // Iterate through each property block:
            foreach ($l_ticket_properties as $l_property) {
                list($l_property_key, $l_property_value) = explode(': ', $l_property);

                // Skip comments:
                if (substr($l_property_key, 0, 1) === '#') {
                    continue;
                }

                $l_property_key = strtolower($l_property_key);

                if (strpos($l_property_key, 'cf.') === 0) {
                    // Custom field:
                    $l_ticket['custom_fields'][substr($l_property_key, 4, -1)] = $l_property_value;
                } elseif ($l_property_key === 'id') {
                    // Identifier:
                    $l_ticket_id = str_replace('ticket/', '', $l_property_value);
                    $l_ticket[$l_property_key] = $l_ticket_id;
                } else {
                    // Everything else:
                    $l_ticket[$l_property_key] = $l_property_value;
                }
            }

            $l_result[$l_ticket_id] = $l_ticket;
        }

        return $this->map_response($l_result);
    }

    /**
     * Prepare a generic get request
     *
     * @param string $p_url
     *
     * @return string
     */
    private function generic_request($p_url)
    {
        $l_raw_data = $this->get($p_url);
        if ($l_raw_data) {
            return $this->format_response($l_raw_data);
        }
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
        $this->m_protocol->attach_base_url($this->m_rt_base);
    }
}

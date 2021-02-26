<?php

/**
 * i-doit
 *
 * Open-source Ticket Request System (OTRS) ticketing connector.
 *
 * @package    i-doit
 * @subpackage Connector
 * @author     Benjamin Heisig <bheisig@synetics.de>
 * @author     Steven Bohm <sbohm@synetics.de>
 * @copyright  synetics GmbH
 * @license    http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_connector_ticketing_otrs extends isys_connector_ticketing
{

    /**
     * Some OTRS constants
     *
     * @todo This is out-dated RT stuff...
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
     *
     * @todo This is out-dated RT stuff...
     */
    const C__TICKETSTATUS__NEW      = 'new';
    const C__TICKETSTATUS__OPEN     = 'open';
    const C__TICKETSTATUS__STALLED  = 'stalled';
    const C__TICKETSTATUS__RESOLVED = 'resolved';
    const C__TICKETSTATUS__REJECTED = 'rejected';
    const C__TICKETSTATUS__DELETED  = 'deleted';

    /**
     * Base URL of the SOAP interface
     *
     * @var string
     */
    protected $m_base_url = '/nph-genericinterface.pl/Webservice/GenericTicketConnector';

    protected $m_mandator = 'IDoitMandator';

    /**
     * Custom field for object id reference inside OTRS
     *
     * @todo This is out-dated RT stuff...
     *
     * @var string
     */
    protected $m_object_field = 'IDoitObjects';

    /**
     * @var string
     */
    protected $m_otrs_base_url = null;

    protected $m_protocol = null;

    /**
     * Response map
     * This mapping maps OTRS response values to an internally defined i-doit format.
     *
     * @todo This is out-dated RT stuff...
     *
     * @var array
     */
    protected $m_response_map = [
        'TicketID'               => parent::C__FIELD__ID,
        'CreateBy'               => parent::C__FIELD__CREATOR,
        'Created'                => parent::C__FIELD__CREATED,
        'EscalationResponseTime' => parent::C__FIELD__TIMEESTIMATED,
        'EscalationSolutionTime' => parent::C__FIELD__TIMEWORKED,
        'EscalationTime'         => parent::C__FIELD__STARTS,
        'EscalationUpdateTime'   => parent::C__FIELD__LASTUPDATED,
        'Owner'                  => parent::C__FIELD__OWNER,
        'Priority'               => parent::C__FIELD__PRIORITY,
        'Queue'                  => parent::C__FIELD__QUEUE,
        'State'                  => parent::C__FIELD__STATUS,
        'Title'                  => parent::C__FIELD__SUBJECT
    ];

    /**
     * @var string
     */
    protected $m_ticket_url_pattern = '/index.pl?Action=AgentTicketZoom;TicketID=%s';

    /**
     * Get ticket links
     *
     * @param int $p_ticket_id
     *
     * @todo This is out-dated RT stuff...
     */
    public function get_ticket_links($p_ticket_id)
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
    }

    /**
     * Set the ticketsystem base url.
     *
     * @param   integer $p_object_id Object identifier
     *
     * @return  string
     */
    public function create_new_ticket_url($p_object_id)
    {
        $l_ticket_new_url = [
            'url'          => $this->m_otrs_base_url . "/index.pl?DynamicField_IDoitObjects=," . $p_object_id . ",&Action=",
            'use_queue'    => 1,
            'select_queue' => [
                'AgentTicketEmail' => isys_application::instance()->container->get('language')
                    ->get('LC__TTS__OTRS_EMAIL_TICKET'),
                'AgentTicketPhone' => isys_application::instance()->container->get('language')
                    ->get('LC__TTS__OTRS_PHONE_TICKET')

            ]
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
     * @param   mixed  $p_ticket_id
     * @param   string $p_history_type
     *
     * @return  array
     */
    public function get_history($p_ticket_id, $p_history_type = null)
    {
    }

    public function get_queue($p_queue_id = null)
    {
    }

    public function get_ticket($p_ticket_id)
    {
        $l_ticket_params = [
            'args'  => [
                new SoapParam($this->m_user, "UserLogin"),
                new SoapParam($this->m_pass, "Password"),
                new SoapParam($p_ticket_id, 'TicketID'),
                new SoapParam(1, 'DynamicFields'),

            ],
            'uri'   => 'Core',
            'style' => 'SOAP_RPC',
            'use'   => 'SOAP_ENCODED',

            'location' => ''
        ];

        $l_method = "TicketGet";
        $l_result = ['Ticket' => [$this->m_protocol->request($l_method, $l_ticket_params)]];

        return $this->format_response($l_result);
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

    /**
     * Returns tickets (or only one ticket by id)
     *
     * @param array $p_tickets
     *
     * @return array
     *
     */
    public function get_tickets($p_tickets)
    {
        $l_ticket_id_string = implode(',', $p_tickets['TicketID']);

        $l_ticket_params = [
            'args'  => [
                new SoapParam($this->m_user, "UserLogin"),
                new SoapParam($this->m_pass, "Password"),
                new SoapParam($l_ticket_id_string, 'TicketID'),
                new SoapParam(1, 'DynamicFields'),

            ],
            'uri'   => 'Core',
            'style' => 'SOAP_RPC',
            'use'   => 'SOAP_ENCODED',

            'location' => ''
        ];

        $l_method = "TicketGet";
        $l_result = $this->m_protocol->request($l_method, $l_ticket_params);

        return $this->format_response($l_result);
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
        $l_ticket_params = [
            'args'     => [
                new SoapParam($this->m_user, 'UserLogin'),
                new SoapParam($this->m_pass, 'Password'),
                new SoapVar('<DynamicField_IDoitObjects><Like>*,' . $p_object_id . ',*</Like></DynamicField_IDoitObjects>', XSD_ANYXML)
            ],
            'uri'      => 'Core',
            'style'    => 'SOAP_RPC',
            'use'      => 'SOAP_ENCODED',
            'location' => ''
        ];

        $l_method = 'TicketSearch';

        $l_result = $this->m_protocol->request($l_method, $l_ticket_params);

        if (is_array($l_result)) {
            return $this->get_tickets($l_result);
        } elseif ($l_result === null) {
            return [];
        } else {
            return $this->get_ticket($l_result);
        }
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
    }

    public function get_users($p_user_id = null)
    {
    }

    public function login($p_user, $p_pass)
    {
        $this->m_protocol->set_user($p_user);
        $this->m_protocol->set_pass($p_pass);
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
     *
     * @todo This is out-dated RT stuff...
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
     *
     * @todo This is out-dated RT stuff...
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
            'user'     => $this->m_user,
            'password' => $this->m_pass
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
     *
     * @todo Write me :-)
     */
    private function format_response($p_raw_data)
    {
        $l_result = [];
        foreach ($p_raw_data['Ticket'] as $l_value) {
            $l_result[$l_value->TicketID]['id'] = $l_value->TicketID;
            $l_result[$l_value->TicketID]['subject'] = $l_value->Title;
            $l_result[$l_value->TicketID]['created'] = $l_value->Created;
            $l_result[$l_value->TicketID]['starts'] = $l_value->Created;
            $l_result[$l_value->TicketID]['owner'] = $l_value->Owner;
            $l_result[$l_value->TicketID]['requestors'] = $l_value->CustomerUserID;
            $l_result[$l_value->TicketID]['lastupdated'] = $l_value->Changed;
            $l_result[$l_value->TicketID]['status'] = $l_value->State;
            $l_result[$l_value->TicketID]['queue'] = $l_value->Queue;
            $l_result[$l_value->TicketID]['priority'] = $l_value->Priority;
            $l_result[$l_value->TicketID]['custom_fields']['i-doit objects'] = $l_value->DynamicField_IDoitObjects;
        }

        return $this->map_response($l_result);
    }

    /**
     * Injects the used protocol and sets OTRS's base url.
     *
     * @param isys_protocol $p_protocol
     */
    public function __construct($p_protocol)
    {
        parent::__construct($p_protocol);

        $this->m_ticket_url_pattern = $this->m_protocol->get_base_url() . $this->m_ticket_url_pattern;
        $this->m_otrs_base_url = $this->m_protocol->get_host() . $this->m_protocol->get_base_url();
        $this->m_protocol->attach_base_url($this->m_base_url);
    }
}

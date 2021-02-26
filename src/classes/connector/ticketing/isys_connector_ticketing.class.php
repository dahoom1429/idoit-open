<?php

/**
 * i-doit
 *
 * Ticketing connector
 *
 * @package    i-doit
 * @subpackage Connector
 * @author     Dennis Stücken <dstuecken@synetics.de>
 * @copyright  synetics GmbH
 * @license    http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
abstract class isys_connector_ticketing
{

    /**
     * Ticket fields
     *
     * This is an internal map of an i-doit conform ticket.
     */
    const C__FIELD__ID              = 'id';
    const C__FIELD__SUBJECT         = 'subject';
    const C__FIELD__QUEUE           = 'queue';
    const C__FIELD__OWNER           = 'owner';
    const C__FIELD__CREATOR         = 'creator';
    const C__FIELD__STATUS          = 'status';
    const C__FIELD__PRIORITY        = 'priority';
    const C__FIELD__INITIALPRIORITY = 'initial_priority';
    const C__FIELD__FINALPRIORITY   = 'final_priority';
    const C__FIELD__REQUESTORS      = 'requestors';
    const C__FIELD__CC              = 'cc';
    const C__FIELD__ADMINCC         = 'admincc';
    const C__FIELD__CREATED         = 'created';
    const C__FIELD__STARTS          = 'start_time';
    const C__FIELD__STARTED         = 'started';
    const C__FIELD__DUE             = 'due';
    const C__FIELD__RESOLVED        = 'resolved';
    const C__FIELD__TOLD            = 'told';
    const C__FIELD__LASTUPDATED     = 'last_updated';
    const C__FIELD__TIMEESTIMATED   = 'time_estimated';
    const C__FIELD__TIMEWORKED      = 'time_worked';
    const C__FIELD__TIMELEFT        = 'time_left';

    /**
     * Password
     *
     * @var string
     */
    protected $m_pass = null;

    /**
     * The protocol
     *
     * @var isys_protocol
     */
    protected $m_protocol = null;

    /**
     * Response map for automatic mapping of a response
     *
     * @var array
     */
    protected $m_response_map = [];

    /**
     * User
     *
     * @var string
     */
    protected $m_user = null;

    /**
     * @param $p_object_id
     *
     * @return mixed
     */
    abstract public function create_new_ticket_url($p_object_id);

    /**
     * @param $p_attachment_id
     *
     * @return array
     */
    abstract public function get_attachment_content($p_attachment_id);

    /**
     * @param $p_ticket_id
     *
     * @return array
     */
    abstract public function get_attachments($p_ticket_id);

    /**
     * @param $p_ticket_id
     *
     * @return array
     */
    abstract public function get_comments($p_ticket_id);

    /**
     * @param      $p_ticket_id
     * @param null $p_history_type
     *
     * @return array
     */
    abstract public function get_history($p_ticket_id, $p_history_type = null);

    /**
     * @param null $p_queue_id
     *
     * @return array
     */
    abstract public function get_queue($p_queue_id = null);

    /**
     * Abstract methods
     *
     * @return array
     * ---------------------------------------------------------------
     */
    abstract public function get_ticket($p_ticket_id);

    /**
     * @param $p_ticket_id
     *
     * @return string
     */
    abstract public function get_ticket_url($p_ticket_id);

    /**
     * @param $p_tickets
     *
     * @return array
     */
    abstract public function get_tickets($p_tickets);

    /**
     * @param $p_object_id
     *
     * @return array
     */
    abstract public function get_tickets_by_cmdb_object($p_object_id);

    /**
     * @param $p_status
     *
     * @return array
     */
    abstract public function get_tickets_by_status($p_status);

    /**
     * @param null $p_user_id
     *
     * @return array
     */
    abstract public function get_users($p_user_id = null);

    /**
     * @param $p_user
     * @param $p_pass
     *
     * @return bool
     */
    abstract public function login($p_user, $p_pass);

    /**
     * @return bool
     */
    abstract public function logout();

    /**
     * @param $p_params
     *
     * @return array
     */
    abstract public function search($p_params);

    /**
     * Sets the password
     *
     * @param string $p_user
     *
     * @return isys_connector_ticketing
     */
    public function set_pass($p_user)
    {
        $this->m_pass = $p_user;

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
        $this->m_user = $p_user;

        return $this;
    }

    /**
     * Maps the response to the internal i-doit format
     *
     * @param array $p_response
     *
     * @return array
     */
    protected function map_response($p_response)
    {

        $l_mapped_response = [];

        if (is_array($p_response)) {

            /* Iterate through response and map */
            foreach ($p_response as $l_property_key => $l_property_value) {

                if (isset($this->m_response_map[$l_property_key])) {
                    $l_mapped_response[$this->m_response_map[$l_property_key]] = $l_property_value;
                } else {
                    $l_mapped_response[$l_property_key] = $l_property_value;
                }
            }
        }

        return $l_mapped_response;
    }
    /* --------------------------------------------------------------- */

    /**
     * Inject the protocl to use
     *
     * @param isys_protocol $p_protocol
     *
     * @throws isys_exception_general
     */
    public function __construct($p_protocol)
    {
        if (!($p_protocol instanceof isys_protocol)) {
            throw new isys_exception_general('The protocol used here should be an instance of isys_protocol.');
        } else {
            $this->m_protocol = $p_protocol;
        }
    }

}
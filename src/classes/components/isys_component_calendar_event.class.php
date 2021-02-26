<?php

/**
 * i-doit
 *
 * Calendar event class.
 *
 * @package     i-doit
 * @subpackage  Components
 * @author      Leonard Fischer <lfischer@i-doit.com>
 * @version     1.0.0
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 * @since       1.2.0
 */
class isys_component_calendar_event extends isys_component
{
    const TYPE_NOTE     = 'note';
    const TYPE_ALERT    = 'alert';
    const TYPE_CALLBACK = 'callback';

    /**
     * This variable is used for a callback - Looks like "array(array('class_name', 'method_name'), array('params')".
     *
     * @var  array
     */
    protected $m_callback = null;

    /**
     * Holds the day, for this event.
     *
     * @var  integer
     */
    protected $m_day = null;

    /**
     * Holds the month, for this event.
     *
     * @var  integer
     */
    protected $m_month = null;

    // Defining some event-types.

    /**
     * The callbacks name.
     *
     * @var  string
     */
    protected $m_name = '';

    /**
     * This variable holds the event-type.
     *
     * @var  string
     */
    protected $m_type = self::TYPE_NOTE;

    /**
     * Holds the year, for this event.
     *
     * @var  integer
     */
    protected $m_year = null;

    /**
     * Factory method for instant chaining.
     *
     * @param   string  $p_event
     * @param   integer $p_day
     * @param   integer $p_month
     * @param   integer $p_year
     *
     * @return  isys_component_calendar_event
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public static function factory($p_event, $p_day = null, $p_month = null, $p_year = null)
    {
        return new self($p_event, $p_day, $p_month, $p_year);
    }

    /**
     * Sets an event type.
     *
     * @param   string $p_type
     *
     * @return  isys_component_calendar_event
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function set_type($p_type = self::TYPE_NOTE)
    {
        $this->m_type = $p_type;

        return $this;
    }

    /**
     * Gets the event type.
     *
     * @return  string
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function get_type()
    {
        return $this->m_type;
    }

    /**
     * Sets a callback for this event.
     *
     * @param   array $p_callable Must be an array like "array('class_name', 'method_name')".
     * @param   array $p_params
     *
     * @return  isys_component_calendar_event
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function set_callback($p_callable, array $p_params = [])
    {
        $this->m_callback = [
            'callback' => $p_callable,
            'params'   => $p_params
        ];

        return $this->set_type(self::TYPE_CALLBACK);
    }

    /**
     * Returns the events callback.
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function get_callback()
    {
        return $this->m_callback;
    }

    /**
     * Sets this events date.
     *
     * @param   integer $p_day
     * @param   integer $p_month
     * @param   integer $p_year
     *
     * @return  isys_component_calendar_event
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function set_date($p_day = null, $p_month = null, $p_year = null)
    {
        $this->m_year = $p_year ?: C__WILDCARD;
        $this->m_month = $p_month ?: C__WILDCARD;
        $this->m_day = $p_day ?: C__WILDCARD;

        return $this;
    }

    /**
     * Returns this events date.
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function get_date()
    {
        return [
            'year'  => $this->m_year,
            'month' => $this->m_month,
            'day'   => $this->m_day,
        ];
    }

    /**
     * This method checks, if the event matches the given date.
     *
     * @param   integer $p_day
     * @param   integer $p_month
     * @param   integer $p_year
     *
     * @return  boolean
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function is_event_date($p_day, $p_month, $p_year)
    {
        if ($this->m_year == $p_year && $this->m_month == $p_month && $this->m_day == $p_day) {
            return true;
        }

        if ($this->m_year == C__WILDCARD && $this->m_month == $p_month && $this->m_day == $p_day) {
            // Event happens on a single day in one month, every year.
            return true;
        }

        if ($this->m_year == $p_year && $this->m_month == C__WILDCARD && $this->m_day == $p_day) {
            // Event happens on a single day, each month for the given year.
            return true;
        }

        if ($this->m_year == C__WILDCARD && $this->m_month == C__WILDCARD && $this->m_day == $p_day) {
            // Event happens on a single day, each month every year.
            return true;
        }

        if ($this->m_year == $p_year && $this->m_month == $p_month && $this->m_day == C__WILDCARD) {
            // Event happens every day for the given year and month.
            return true;
        }

        if ($this->m_year == C__WILDCARD && $this->m_month == $p_month && $this->m_day == C__WILDCARD) {
            // Event happens every day for the given month.
            return true;
        }

        if ($this->m_year == $p_year && $this->m_month == C__WILDCARD && $this->m_day == C__WILDCARD) {
            // Events happens every day for the given year.
            return true;
        }

        if ($this->m_year == C__WILDCARD && $this->m_month == C__WILDCARD && $this->m_day == C__WILDCARD) {
            // Event happens every day.
            return true;
        }

        return false;
    }

    /**
     * This method will return an array of the corresponsing event to lay on the calendar.
     *
     * @return  array
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function get_event()
    {
        switch ($this->m_type) {
            default:
            case self::TYPE_NOTE:
            case self::TYPE_ALERT:
                return [
                    'name' => $this->m_name,
                    'type' => $this->m_type
                ];

            case self::TYPE_CALLBACK:
                return [
                    'name'     => $this->m_name,
                    'type'     => $this->m_type,
                    'callback' => $this->m_callback
                ];
        }
    }

    /**
     * Private constructor - Singleton.
     *
     * @param   string  $p_event
     * @param   integer $p_day
     * @param   integer $p_month
     * @param   integer $p_year
     *
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function __construct($p_event, $p_day = null, $p_month = null, $p_year = null)
    {
        $this->m_name = $p_event;

        $this->m_year = $p_year ?: C__WILDCARD;
        $this->m_month = $p_month ?: C__WILDCARD;
        $this->m_day = $p_day ?: C__WILDCARD;
    }
}
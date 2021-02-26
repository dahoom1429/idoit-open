<?php

/**
 * i-doit
 *
 * Signal collection. Singleton.
 *
 * @see         http://en.wikipedia.org/wiki/Signals_and_slots
 * @package     i-doit
 * @subpackage  Components
 * @author      Dennis Stuecken <dstuecken@i-doit.de>
 * @version     1.0
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_component_signalcollection extends isys_component
{
    /**
     * Instance of self. For handling the singleton.
     *
     * @var  isys_component_signalcollection
     */
    private static $m_instance = null;

    /**
     * Configuration value. Sets wheter signales get emmitted or not.
     *
     * @var  boolean
     */
    private $m_emit_signals = true;

    /**
     * Holds the amount of slots which where recently triggered.
     *
     * @var  integer
     */
    private $m_last_emit_count = 0;

    /**
     * Signal::Slot register for priority slots.
     *
     * @var  array
     */
    private $m_priority_register = null;

    /**
     * Signal::Slot register.
     *
     * @var  array
     */
    private $m_signal_register = null;

    /**
     * Get intance.
     *
     * @static
     * @return  isys_component_signalcollection
     */
    public static function get_instance()
    {
        if (self::$m_instance === null) {
            self::$m_instance = new self;
        }

        return self::$m_instance;
    }

    /**
     * Enables signal slot emitting
     */
    public function enable_emitting()
    {
        $this->m_emit_signals = true;
    }

    /**
     * Stop emitting signals.
     */
    public function disable_emitting()
    {
        $this->m_emit_signals = false;
    }

    /**
     * Returns the signal register.
     *
     * @return  array
     */
    public function get_signals()
    {
        return [
            'priorized' => $this->m_priority_register,
            'standard'  => $this->m_signal_register
        ];
    }

    /**
     * Returns the count of how many slots were emitted on the last emitment.
     *
     * @return  integer
     */
    public function get_last_emit_count()
    {
        return $this->m_last_emit_count;
    }

    /**
     * Checks if a specific signal is connected by anyone.
     *
     * @param   string $p_signal
     *
     * @return  boolean
     */
    public function is_connected($p_signal)
    {
        return (isset($this->m_signal_register[$p_signal]) && is_countable($this->m_signal_register[$p_signal]) && count($this->m_signal_register[$p_signal]) > 0) ||
            (isset($this->m_priority_register[$p_signal]) && is_countable($this->m_priority_register[$p_signal]) && count($this->m_priority_register[$p_signal]) > 0);
    }

    /**
     * The almighty signal connector.
     *
     * @param   string   $p_signal
     * @param   callable $p_slot
     * @param   integer  $p_priority
     *
     * @return  isys_component_signalcollection
     */
    public function connect($p_signal, $p_slot, $p_priority = 0)
    {
        if ($p_priority) {
            $this->m_priority_register[$p_signal][$p_priority][] = $p_slot;
            sort($this->m_priority_register[$p_signal], SORT_NUMERIC);
        } else {
            $this->m_signal_register[$p_signal][] = $p_slot;
        }

        return $this;
    }

    /**
     * Removes a slot from the signal collection.
     *
     * @param   string  $p_signal
     * @param   mixed   $p_slot
     * @param   integer $p_priority
     *
     * @throws  Exception
     * @return  isys_component_signalcollection
     */
    public function disconnect($p_signal, $p_slot, $p_priority = 0)
    {
        try {
            if ($this->is_connected($p_signal) && is_countable($this->m_signal_register[$p_signal])) {
                if (!$p_priority) {
                    foreach ($this->m_signal_register[$p_signal] as $l_key => $l_slot) {
                        if ($this->slotcmp($l_slot, $p_slot)) {
                            unset($this->m_signal_register[$p_signal][$l_key]);
                            break;
                        }
                    }

                    if (count($this->m_signal_register[$p_signal]) == 0) {
                        unset($this->m_signal_register[$p_signal]);
                    }
                } else {
                    foreach ($this->m_priority_register[$p_signal][$p_priority] as $l_key => $l_slot) {
                        if ($this->slotcmp($l_slot, $p_slot)) {
                            unset($this->m_priority_register[$p_signal][$p_priority][$l_key]);
                            break;
                        }
                    }

                    if (count($this->m_priority_register[$p_signal][$p_priority]) == 0) {
                        unset($this->m_priority_register[$p_signal][$p_priority]);
                    }
                }
            }
        } catch (Exception $e) {
            throw $e;
        }

        return $this;
    }

    /**
     * Emits a signal.
     *
     * This method is used to evaluate all registered signals which results in calling the corresponding slots.
     *
     * @param string $p_signal
     * @param        $p_parameter1
     * @param        $p_parameter2
     * @param ..
     *
     * @return mixed
     */
    public function emit($p_signal /*, $p_parameters */)
    {

        try {
            $l_emit_count = 0;
            $l_return = [];

            if ($this->m_emit_signals === false) {
                return null;
            }

            /* Extract parameters */
            $p_parameters = array_slice(func_get_args(), 1);

            /* Start emitting priority signals */

            /* Check for an existing signal in our signal register */
            if (isset($this->m_priority_register[$p_signal])) {

                /* Check for any existing slots for the current signal */
                if (is_array($this->m_priority_register[$p_signal])) {

                    foreach ($this->m_priority_register[$p_signal] as $l_priority) {

                        /* Iterate through slots and call them */
                        foreach ($l_priority as $l_callback) {

                            if (is_callable($l_callback)) {
                                $l_return[] = call_user_func_array($l_callback, $p_parameters);
                                $l_emit_count++;
                            }

                        }

                    }

                }

            }

            /* Start emitting all other signals */

            /* Check for an existing signal in our signal register */
            if (isset($this->m_signal_register[$p_signal])) {

                /* Check for any existing slots for the current signal */
                if (is_array($this->m_signal_register[$p_signal])) {

                    /* Iterate through slots and call them */
                    foreach ($this->m_signal_register[$p_signal] as $l_callback) {

                        if (is_callable($l_callback)) {
                            $l_return[] = call_user_func_array($l_callback, $p_parameters);
                            $l_emit_count++;
                        }

                    }

                }

            }

            $this->m_last_emit_count = $l_emit_count;

            return $l_return;

        } catch (Exception $e) {
            throw new Exception('Signal error (' . $p_signal . ') : ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Slot comparing engine.
     *
     * @param   mixed $p_slot1
     * @param   mixed $p_slot2
     *
     * @return  boolean
     */
    private function slotcmp($p_slot1, $p_slot2)
    {
        if (is_array($p_slot1) && is_array($p_slot2)) {
            if (count($p_slot2) === 1) {
                if (strcmp($p_slot2[0], $p_slot1[0])) {
                    return true;
                }
            } elseif (count($p_slot2) === 2) {
                if ($p_slot2[0] == $p_slot1[0] && $p_slot2[1] == $p_slot1[1]) {
                    return true;
                }
            }
        } elseif (is_string($p_slot1) && is_string($p_slot2)) {
            if (strcmp($p_slot1, $p_slot2)) {
                return true;
            }
        }

        return false;
    }
}
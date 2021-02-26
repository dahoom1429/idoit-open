<?php

/**
 * i-doit
 * Validation exception class.
 *
 * @package     i-doit
 * @subpackage  Exceptions
 * @author      Leonard Fischer <lfischer@i-doit.de>
 * @version     1.5
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_exception_validation extends isys_exception
{

    /**
     * This variable will hold the failed category entry ID.
     *
     * @var  integer
     */
    protected $m_cat_entry_id = [];

    /**
     * Exception topic, may contain a language constant!
     *
     * @var  string
     */
    protected $m_exception_topic = 'Validation exception';

    /**
     * This variable will hold all failed validations.
     *
     * @var  array
     */
    protected $m_validation_errors = [];

    /**
     * Method for retrieving the validation failures.
     *
     * @return array
     */
    public function get_validation_errors()
    {
        return $this->m_validation_errors;
    }

    /**
     * Method for retrieving the validation failures.
     *
     * @return array
     */
    public function get_cat_entry_id()
    {
        return $this->m_cat_entry_id;
    }

    /**
     * Exception constructor.
     *
     * @param  string  $p_message
     * @param  array   $p_validation_errors
     * @param  integer $p_cat_entry_id
     */
    public function __construct($p_message, $p_validation_errors, $p_cat_entry_id = null)
    {
        $this->m_validation_errors = $p_validation_errors;
        $this->m_cat_entry_id = (int)$p_cat_entry_id;

        parent::__construct($p_message, 0);
    }
}
<?php
/**
 * CMDB ACTION PROCESSOR - BELONGS TO THE COMING HIGH-LEVEL API
 *
 * @package     i-doit
 * @subpackage  CMDB_Actions
 * @author      Andre Woesten <awoesten@i-doit.de>
 * @version     1.0
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */

/**
 * Class isys_cmdb_action_processor
 */
class isys_cmdb_action_processor extends isys_component
{
    /**
     * Action table assigning action constants to action handler classes.
     *
     * @var  array
     */
    private static $m_actiontable;

    /**
     * Action list.
     *
     * @var  array
     */
    private $m_actions = [];

    /**
     * CMDB Access object.
     *
     * @var  isys_cmdb_dao
     */
    private $m_dao_cmdb;

    /**
     * Result stack.
     *
     * @var  array
     */
    private $m_resultstack = [];

    /**
     * Pushes a result on the stack.
     *
     * @param   mixed $p_data
     *
     * @return  integer
     */
    public function result_push($p_data)
    {
        return array_push($this->m_resultstack, $p_data);
    }

    /**
     * Pops a result off the stack.
     *
     * @return  mixed
     */
    public function result_pop()
    {
        if ($this->result_count() > 0) {
            return array_pop($this->m_resultstack);
        }

        return null;
    }

    /**
     * Returns the count of entries in the result stack.
     *
     * @return  integer
     */
    public function result_count()
    {
        return count($this->m_resultstack);
    }

    /**
     * Returns the result stack (You won't do this).
     *
     * @return  array
     */
    public function result_stack()
    {
        return $this->m_resultstack;
    }

    /**
     * Clears the result stack.
     */
    public function result_clear()
    {
        $this->m_resultstack = [];
    }

    /**
     * Inserts an action into the action list.
     *
     * @param   integer $p_action_id
     * @param   mixed   $p_action_data
     *
     * @return  boolean
     */
    public function insert($p_action_id, $p_action_data)
    {
        if (array_key_exists($p_action_id, self::$m_actiontable)) {
            $this->m_actions[] = [
                $p_action_id,
                $p_action_data
            ];

            return key($this->m_actions);
        }

        return false;
    }

    /**
     * Removes an action from the action list.
     *
     * @param   integer $p_index
     *
     * @return  boolean
     */
    public function remove($p_index)
    {
        if (array_key_exists($p_index, $this->m_actions)) {
            unset($this->m_actions[$p_index]);

            return true;
        }

        return false;
    }

    /**
     * Returns the number of entries in the action list.
     *
     * @return integer
     */
    public function count()
    {
        return count($this->m_actions);
    }

    /**
     * Resets the action list.
     */
    public function reset()
    {
        $this->m_actions = [];
    }

    /**
     * Return last result of resultstack.
     *
     * @return  array
     */
    public function get_last_result()
    {
        return max($this->m_resultstack);
    }

    /**
     * @return  array
     */
    public function get_actions()
    {
        return $this->m_actions;
    }

    /**
     * Processes all actions.
     *
     * @throws isys_exception_cmdb
     */
    public function process()
    {
        $l_nSecCounter = 0;

        foreach ($this->m_actions as $l_ai => $l_action) {
            $l_atable = self::$m_actiontable;
            $l_class = $l_atable[$l_action[0]];
            $l_data = $l_action[1];

            $l_nSecCounter++;

            if ($l_nSecCounter > 10) {
                throw new isys_exception_cmdb("There were more than 10 actions detected! Aborting...");
            }

            try {
                $this->action_process($l_class, $l_data);
            } catch (isys_exception_cmdb $l_e) {
                throw new isys_exception_cmdb(
                    'Could not handle ' . $l_class . ' ' . sprintf("%04X", $l_action[0]) . 'h (' . $l_ai . '): ' . $l_e->getMessage(),
                    C__CMDB__ERROR__ACTION_PROCESSOR,
                    $l_e->getTrace()
                );
            }
        }

        $this->reset();

        return $this->m_resultstack;
    }

    /**
     * Set DAO for the Processor and the DAO.
     *
     * @param  isys_cmdb_dao $p_dao
     */
    public function set_dao(isys_cmdb_dao $p_dao)
    {
        $this->m_dao_cmdb = $p_dao;
    }

    /**
     * Process a single action and do the error handling simulteanously.
     *
     * @param   string $p_handler_class
     * @param   array  $p_data
     *
     * @return  boolean
     */
    private function action_process($p_handler_class, $p_data)
    {
        $l_ret = null;

        if (class_exists($p_handler_class)) {
            /**
             * @var $l_object isys_cmdb_action
             */
            $l_object = new $p_handler_class;

            $p_data["__ACTIONPROC"] = &$this;

            try {
                $l_ret = $l_object->handle($this->m_dao_cmdb, $p_data);
            } catch (isys_exception_auth $e) {
                global $index_includes;

                $index_includes['contentbottomcontent'] = 'exception-auth.tpl';

                isys_application::instance()->template->assign('exception', $e->write_log());
            }

            $newStack = $p_data["__ACTIONPROC"]->result_stack();
            if (is_array($newStack)) {
                // Update resultstack.
                $this->m_resultstack = $newStack;
            }

            return $l_ret;
        }

        return false;
    }

    /**
     * Constructor.
     *
     * @param   isys_cmdb_dao $p_dao
     *
     * @throws  isys_exception_cmdb
     */
    public function __construct(isys_cmdb_dao $p_dao)
    {
        if (is_object($p_dao)) {
            $this->m_dao_cmdb = $p_dao;
        } else {
            // Can this even appear without any PHP Warnings?
            throw new isys_exception_cmdb("Could not instantiate CMDB action processor (Bad DAO)", 0);
        }

        $this->m_actions = [];
        $this->m_resultstack = [];

        if (!isset(self::$m_actiontable)) {
            self::$m_actiontable = [
                // Creates an object, implies the usage of "insert_new_obj" and the automatic category creation.
                C__CMDB__ACTION__OBJECT_CREATE     => "isys_cmdb_action_object_create",
                // Rank an object, using the general method "rank_records".
                C__CMDB__ACTION__OBJECT_RANK       => "isys_cmdb_action_object_rank",
                // Creates a category list entry (using create_connector or attachObjects).
                C__CMDB__ACTION__CATEGORY_CREATE   => "isys_cmdb_action_category_create",
                // Ranks a category entry (using rank_record).
                C__CMDB__ACTION__CATEGORY_RANK     => "isys_cmdb_action_category_rank",
                // Updates a category list entry (using save_element).
                C__CMDB__ACTION__CATEGORY_UPDATE   => "isys_cmdb_action_category_update",
                // Configures an object (object <-> dynamic categories).
                C__CMDB__ACTION__CONFIG_OBJECT     => "isys_cmdb_action_config_object",
                // Configures an objecttype (object <-> global categories and configuration.
                C__CMDB__ACTION__CONFIG_OBJECTTYPE => "isys_cmdb_action_config_objecttype"
            ];
        }
    }
}

/**
 * Action interface. You need to implement this for own actions.
 */
interface isys_cmdb_action
{
    public function handle(isys_cmdb_dao $p_dao, &$p_data);
}

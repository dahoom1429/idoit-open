<?php

/**
 * i-doit
 *
 * Database transaction manager for mySQL
 *
 * @package    i-doit
 * @subpackage Components
 * @author     Andre Woesten <awoesten@i-doit.de>
 * @version    1.0
 * @copyright  synetics GmbH
 * @license    http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 * @todo       Echter Transaktionsmanager mit Liste aktueller (verschachtelter)
 *       Transaktionen mit Verwaltungsmöglichkeiten und einem entsprechenden
 *       Interface für Entwickler zum Abfragen der aktuellen Transaktionen
 *       und Abfragelisten.
 * @todo       Implementierung muss noch fortgeführt werden:
 *       - Verschachtelte Transaktionen (begin_update)
 */
class isys_component_database_transaction_manager
{
    /**
     * @desc
     * Constants with isolation levels - class-specific as always, so use them
     * by doing:
     *  <code><?php
     *   $myTransaction->set_transaction_isolation_level(
     *    isys_componenet_database_transaction_manager::
     *     c_isolation_level_serializable
     *   );
     *  ?></code>
     */
    const c_isolation_level_read_commited   = "READ COMMITTED";
    const c_isolation_level_read_uncommited = "READ UNCOMMITTED";
    const c_isolation_level_repeatable_read = "REPEATABLE READ";
    const c_isolation_level_serializable    = "SERIALIZABLE";

    /**
     * @var isys_component_database_transaction_manager[]
     */
    private static $instance;

    /**
     * @var isys_component_database
     */
    private $m_db;

    private $m_transaction_count = 0;

    /**
     * @param isys_component_database $p_database
     *
     * @return isys_component_database_transaction_manager
     */
    final public static function instance(isys_component_database $p_database)
    {
        if (!isset(self::$instance[$p_database->get_db_name()])) {
            self::$instance[$p_database->get_db_name()] = new self($p_database);
        }

        return self::$instance[$p_database->get_db_name()];
    }

    /**
     * @return bool
     * @desc Start transaction
     */
    public function begin()
    {
        if (!$this->m_transaction_count) {
            $this->m_transaction_count++;

            return $this->m_db->begin();
        }

        return false;
    }

    /**
     * @return bool
     * @desc Commit transaction
     */
    public function commit()
    {
        if ($this->m_transaction_count > 0) {
            $this->m_transaction_count--;

            return $this->m_db->commit();
        }

        return true;
    }

    /**
     * @return bool
     * @desc Rollback transaction
     */
    public function rollback()
    {
        if ($this->m_transaction_count > 0) {
            $this->m_transaction_count--;

            return $this->m_db->rollback();
        }

        return false;
    }

    /**
     * @return bool
     * @desc Returns status, if a transaction is running
     */
    public function is_transaction_running()
    {
        return $this->m_transaction_count > 0;
    }

    /**
     * Returns count of running transactions
     *
     * @return integer
     */
    public function get_transaction_count()
    {
        return $this->m_transaction_count;
    }

    /**
     * @param $pIsolationLevel
     *
     * @return bool
     */
    public function set_isolation_level($pIsolationLevel)
    {
        if ($pIsolationLevel == self::c_isolation_level_read_commited || $pIsolationLevel == self::c_isolation_level_read_uncommited ||
            $pIsolationLevel == self::c_isolation_level_repeatable_read || $pIsolationLevel == self::c_isolation_level_serializable) {
            return !!($this->m_db->set_isolation_level($pIsolationLevel));
        }

        return false;
    }

    /**
     * @param $p_autoCommit
     */
    public function set_autocommit($p_autoCommit)
    {
        $this->m_db->set_autocommit($p_autoCommit);
    }

    /**
     * isys_component_database_transaction_manager constructor.
     *
     * @param $pDatabase
     */
    public function __construct(isys_component_database &$pDatabase)
    {
        $this->m_db = $pDatabase;
        $this->m_transaction_count = 0;

        //$this->set_autocommit(false);
        //$this->set_isolation_level(self::c_isolation_level_repeatable_read);
    }
}
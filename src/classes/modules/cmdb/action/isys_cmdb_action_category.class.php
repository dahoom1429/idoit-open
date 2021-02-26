<?php

/**
 * i-doit
 *
 * CMDB Action Processor
 *
 * @package     i-doit
 * @subpackage  CMDB
 * @author      Dennis Stücken <dstuecken@i-doit.de>
 * @version     1.0
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_action_category
{
    /**
     * Instance of isys_component_dao_lock.
     *
     * @var  isys_component_dao_lock
     */
    protected $m_dao_lock;

    /**
     * Callback function called by rank_records.
     *
     * @param   $p_object_id
     * @param   $p_category_const
     *
     * @return  boolean
     * @author  Selcuk Kekec <skekec@i-doit.com>
     */
    public function check_right($p_object_id, $p_category_const)
    {
        return isys_auth_cmdb::instance()
            ->has_rights_in_obj_and_category(isys_auth::EDIT, $p_object_id, $p_category_const);
    }

    /**
     * Checks if the object is locked. Returns true, if object is locked for the current user - false, if not.
     *
     * @return  boolean
     */
    protected function object_is_locked()
    {
        if ($this->m_dao_lock->check_lock($_GET[C__CMDB__GET__OBJECT])) {
            isys_component_template_infobox::instance()
                ->set_message("<b>ERROR! - " . isys_application::instance()->container->get('language')
                        ->get("LC__OBJECT_LOCKED") . " (Lock-Timeout: " . C__LOCK__TIMEOUT . "s)</b>", null, null, null, defined_or_default('C__LOGBOOK__ALERT_LEVEL__3', 3));

            return true;
        }

        return false;
    }

    /**
     * Constructor.
     */
    public function __construct()
    {
        global $g_comp_database;

        $this->m_dao_lock = new isys_component_dao_lock($g_comp_database);
    }
}

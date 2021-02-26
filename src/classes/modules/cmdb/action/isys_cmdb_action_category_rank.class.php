<?php

/**
 * i-doit
 *
 * CMDB
 * Action Processor
 *
 * Action: Category ranking
 *
 * @package     i-doit
 * @subpackage  CMDB
 * @author      Andre Woesten <awoesten@i-doit.de>
 * @version     0.9
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_action_category_rank extends isys_cmdb_action_category implements isys_cmdb_action
{

    /**
     * @var isys_cmdb_dao_category
     */
    protected $m_cat_dao = null;

    /**
     * Callback function called by rank_records.
     *
     * @todo    It would be much better, if the current object-id would get passed by parameter.
     *
     * @param   integer $p_direction
     * @param   integer $p_target_status
     *
     * @return  boolean
     */
    public function check_right($p_direction = null, $p_target_status = null)
    {
        if ($p_direction == C__CMDB__RANK__DIRECTION_DELETE && $p_target_status == C__RECORD_STATUS__ARCHIVED) {
            return isys_auth_cmdb::instance()
                    ->has_rights_in_obj_and_category(isys_auth::ARCHIVE, $_GET[C__CMDB__GET__OBJECT], $this->m_cat_dao->get_category_const()) || isys_auth_cmdb::instance()
                    ->has_rights_in_obj_and_category(isys_auth::DELETE, $_GET[C__CMDB__GET__OBJECT], $this->m_cat_dao->get_category_const());
        }

        if ($p_direction == C__CMDB__RANK__DIRECTION_RECYCLE && $p_target_status == C__RECORD_STATUS__NORMAL) {
            return isys_auth_cmdb::instance()
                    ->has_rights_in_obj_and_category(isys_auth::ARCHIVE, $_GET[C__CMDB__GET__OBJECT], $this->m_cat_dao->get_category_const()) || isys_auth_cmdb::instance()
                    ->has_rights_in_obj_and_category(isys_auth::DELETE, $_GET[C__CMDB__GET__OBJECT], $this->m_cat_dao->get_category_const());
        }

        return isys_auth_cmdb::instance()
            ->has_rights_in_obj_and_category(isys_auth::DELETE, $_GET[C__CMDB__GET__OBJECT], $this->m_cat_dao->get_category_const());
    }

    /**
     * Method for handling the ranking-action.
     *
     * @param   isys_cmdb_dao $p_dao
     * @param   array         &$p_data
     *
     * @throws  Exception|isys_exception
     * @throws  isys_exception_cmdb
     */
    public function handle(isys_cmdb_dao $p_dao, &$p_data)
    {
        $p_direction = $p_data[0];

        if (is_a($p_data[1], 'isys_cmdb_dao_category')) {
            $this->m_cat_dao = $p_data[1];
        } else {
            throw new \Exception('Invalid data package sent to ' . get_class($this) . ': ' . var_export($p_data, true));
        }

        $p_table = $p_data[2];
        $p_posts = $p_data[3];

        if (isset($p_data[4]) && ($p_data[4] == C__NAVMODE__PURGE || $p_data[4] == C__NAVMODE__QUICK_PURGE)) {
            $l_purge = true;
        } else {
            $l_purge = false;
        }

        if (!$this->object_is_locked()) {
            if (strpos($p_table, "isys_cats") === 0) {
                if (strripos($p_table, "_list")) {
                    $p_table = substr($p_table, 0, strripos($p_table, "_list"));
                }

                $p_table = trim($p_table);
                $p_table = $p_table . "_list";
            }

            try {
                if (is_array($p_posts) && count($p_posts) > 0) {
                    $l_tmp = count($p_posts);
                    // This is used to remove "empty" entries like array(0 => "0").
                    $p_posts = array_filter($p_posts);

                    if ($l_tmp != count($p_posts)) {
                        // Notify the user!
                        isys_notify::warning(isys_application::instance()->container->get('language')
                            ->get('LC__CMDB__RANK_ERROR__ZERO_ID', [$p_table]), ['sticky' => true]);

                        // This will create a log message, without displaying the big red error-box.
                        new isys_exception_database(isys_application::instance()->container->get('language')
                            ->get('LC__CMDB__RANK_ERROR__ZERO_ID', [$p_table]));
                    }

                    if (count($p_posts) > 0) {
                        isys_component_signalcollection::get_instance()
                            ->emit("mod.cmdb.beforeCategoryEntryRank", $this->m_cat_dao, $p_table, $p_direction, $p_posts);

                        if (!($l_result = $this->m_cat_dao->rank_records($p_posts, $p_direction, $p_table, [
                            $this,
                            'check_right'
                        ], $l_purge))) {
                            throw new isys_exception_cmdb("Could not delete category entries (" . var_export($p_posts, true) . ") (CMDB-DAO->rank_records)",
                                C__CMDB__ERROR__ACTION_PROCESSOR);
                        }

                        // If entry is purged unset the member variable for the list id
                        if ($l_purge) {
                            $this->m_cat_dao->set_list_id(null);
                        }

                        isys_component_signalcollection::get_instance()
                            ->emit("mod.cmdb.afterCategoryEntryRank", $this->m_cat_dao, $p_table, $l_result, $p_direction, $p_posts);

                        unset($_POST['savedCheckboxes']);
                    }
                }
            } catch (isys_exception $e) {
                throw $e;
            }
        }
    }
}

<?php

namespace idoit\Module\Cmdb\Model\Matcher;

use idoit\Exception\Exception;
use idoit\Model\Dao\Base;
use isys_application;

/**
 * i-doit
 *
 * Ci Models
 *
 * @package     i-doit
 * @subpackage  Cmdb
 * @author      Dennis Stücken <dstuecken@i-doit.com>
 * @version     1.8
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class MatchDao extends Base
{

    /**
     * Retrieves a object match profile by id
     *
     * @param $id
     *
     * @return array
     */
    public function getMatchProfileById($id)
    {
        $sql = 'SELECT * FROM isys_obj_match WHERE isys_obj_match__id = ' . $this->convert_sql_id($id);

        return $this->retrieve($sql)
            ->get_row();
    }

    /**
     * Get all object match profiles
     *
     * @return \isys_component_dao_result
     * @throws \isys_exception_database
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function getMatchProfiles()
    {
        return $this->retrieve('SELECT * FROM isys_obj_match');
    }

    /**
     * Update a object match profile
     *
     * @param int    $id
     * @param string $title
     * @param int    $bits
     * @param int    $minMatch
     *
     * @return bool
     * @throws \isys_exception_dao
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function saveMatchProfile($id = null, $title, $bits, $minMatch)
    {
        $sql = 'UPDATE isys_obj_match SET
          isys_obj_match__title = ' . $this->convert_sql_text($title) . ',
          isys_obj_match__bits = ' . $this->convert_sql_int($bits) . ',
          isys_obj_match__min_match = ' . $this->convert_sql_int($minMatch) . '
          WHERE isys_obj_match__id = ' . $this->convert_sql_id($id);

        return $this->update($sql);
    }

    /**
     * Create a new object match profile
     *
     * @param $title
     * @param $bits
     * @param $minMatch
     *
     * @return bool
     * @throws \isys_exception_dao
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function createMatchProfile($title, $bits, $minMatch)
    {
        $sql = 'INSERT INTO isys_obj_match (isys_obj_match__title, isys_obj_match__bits, isys_obj_match__min_match) VALUES
          (' . $this->convert_sql_text($title) . ', ' . $this->convert_sql_int($bits) . ', ' . $this->convert_sql_int($minMatch) . ');';

        return $this->update($sql);
    }

    /**
     * Delete object match profile
     *
     * @param $profileIDs
     *
     * @return bool
     * @throws \isys_exception_dao
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function deleteMatchProfile($profileID)
    {
        if (is_array($profileID) && count($profileID) > 0) {
            $condition = 'WHERE isys_obj_match__id IN (';
            $idsCollection = '';
            foreach ($profileID as $id) {
                if ($id == 1) {
                    continue;
                }

                $idsCollection .= $id . ',';
            }

            if ($idsCollection == '') {
                throw new \isys_exception_general(isys_application::instance()->container->get('language')
                    ->get('LC__MODULE__SYSTEM__OBJECT_MATCHING__DEFAULT_PROFILE_IS_NOT_DELETABLE'));
            }

            $condition .= rtrim($idsCollection, ',') . ')';
        } elseif (is_numeric($profileID)) {
            if ($profileID == 1) {
                throw new \isys_exception_general(isys_application::instance()->container->get('language')
                    ->get('LC__MODULE__SYSTEM__OBJECT_MATCHING__DEFAULT_PROFILE_IS_NOT_DELETABLE'));
            }

            $condition = 'WHERE isys_obj_match__id = ' . $this->convert_sql_id($profileID);
        } else {
            if ($profileID) {
                throw new Exception(sprintf('Could not delete matching profile ID "%s".', $profileID));
            } else {
                throw new Exception(sprintf('No profiles were selected.'));
            }
        }

        $delete = 'DELETE FROM isys_obj_match ' . $condition;

        return $this->update($delete);
    }
}

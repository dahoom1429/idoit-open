<?php

/**
 * i-doit
 *
 * CMDB DAO Extension for jdisc
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Dennis Stücken <dstuecken@i-doit.de>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_jdisc extends isys_cmdb_dao
{
    /**
     * Remember all really created object ids of current session
     *
     * @var array
     */
    private static $m_created_objects = [];

    /**
     * @param $p_object_id
     *
     * @return mixed
     */
    public static function object_created_in_current_session($p_object_id)
    {
        return isset(self::$m_created_objects[$p_object_id]);
    }

    /**
     * Custom object creation function used by the jdisc import
     *
     * @param int       $p_obj_type_id
     * @param bool|null $p_unused
     * @param null      $p_strTitle
     * @param null      $p_strSYSID
     * @param int       $p_record_status
     * @param null      $p_hostname
     * @param null      $p_scantime
     * @param bool      $p_import_date
     * @param null      $p_created
     * @param null      $p_created_by
     * @param null      $p_updated
     * @param null      $p_updated_by
     * @param null      $p_category
     * @param null      $p_purpose
     * @param null      $p_cmdb_status
     * @param null      $p_description
     *
     * @return int|null
     */
    public function insert_new_obj(
        $p_obj_type_id,
        $p_unused = null,
        $p_strTitle = null,
        $p_strSYSID = null,
        $p_record_status = C__RECORD_STATUS__BIRTH,
        $p_hostname = null,
        $p_scantime = null,
        $p_import_date = false,
        $p_created = null,
        $p_created_by = null,
        $p_updated = null,
        $p_updated_by = null,
        $p_category = null,
        $p_purpose = null,
        $p_cmdb_status = null,
        $p_description = null
    ) {
        $l_id = false;

        if (isys_settings::get('jdisc.prevent-duplicates', true) && isys_tenantsettings::get('cmdb.unique.object-title', false)) {
            if (!empty($p_strTitle)) {
                $l_id = $this->get_obj_id_by_title($p_strTitle);
            }
        }

        if (!$l_id) {
            $l_id = parent::insert_new_obj($p_obj_type_id, (bool)$p_unused, //$p_set_obj_virtual
                $p_strTitle, $p_strSYSID, $p_record_status, $p_hostname, $p_scantime, $p_import_date, $p_created, $p_created_by, $p_updated, $p_updated_by, $p_category,
                $p_purpose, $p_cmdb_status, $p_description);
            self::$m_created_objects[$l_id] = true;
        }

        return $l_id;
    }

    /**
     * Do not update object from jdisc
     *
     * @See      ID-4038
     *
     * @param int  $p_object_id
     * @param null $p_object_type_id
     * @param null $p_title
     * @param null $p_description
     * @param null $p_sysid
     * @param null $p_record_status
     * @param null $p_hostname
     * @param null $p_scantime
     * @param null $p_created
     * @param null $p_created_by
     * @param null $p_updated
     * @param null $p_updated_by
     * @param null $p_cmdb_status
     * @param null $p_rt_cf_id
     * @param null $p_category
     * @param null $p_purpose
     *
     * @return bool
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function update_object(
        $p_object_id,
        $p_object_type_id = null,
        $p_title = null,
        $p_description = null,
        $p_sysid = null,
        $p_record_status = null,
        $p_hostname = null,
        $p_scantime = null,
        $p_created = null,
        $p_created_by = null,
        $p_updated = null,
        $p_updated_by = null,
        $p_cmdb_status = null,
        $p_rt_cf_id = null,
        $p_category = null,
        $p_purpose = null
    ) {
        // If prevent-duplicates is active and unique object title is active than do not update object otherwise the import could produce duplicates
        if (isys_settings::get('jdisc.prevent-duplicates', true) && isys_tenantsettings::get('cmdb.unique.object-title', false) && $p_record_status === null) {
            return true;
        } else {
            return parent::update_object($p_object_id, $p_object_type_id, $p_title, $p_description, $p_sysid, $p_record_status, $p_hostname, $p_scantime, $p_created,
                $p_created_by, $p_updated, $p_updated_by, $p_cmdb_status, $p_rt_cf_id, $p_category, $p_purpose);
        }
    }
}

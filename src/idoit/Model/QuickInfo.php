<?php

namespace idoit\Model;

use idoit\Model\Dao\Base;
use isys_convert;
use isys_tenantsettings;

/**
 * i-doit QuickInfo Model
 *
 * @package     i-doit
 * @subpackage  Core
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class QuickInfo extends Base
{
    /**
     * Table fields of `isys_cache_qinfo`.
     */
    const FIELDS = [
        'isys_cache_qinfo__id'           => 'id',
        'isys_cache_qinfo__isys_obj__id' => 'objectId',
        'isys_cache_qinfo__data'         => 'data',
        'isys_cache_qinfo__expiration'   => 'expiration'
    ];

    /**
     * Method for setting the quick info cache, will overwrite existing entry if necessary.
     *
     * @param int    $objectId
     * @param string $data
     *
     * @return bool
     * @throws \isys_exception_dao
     * @throws \isys_exception_database
     */
    public function setCache($objectId, $data)
    {
        // First we check if the given object exists in the floorplan.
        $quickinfoId = $this->getCache($objectId, true)->get_row_value('id');

        if ($quickinfoId !== null) {
            $sql = 'UPDATE isys_cache_qinfo SET %s WHERE isys_cache_qinfo__isys_obj__id = ' . $this->convert_sql_id($objectId) . ';';
        } else {
            $sql = 'INSERT INTO isys_cache_qinfo SET %s;';
        }

        $cacheTime = (int)isys_tenantsettings::get('cache.quickinfo.expiration', isys_convert::HOUR);

        $sqlFields = [
            'isys_cache_qinfo__isys_obj__id = ' . $this->convert_sql_id($objectId),
            'isys_cache_qinfo__data = ' . $this->convert_sql_text($data),
            'isys_cache_qinfo__expiration = ' . $this->convert_sql_int(time() + $cacheTime)
        ];

        return $this->update(str_replace('%s', implode(', ', $sqlFields), $sql)) && $this->apply_update();
    }

    /**
     * @param int|array $objectIds
     * @param bool      $ignoreCacheTime
     *
     * @return \isys_component_dao_result
     * @throws \isys_exception_database
     */
    public function getCache($objectIds, $ignoreCacheTime = null)
    {
        $sql = 'SELECT ' . $this->selectImplode(self::FIELDS) . ' 
            FROM isys_cache_qinfo 
            WHERE isys_cache_qinfo__isys_obj__id ' . $this->prepare_in_condition((array)$objectIds);

        if (!$ignoreCacheTime) {
            $sql .= ' AND isys_cache_qinfo__expiration >= ' . time();
        }

        return $this->retrieve($sql . ';');
    }

    /**
     * @param int|array $objectIds
     *
     * @return bool
     * @throws \isys_exception_dao
     */
    public function deleteCache($objectIds)
    {
        $sql = 'DELETE FROM isys_cache_qinfo WHERE isys_cache_qinfo__isys_obj__id ' . $this->prepare_in_condition((array)$objectIds) . ';';

        return $this->update($sql) && $this->apply_update();
    }

    /**
     * Truncate the quick info cache completely.
     *
     * @return bool
     * @throws \isys_exception_dao
     */
    public function truncate()
    {
        return $this->update('TRUNCATE isys_cache_qinfo;') && $this->apply_update();
    }
}

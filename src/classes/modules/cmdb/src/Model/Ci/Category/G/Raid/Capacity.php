<?php

namespace idoit\Module\Cmdb\Model\Ci\Category\G\Raid;

use idoit\Module\Cmdb\Model\Ci\Category\DynamicCallbackInterface;
use isys_application;
use isys_cmdb_dao_category_g_drive;
use isys_cmdb_dao_category_g_stor;
use isys_convert;

/**
 * i-doit
 *
 * Raid Category "Capacity" callback.
 *
 * @package     i-doit
 * @subpackage  Cmdb
 * @author      Kevin Mauel<kmauel@i-doit.com>
 * @version     1.11
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class Capacity implements DynamicCallbackInterface
{
    /**
     * Render method.
     *
     * @param string $data
     * @param mixed  $extra
     *
     * @return mixed
     */
    public static function render($data, $extra = null)
    {
        if ($data === null) {
            return '';
        }

        $objId = filter_var($data, FILTER_SANITIZE_NUMBER_INT);

        // Compute total capacity of RAID group.
        $daoRaid = \isys_cmdb_dao_category_g_raid::instance(isys_application::instance()->container->get('database'));
        $daoStorage = new isys_cmdb_dao_category_g_stor(isys_application::instance()->container->get('database'));
        $daoDrive = new isys_cmdb_dao_category_g_drive(isys_application::instance()->container->get('database'));

        $raids = $daoRaid->get_data(null, $objId)
            ->__as_array();

        $raidsConcat = '';

        foreach ($raids as $raid) {
            $raid["isys_title"] = $raid["isys_catg_raid_list__title"];
            $raid["isys_level__title"] = $raid["isys_stor_raid_level__title"];
            $raid["isys_type__title"] = $raid["isys_raid_type__title"];
            $numDisks = $lo = $maximumRaidCapacity = 0;

            if (is_value_in_constants($raid["isys_catg_raid_list__isys_raid_type__id"], ['C__CMDB__RAID_TYPE__HARDWARE'])) {
                $resource = $daoStorage->get_devices(null, $_GET[C__CMDB__GET__OBJECT], $raid["isys_catg_raid_list__id"]);
                $numDisks = $resource->num_rows();

                if ($resource->num_rows() > 0) {
                    $row = $resource->get_row();
                    $lo = $row["isys_catg_stor_list__capacity"];
                    $maximumRaidCapacity = $row["isys_catg_stor_list__capacity"];

                    while ($row = $resource->get_row()) {
                        if ($row["isys_catg_stor_list__capacity"] < $lo) {
                            $lo = $row["isys_catg_stor_list__capacity"];
                        }

                        if ($row["isys_catg_stor_list__hotspare"] == "1") {
                            $numDisks--;
                        }

                        $maximumRaidCapacity += $row["isys_catg_stor_list__capacity"];
                    }
                }
            } elseif (is_value_in_constants($raid["isys_catg_raid_list__isys_raid_type__id"], ['C__CMDB__RAID_TYPE__SOFTWARE'])) {
                $resource = $daoDrive->get_drives($raid["isys_catg_raid_list__id"]);
                $numDisks = $resource->num_rows();

                if ($resource->num_rows() > 0) {
                    $row = $resource->get_row();
                    $lo = $row["isys_catg_drive_list__capacity"];
                    $maximumRaidCapacity = $row["isys_catg_drive_list__capacity"];

                    while ($row = $resource->get_row()) {
                        if ($row["isys_catg_drive_list__capacity"] < $lo) {
                            $lo = $row["isys_catg_drive_list__capacity"];
                        }

                        if ($row["isys_catg_stor_list__hotspare"] == "1") {
                            $numDisks--;
                        }

                        $maximumRaidCapacity += $row["isys_catg_drive_list__capacity"];
                    }
                }
            }

            $unit = isys_convert::get_memory_unit_const($maximumRaidCapacity, true);

            switch ($unit) {
                case 'C__MEMORY_UNIT__TB':
                    $memory_type_const = 'LC__CMDB__MEMORY_UNIT__TB';
                    break;
                case 'C__MEMORY_UNIT__KB':
                    $memory_type_const = 'LC__CMDB__MEMORY_UNIT__KB';
                    break;
                case 'C__MEMORY_UNIT__MB':
                    $memory_type_const = 'LC__CMDB__MEMORY_UNIT__MB';
                    break;
                case 'C__MEMORY_UNIT__GB':
                    $memory_type_const = 'LC__CMDB__MEMORY_UNIT__GB';
                    break;
                default:
                    $memory_type_const = 'LC__CMDB__MEMORY_UNIT__Bytes';
                    break;
            }

            $maximumRaidCapacity = isys_convert::memory($maximumRaidCapacity, $unit, C__CONVERT_DIRECTION__BACKWARD);
            $raid["isys_capacity"] = $maximumRaidCapacity . " " . isys_application::instance()->container->get('language')
                    ->get($memory_type_const);

            if ($raid["isys_stor_raid_level__const"] == "C__STOR_RAID_LEVEL__JBOD") {
                $raid["isys_capacity"] = $maximumRaidCapacity . ' ' . isys_application::instance()->container->get('language')
                        ->get($memory_type_const);
            } else {
                $raid["isys_capacity"] = isys_convert::memory(isys_cmdb_dao_category_g_stor::instance(isys_application::instance()->container->get('database'))
                        ->raidcalc($numDisks, $lo, $raid["isys_stor_raid_level__title"]), $unit, C__CONVERT_DIRECTION__BACKWARD) . ' ' .
                    isys_application::instance()->container->get('language')
                        ->get($memory_type_const);
            }

            if ($raidsConcat === '') {
                $raidsConcat .= $raid['isys_catg_raid_list__title'] . ': ' . $raid['isys_capacity'];
                continue;
            }

            $raidsConcat .= '<br />'. $raid['isys_catg_raid_list__title'] . ': ' . $raid['isys_capacity'];
        }

        return $raidsConcat;
    }
}

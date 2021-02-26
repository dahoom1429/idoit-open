<?php

define('C__CONVERT_DIRECTION__FORMWARD', 1);
define('C__CONVERT_DIRECTION__BACKWARD', 2);

/**
 * i-doit
 *
 * Convert helper.
 *
 * @package     i-doit
 * @subpackage  General
 * @author      Dennis Stuecken <dstuecken@synetics.de>
 * @version     0.9
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_convert
{
    /**
     * Unit for inches.
     *
     * @var  float
     */
    const INCH = 25.4;

    /**
     * Unit for foot.
     *
     * @var  float
     */
    const FOOT = 304.8;

    /**
     * Unit for bytes.
     *
     * @var  integer
     */
    const BYTE = 1024;

    /**
     * Unit for hertz.
     *
     * @var  integer
     */
    const HERTZ = 1000;

    /**
     * The amount of seconds in one "common" year, as defined: http://en.wikipedia.org/wiki/Year#Symbol_a
     *
     * @var  integer
     */
    const YEAR = 31556926;

    /**
     * The amount of seconds in one "common" month, rounded result of 31556926 / 12.
     *
     * @var  integer
     */
    const MONTH = 2629744;

    /**
     * The amount of seconds in one week.
     *
     * @var  integer
     */
    const WEEK = 604800;

    /**
     * The amount of seconds in one day.
     *
     * @var  integer
     */
    const DAY = 86400;

    /**
     * The amount of seconds in one hour.
     *
     * @var  integer
     */
    const HOUR = 3600;

    /**
     * The amount of seconds in one minute.
     *
     * @var  integer
     */
    const MINUTE = 60;

    /**
     * Converts seconds to a $p_unit conform period.
     *
     * @param  integer    $seconds
     * @param  int|string $unit
     *
     * @return integer
     * @todo   Merge with period_to_seconds - Maybe even replace with isys_convert::time();
     */
    public static function seconds_to_period($seconds, $unit)
    {
        if ($seconds === null || !is_numeric($seconds)) {
            return null;
        }

        if (is_numeric($unit)) {
            $database = isys_application::instance()->container->get('database');
            $unit = isys_factory_cmdb_dialog_dao::get_instance('isys_guarantee_period_unit', $database)
                ->get_data($unit)['isys_guarantee_period_unit__const'];
        }

        switch ($unit) {
            case 'C__GUARANTEE_PERIOD_UNIT_DAYS':
                return round($seconds / self::DAY);

            case 'C__GUARANTEE_PERIOD_UNIT_WEEKS':
                return round($seconds / self::WEEK);

            case 'C__GUARANTEE_PERIOD_UNIT_MONTH':
                return round($seconds / self::MONTH);

            case 'C__GUARANTEE_PERIOD_UNIT_YEARS':
                return round($seconds / self::YEAR);
        }

        return $seconds;
    }

    /**
     * Converts a period beginning at $p_from_date to seconds.
     *
     * @param  integer $period
     * @param  int|string $unit
     *
     * @return integer
     * @todo   Merge with seconds_to_period - Maybe even replace with isys_convert::time();
     */
    public static function period_to_seconds($period, $unit)
    {
        if ($period === null || !is_numeric($period)) {
            return null;
        }

        if (is_numeric($unit)) {
            $database = isys_application::instance()->container->get('database');
            $unit = isys_factory_cmdb_dialog_dao::get_instance('isys_guarantee_period_unit', $database)
                ->get_data($unit)['isys_guarantee_period_unit__const'];
        }

        switch ($unit) {
            case 'C__GUARANTEE_PERIOD_UNIT_DAYS':
                return $period * self::DAY;

            case 'C__GUARANTEE_PERIOD_UNIT_WEEKS':
                return $period * self::WEEK;

            case 'C__GUARANTEE_PERIOD_UNIT_MONTH':
                return $period * self::MONTH;

            case 'C__GUARANTEE_PERIOD_UNIT_YEARS':
                return $period * self::YEAR;
        }

        return $period;
    }

    /**
     * Converts KHz, MHz, GHz, THz.
     *
     * @param   int|float  $value May be an integer or an float.
     * @param   int|string $unit    May be an integer or the unit-constant.
     * @param   integer    $direction
     *
     * @return  mixed  Float or integer.
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public static function frequency($value, $unit, $direction = null)
    {
        if ($value === null || !is_numeric($value)) {
            return null;
        }

        if (is_numeric($unit)) {
            $database = isys_application::instance()->container->get('database');
            $unit = isys_factory_cmdb_dialog_dao::get_instance('isys_frequency_unit', $database)
                ->get_data($unit)['isys_frequency_unit__const'];
        }

        switch ($direction) {
            default:
            case C__CONVERT_DIRECTION__FORMWARD:
                switch ($unit) {
                    case 'C__FREQUENCY_UNIT__KHZ':
                        return $value * self::HERTZ;

                    case 'C__FREQUENCY_UNIT__MHZ':
                        return $value * (self::HERTZ ** 2);

                    case 'C__FREQUENCY_UNIT__GHZ':
                        return $value * (self::HERTZ ** 3);

                    case 'C__FREQUENCY_UNIT__THZ':
                        return $value * (self::HERTZ ** 4);
                }
                break;

            case C__CONVERT_DIRECTION__BACKWARD:
                switch ($unit) {
                    case 'C__FREQUENCY_UNIT__KHZ':
                        return $value / self::HERTZ;

                    case 'C__FREQUENCY_UNIT__MHZ':
                        return $value / (self::HERTZ ** 2);

                    case 'C__FREQUENCY_UNIT__GHZ':
                        return $value / (self::HERTZ ** 3);

                    case 'C__FREQUENCY_UNIT__THZ':
                        return $value / (self::HERTZ ** 4);
                }
        }

        return (float)$value;
    }

    /**
     * Converts B, KB, MB, GB, TB.
     *
     * @param  int|float  $value May be an integer or an float.
     * @param  int|string $unit  May be an integer or the unit-constant.
     * @param  integer    $direction
     *
     * @return float
     * @author Leonard Fischer <lfischer@i-doit.org>
     */
    public static function memory($value, $unit, $direction = null)
    {
        if ($direction === C__CONVERT_DIRECTION__FORMWARD) {
            $value = isys_helper::filter_number($value);
        }

        if ($value === null || !is_numeric($value)) {
            return null;
        }

        if (is_numeric($unit)) {
            $database = isys_application::instance()->container->get('database');
            $unit = isys_factory_cmdb_dialog_dao::get_instance('isys_memory_unit', $database)
                ->get_data($unit)['isys_memory_unit__const'];
        }

        switch ($direction) {
            default:
            case C__CONVERT_DIRECTION__FORMWARD:
                switch ($unit) {
                    case 'C__MEMORY_UNIT__KB':
                        return $value * self::BYTE;

                    case 'C__MEMORY_UNIT__MB':
                        return $value * (self::BYTE ** 2);

                    case 'C__MEMORY_UNIT__GB':
                        return $value * (self::BYTE ** 3);

                    case 'C__MEMORY_UNIT__TB':
                        return $value * (self::BYTE ** 4);
                }
                break;

            case C__CONVERT_DIRECTION__BACKWARD:
                switch ($unit) {
                    case 'C__MEMORY_UNIT__KB':
                        return $value / self::BYTE;

                    case 'C__MEMORY_UNIT__MB':
                        return $value / (self::BYTE ** 2);

                    case 'C__MEMORY_UNIT__GB':
                        return $value / (self::BYTE ** 3);

                    case 'C__MEMORY_UNIT__TB':
                        return $value / (self::BYTE ** 4);
                }
        }

        return (float)$value;
    }

    /**
     * @param  int|float $value
     *
     * @return string
     */
    public static function formatNumber($value)
    {
        try {
            return isys_application::instance()->container->get('locales')->fmt_numeric($value);
        } catch (isys_exception_locale $e) {
            try {
                return isys_locale::get_instance()->fmt_numeric($value);
            } catch (isys_exception_locale $e) {
                return number_format($value, 2, '.', '');
            }
        }
    }

    /**
     * Function to get and convert the capacity or memory in callbacks
     * $columnNamePart is the last part of the capacity/memory column name in the table of the database
     *
     * @param array                  $rowData
     * @param isys_cmdb_dao_category $dao
     * @param string                 $columnNamePart
     *
     * @return mixed|string
     */
    public static function retrieveFormattedMemoryByDao($rowData, isys_cmdb_dao_category $dao, $columnNamePart)
    {
        $tableName = $dao->get_table();

        //check if id is set
        if (isset($rowData[$tableName . '__id'])) {
            //setting unit column
            $unitId = $tableName . '__isys_memory_unit__id';
            //setting capacity/memory column
            $columnName = $tableName . $columnNamePart;

            $driveResult = $dao->get_data($rowData[$tableName . '__id']);

            //check if more than 0 drives exists
            if (!empty($driveResult)) {
                $driveRow = $driveResult->get_row();

                //check if capacity/memory and unit are set
                if ($driveRow[$columnName] > 0 && isset($driveRow[$unitId])) {
                    //return capacity/memory in correct numeric format with unit
                    return isys_convert::formatNumber(isys_convert::memory($driveRow[$columnName], $driveRow[$unitId], C__CONVERT_DIRECTION__BACKWARD)) . ' ' .
                        isys_factory_cmdb_dialog_dao::get_instance('isys_memory_unit', isys_application::instance()->container->get('database'))
                            ->get_data($driveRow[$unitId])['isys_memory_unit__title'];
                }
            }
        }

        //return empty when nothing is set
        return isys_tenantsettings::get('gui.empty_value', '-');
    }

    /**
     * Helper method to retrieve the memory unit const
     *
     * @param      $p_value
     * @param bool $p_as_string
     *
     * @return int|string
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public static function get_memory_unit_const($p_value, $p_as_string = false)
    {
        if ($p_value >= (self::BYTE ** 4)) {
            return ($p_as_string) ? 'C__MEMORY_UNIT__TB' : defined_or_default('C__MEMORY_UNIT__TB');
        }

        if ($p_value >= (self::BYTE ** 3)) {
            return ($p_as_string) ? 'C__MEMORY_UNIT__GB' : defined_or_default('C__MEMORY_UNIT__GB');
        }

        if ($p_value >= (self::BYTE ** 2)) {
            return ($p_as_string) ? 'C__MEMORY_UNIT__MB' : defined_or_default('C__MEMORY_UNIT__MB');
        }

        if ($p_value >= self::BYTE) {
            return ($p_as_string) ? 'C__MEMORY_UNIT__KB' : defined_or_default('C__MEMORY_UNIT__KB');
        }

        return ($p_as_string) ? 'C__MEMORY_UNIT__B' : defined_or_default('C__MEMORY_UNIT__B');
    }

    /**
     * Converts mm, cm and inch.
     *
     * @param  int|float  $p_value May be an integer or an float.
     * @param  int|string $unit  May be an integer or the unit-constant.
     * @param  integer    $p_direction
     *
     * @return float
     * @throws Exception
     * @author Dennis Stücken <dstuecken@synetics.de>
     */
    public static function measure($p_value, $unit, $p_direction = null)
    {
        if ($p_value === null || !is_numeric($p_value)) {
            return null;
        }

        if (is_numeric($unit)) {
            $database = isys_application::instance()->container->get('database');
            $unit = isys_factory_cmdb_dialog_dao::get_instance('isys_depth_unit', $database)
                ->get_data($unit)['isys_depth_unit__const'];
        }

        switch ($p_direction) {
            default:
            case C__CONVERT_DIRECTION__FORMWARD:
                switch ($unit) {
                    case 'C__DEPTH_UNIT__CM':
                        return $p_value * 10;

                    case 'C__DEPTH_UNIT__INCH':
                        return $p_value * self::INCH;

                    case 'C__DEPTH_UNIT__FOOT':
                        return $p_value * self::FOOT;

                    case 'C__DEPTH_UNIT__METER':
                        return $p_value * 1000;

                    case 'C__DEPTH_UNIT__KILOMETER':
                        return $p_value * 1000000;
                }
                break;

            case C__CONVERT_DIRECTION__BACKWARD:
                switch ($unit) {
                    case 'C__DEPTH_UNIT__CM':
                        return $p_value / 10;

                    case 'C__DEPTH_UNIT__INCH':
                        return $p_value / self::INCH;

                    case 'C__DEPTH_UNIT__FOOT':
                        return $p_value / self::FOOT;

                    case 'C__DEPTH_UNIT__METER':
                        return $p_value / 1000;

                    case 'C__DEPTH_UNIT__KILOMETER':
                        return $p_value / 1000000;
                }
        }

        return (float)$p_value;
    }

    /**
     * Converts Bit/s, KBit/s, MBit/s and GBit/s.
     *
     * @param  int|float  $value May be an integer or an float.
     * @param  int|string $unit  May be an integer or the unit-constant.
     * @param  integer    $direction
     *
     * @return float
     * @throws Exception
     * @author Leonard Fischer <lfischer@i-doit.org>
     */
    public static function speed($value, $unit, $direction = null)
    {
        if ($value === null || !is_numeric($value)) {
            return null;
        }

        if (is_numeric($unit)) {
            $database = isys_application::instance()->container->get('database');
            $unit = isys_factory_cmdb_dialog_dao::get_instance('isys_port_speed', $database)
                ->get_data($unit)['isys_port_speed__const'];
        }

        switch ($direction) {
            default:
            case C__CONVERT_DIRECTION__FORMWARD:
                switch ($unit) {
                    case 'C__PORT_SPEED__KBIT_S':
                        return $value * 1000;

                    case 'C__PORT_SPEED__MBIT_S':
                        return $value * 1000000;

                    case 'C__PORT_SPEED__GBIT_S':
                        return $value * 1000000000;
                }
                break;

            case C__CONVERT_DIRECTION__BACKWARD:
                switch ($unit) {
                    case 'C__PORT_SPEED__KBIT_S':
                        return $value / 1000;

                    case 'C__PORT_SPEED__MBIT_S':
                        return $value / 1000000;

                    case 'C__PORT_SPEED__GBIT_S':
                        return $value / 1000000000;
                }
        }

        return (float)$value;
    }

    /**
     * Converts Bit/s, KBit/s, MBit/s and GBit/s.
     *
     * @param  mixed   $value May be an integer or an float.
     * @param  mixed   $unit  May be an integer or the unit-constant as string.
     * @param  integer $direction
     *
     * @return mixed  Float or integer.
     * @throws Exception
     * @author Leonard Fischer <lfischer@i-doit.org>
     * @todo   Merge this to the existing speed method (+ migration).
     */
    public static function speed_wan($value, $unit, $direction = null)
    {
        if ($value === null || !is_numeric($value)) {
            return null;
        }

        if (is_numeric($unit)) {
            $database = isys_application::instance()->container->get('database');
            $unit = isys_factory_cmdb_dialog_dao::get_instance('isys_wan_capacity_unit', $database)
                ->get_data($unit)['isys_wan_capacity_unit__const'];
        }

        switch ($direction) {
            default:
            case C__CONVERT_DIRECTION__FORMWARD:
                switch ($unit) {
                    default:
                    case 'C__WAN_CAPACITY_UNIT__BITS':
                        return (float)$value;

                    case 'C__WAN_CAPACITY_UNIT__KBITS':
                        return $value * 1000;

                    case 'C__WAN_CAPACITY_UNIT__MBITS':
                        return $value * 1000000;

                    case 'C__WAN_CAPACITY_UNIT__GBITS':
                        return $value * 1000000000;

                    case 'C__WAN_CAPACITY_UNIT__TBITS':
                        return $value * 1000000000000;
                }
                break;

            case C__CONVERT_DIRECTION__BACKWARD:
                switch ($unit) {
                    default:
                    case 'C__WAN_CAPACITY_UNIT__BITS':
                        return (float)$value;

                    case 'C__WAN_CAPACITY_UNIT__KBITS':
                        return $value / 1000;

                    case 'C__WAN_CAPACITY_UNIT__MBITS':
                        return $value / 1000000;

                    case 'C__WAN_CAPACITY_UNIT__GBITS':
                        return $value / 1000000000;

                    case 'C__WAN_CAPACITY_UNIT__TBITS':
                        return $value / 1000000000000;
                }
        }
    }

    /**
     * Converts seconds, minutes, hours, days, months and years.
     *
     * @param  int|float   $value May be an integer or an float.
     * @param  int|string  $unit  May be an integer or the unit-constant.
     * @param  integer     $p_direction
     *
     * @return float
     * @throws Exception
     * @author Leonard Fischer <lfischer@i-doit.org>
     */
    public static function time($value, $unit, $p_direction = null)
    {
        if ($value === null || !is_numeric($value)) {
            return null;
        }

        if (is_numeric($unit)) {
            $database = isys_application::instance()->container->get('database');
            $unit = isys_factory_cmdb_dialog_dao::get_instance('isys_unit_of_time', $database)
                ->get_data($unit)['isys_unit_of_time__const'];
        }

        switch ($p_direction) {
            default:
            case C__CONVERT_DIRECTION__FORMWARD:
                switch ($unit) {
                    case 'C__CMDB__UNIT_OF_TIME__SECOND':
                        return $value;

                    case 'C__CMDB__UNIT_OF_TIME__MINUTE':
                        return $value * self::MINUTE;

                    case 'C__CMDB__UNIT_OF_TIME__HOUR':
                        return $value * self::HOUR;

                    case 'C__CMDB__UNIT_OF_TIME__DAY':
                        return $value * self::DAY;

                    case 'C__CMDB__UNIT_OF_TIME__MONTH':
                        return $value * self::MONTH;

                    case 'C__CMDB__UNIT_OF_TIME__YEAR':
                        return $value * self::YEAR;
                }
                break;

            case C__CONVERT_DIRECTION__BACKWARD:
                switch ($unit) {
                    case 'C__CMDB__UNIT_OF_TIME__SECOND':
                        return $value;

                    case 'C__CMDB__UNIT_OF_TIME__MINUTE':
                        return $value / self::MINUTE;

                    case 'C__CMDB__UNIT_OF_TIME__HOUR':
                        return $value / self::HOUR;

                    case 'C__CMDB__UNIT_OF_TIME__DAY':
                        return $value / self::DAY;

                    case 'C__CMDB__UNIT_OF_TIME__MONTH':
                        return $value / self::MONTH;

                    case 'C__CMDB__UNIT_OF_TIME__YEAR':
                        return $value / self::YEAR;
                }
                break;
        }

        return (float)$value;
    }

    /**
     * Converts a ini-value to bytes (128M or 1G, ...).
     *
     * @param  string $p_value
     *
     * @return integer
     * @author Leonard Fischer <lfischer@i-doit.org>
     */
    public static function to_bytes($p_value)
    {
        if ($p_value === null || !is_numeric(substr($p_value, 0, -1))) {
            return null;
        }

        $l_unit = strtolower(substr($p_value, -1));
        $l_return = (int)$p_value;

        switch ($l_unit) {
            case 'g':
                return $l_return * (self::BYTE ** 3);

            case 'm':
                return $l_return * (self::BYTE ** 2);

            case 'k':
                return $l_return * self::BYTE;

            case 'b':
                return $l_return;
        }

        return null;
    }

    /**
     * Converts ml and liter.
     *
     * @param  int|float   $value  May be an integer or an float.
     * @param  int|string  $unit   May be an integer or the unit-constant.
     * @param  integer     $direction
     *
     * @return mixed  Float or integer.
     * @throws Exception
     * @author Leonard Fischer <lfischer@i-doit.org>
     */
    public static function volume($value, $unit, $direction = null)
    {
        if ($value === null || !is_numeric($value)) {
            return null;
        }

        if (is_numeric($unit)) {
            $database = isys_application::instance()->container->get('database');
            $unit = isys_factory_cmdb_dialog_dao::get_instance('isys_volume_unit', $database)
                ->get_data($unit)['isys_volume_unit__const'];
        }

        switch ($direction) {
            default:
            case C__CONVERT_DIRECTION__FORMWARD:
                switch ($unit) {
                    case 'C__VOLUME_UNIT__ML':
                        return $value;

                    case 'C__VOLUME_UNIT__L':
                        return $value * 100;
                }
                break;

            case C__CONVERT_DIRECTION__BACKWARD:
                switch ($unit) {
                    case 'C__VOLUME_UNIT__ML':
                        return $value;

                    case 'C__VOLUME_UNIT__L':
                        return $value / 100;
                }
                break;
        }

        return (float)$value;
    }

    /**
     * Converts Watt and BTU.
     *
     * @param  int|float   $value  May be an integer or an float.
     * @param  int|string  $unit   May be an integer or the unit-constant.
     * @param  integer     $direction
     *
     * @return float
     * @throws Exception
     * @author Leonard Fischer <lfischer@i-doit.org>
     */
    public static function watt($value, $unit, $direction = null)
    {
        if ($value === null || !is_numeric($value)) {
            return null;
        }

        if (is_numeric($unit)) {
            $database = isys_application::instance()->container->get('database');
            $unit = isys_factory_cmdb_dialog_dao::get_instance('isys_ac_refrigerating_capacity_unit', $database)
                ->get_data($unit)['isys_ac_refrigerating_capacity_unit__const'];
        }

        switch ($direction) {
            default:
            case C__CONVERT_DIRECTION__FORMWARD:
                switch ($unit) {
                    case 'C__REF_CAPACITY_UNIT__KWATT':
                        return $value * 1000;

                    case 'C__REF_CAPACITY_UNIT__MWATT':
                        return $value * 1000000;

                    case 'C__REF_CAPACITY_UNIT__GWATT':
                        return $value * 1000000000;

                    case 'C__REF_CAPACITY_UNIT__BTU':
                        return $value * 3.414;
                }
                break;

            case C__CONVERT_DIRECTION__BACKWARD:
                switch ($unit) {
                    case 'C__REF_CAPACITY_UNIT__KWATT':
                        return $value / 1000;

                    case 'C__REF_CAPACITY_UNIT__MWATT':
                        return $value / 1000000;

                    case 'C__REF_CAPACITY_UNIT__GWATT':
                        return $value / 1000000000;

                    case 'C__REF_CAPACITY_UNIT__BTU':
                        return $value / 3.414;
                }
                break;
        }

        return (float)$value;
    }

    /**
     * Converts g, kg and t.
     *
     * @param  int|float   $value  May be an integer or an float.
     * @param  int|string  $unit   May be an integer or the unit-constant.
     * @param  integer     $direction
     *
     * @return float
     * @throws Exception
     * @author Leonard Fischer <lfischer@i-doit.org>
     */
    public static function weight($value, $unit, $direction = null)
    {
        if ($value === null || !is_numeric($value)) {
            return null;
        }

        if (is_numeric($unit)) {
            $database = isys_application::instance()->container->get('database');
            $unit = isys_factory_cmdb_dialog_dao::get_instance('isys_weight_unit', $database)
                ->get_data($unit)['isys_weight_unit__const'];
        }

        switch ($direction) {
            default:
            case C__CONVERT_DIRECTION__FORMWARD:
                switch ($unit) {
                    case 'C__WEIGHT_UNIT__KG':
                        return $value * 1000;

                    case 'C__WEIGHT_UNIT__T':
                        return $value * 1000000;
                }
                break;

            case C__CONVERT_DIRECTION__BACKWARD:
                switch ($unit) {
                    case 'C__WEIGHT_UNIT__KG':
                        return $value / 1000;

                    case 'C__WEIGHT_UNIT__T':
                        return $value / 1000000;
                }
                break;
        }

        return (float)$value;
    }

    /**
     * Since these values are not calculated, we simply return them.
     * Method is necessary for "unified" return values for "units".
     *
     * @param  int|float   $value  May be an integer or an float.
     * @param  int|string  $unit   May be an integer or the unit-constant.
     * @param  integer     $direction
     *
     * @return float
     */
    public static function temperature($value, $unit, $direction = null)
    {
        return (float)$value;
    }
}

<?php

namespace idoit\Component\Helper;

use DateTime;
use idoit\Exception\DateException;
use isys_application;

/**
 * i-doit Date-Helper.
 *
 * @package     i-doit
 * @subpackage  Component
 * @author      Leonard Fischer <lfischer@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 * @since       1.10.1
 */
class Date
{
    const MONDAY    = 'monday';
    const TUESDAY   = 'tuesday';
    const WEDNESDAY = 'wednesday';
    const THURSDAY  = 'thursday';
    const FRIDAY    = 'friday';
    const SATURDAY  = 'saturday';
    const SUNDAY    = 'sunday';

    /**
     * @var array
     */
    private static $days = [
        0 => 'LC__UNIVERSAL__CALENDAR__DAYS_SUNDAY',
        1 => 'LC__UNIVERSAL__CALENDAR__DAYS_MONDAY',
        2 => 'LC__UNIVERSAL__CALENDAR__DAYS_TUESDAY',
        3 => 'LC__UNIVERSAL__CALENDAR__DAYS_WEDNESDAY',
        4 => 'LC__UNIVERSAL__CALENDAR__DAYS_THURSDAY',
        5 => 'LC__UNIVERSAL__CALENDAR__DAYS_FRIDAY',
        6 => 'LC__UNIVERSAL__CALENDAR__DAYS_SATURDAY',
        7 => 'LC__UNIVERSAL__CALENDAR__DAYS_SUNDAY'
    ];

    /**
     * @var array
     */
    private static $months = [
        1  => 'LC__UNIVERSAL__CALENDAR__MONTHS_JANUARY',
        2  => 'LC__UNIVERSAL__CALENDAR__MONTHS_FEBRUARY',
        3  => 'LC__UNIVERSAL__CALENDAR__MONTHS_MARCH',
        4  => 'LC__UNIVERSAL__CALENDAR__MONTHS_APRIL',
        5  => 'LC__UNIVERSAL__CALENDAR__MONTHS_MAY',
        6  => 'LC__UNIVERSAL__CALENDAR__MONTHS_JUNE',
        7  => 'LC__UNIVERSAL__CALENDAR__MONTHS_JULY',
        8  => 'LC__UNIVERSAL__CALENDAR__MONTHS_AUGUST',
        9  => 'LC__UNIVERSAL__CALENDAR__MONTHS_SEPTEMBER',
        10 => 'LC__UNIVERSAL__CALENDAR__MONTHS_OCTOBER',
        11 => 'LC__UNIVERSAL__CALENDAR__MONTHS_NOVEMBER',
        12 => 'LC__UNIVERSAL__CALENDAR__MONTHS_DECEMBER'
    ];

    /**
     * @param integer $day
     *
     * @return string
     * @throws DateException
     */
    public static function getDayName($day = null)
    {
        if ($day === null) {
            $day = date('N');
        }

        if (isset(self::$days[$day])) {
            return isys_application::instance()->container->get('language')
                ->get(self::$days[$day]);
        }

        throw new DateException('Please provide a numeric parameter from 0 to 7.');
    }

    /**
     * @param integer $month
     *
     * @return string
     * @throws DateException
     */
    public static function getMonthName($month = null)
    {
        if ($month === null) {
            $month = date('n');
        }

        if (isset(self::$months[$month])) {
            return isys_application::instance()->container->get('language')
                ->get(self::$months[$month]);
        }

        throw new DateException('Please provide a numeric parameter from 1 to 12.');
    }

    /**
     * Returns the relative week of the month by providing a day.
     * For example:
     *    5.March 2018 => 1, because it is the first monday of the month.
     *    13.November 2017 => 2, because it is the second monday of the month.
     *    22.June 2017 => 4, because it is the fourth thursday of the month.
     *
     * @param DateTime $date
     * @param boolean  $translated
     *
     * @return mixed
     */
    public static function getRelativeWeekOfMonthByDay(DateTime $date = null, $translated = false)
    {
        if ($date === null) {
            $date = new DateTime();
        }

        $weekOfMonth = ceil($date->format('j') / 7);

        if (!$translated) {
            return $weekOfMonth;
        }

        switch ($weekOfMonth) {
            default:
            case 1:
                return isys_application::instance()->container->get('language')
                    ->get('LC__UNIVERSAL__FIRST');
            case 2:
                return isys_application::instance()->container->get('language')
                    ->get('LC__UNIVERSAL__SECOND');
            case 3:
                return isys_application::instance()->container->get('language')
                    ->get('LC__UNIVERSAL__THIRD');
            case 4:
                return isys_application::instance()->container->get('language')
                    ->get('LC__UNIVERSAL__FOURTH');
            case 5:
                return isys_application::instance()->container->get('language')
                    ->get('LC__UNIVERSAL__FIFTH');
        }
    }

    /**
     * @param integer $hour
     *
     * @return string
     */
    public static function getDaytimeGreeting($hour)
    {
        if ($hour === null) {
            $hour = date('H');
        }

        switch ($hour) {
            default:
                return isys_application::instance()->container->get('language')
                    ->get('LC_UNIVERSAL__HELLO');
            case ($hour < 6 || $hour >= 22):
                return isys_application::instance()->container->get('language')
                    ->get('LC_UNIVERSAL__DATE__GOOD_NIGHT');
            case ($hour < 12):
                return isys_application::instance()->container->get('language')
                    ->get('LC_UNIVERSAL__DATE__GOOD_MORNING');
            case ($hour < 18):
                return isys_application::instance()->container->get('language')
                    ->get('LC_UNIVERSAL__DATE__GOOD_DAY');
            case ($hour < 22):
                return isys_application::instance()->container->get('language')
                    ->get('LC_UNIVERSAL__DATE__GOOD_EVENING');
        }
    }
}

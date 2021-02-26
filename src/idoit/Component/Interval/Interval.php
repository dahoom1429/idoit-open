<?php

namespace idoit\Component\Interval;

use Carbon\Carbon;
use Carbon\CarbonInterval;
use DatePeriod;
use DateTime;
use Exception;
use idoit\Component\Helper\Date;
use idoit\Exception\DateException;
use idoit\Exception\IntervalException;

/**
 * i-doit Interval-Helper.
 *
 * @package     i-doit
 * @subpackage  Component
 * @author      Leonard Fischer <lfischer@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 * @since       1.10.1
 */
class Interval
{
    /**
     * @var Carbon
     */
    private $checkDate;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var int
     */
    private $eventCounter = 0;

    /**
     * @var bool
     */
    private $intervalDue = false;

    /**
     * @var DateTime
     */
    private $lastIntervalDate;

    /**
     * @var DateTime
     */
    private $nextIntervalDate;

    /**
     * @var bool
     */
    private $prepared = false;

    /**
     * @return Config
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @param Config $config
     *
     * @return $this
     */
    public function setConfig(Config $config)
    {
        $this->config = $config;
        $this->prepared = false;

        return $this;
    }

    /**
     * @return Carbon
     */
    public function getCheckDate()
    {
        return $this->checkDate;
    }

    /**
     * @param DateTime $dateTime
     *
     * @return $this
     */
    public function setCheckDate(DateTime $dateTime)
    {
        $this->checkDate = Carbon::instance($dateTime);
        $this->prepared = false;

        return $this;
    }

    /**
     * Check if the intervala has expired.
     *
     * @return bool
     * @throws IntervalException
     */
    public function isIntervalExpired()
    {
        $this->prepare();

        // If the initialDate is beyond the checkDate, the interval has expired before it began.
        if ($this->config->getExpireIfCheckDateIsBeforeInitialDate() && $this->getStartDate()
                ->diffInDays($this->checkDate, false) < 0) {
            return true;
        }

        // Check by different end conditions.
        switch ($this->config->getEndAfter()) {
            case Config::END_AFTER_NEVER:
                return false;

            case Config::END_AFTER_DATE:
                return $this->checkDate->greaterThanOrEqualTo(Carbon::instance($this->config->getEndDetails()));

            case Config::END_AFTER_EVENTS:
                return ($this->eventCounter >= $this->config->getEndDetails());

            default:
                throw new IntervalException('The configuration has no valid "end after" option');
        }
    }

    /**
     * @return bool
     */
    public function hasLastIntervalDate()
    {
        $this->prepare();

        return ($this->lastIntervalDate instanceof DateTime);
    }

    /**
     * @return DateTime
     */
    public function getLastIntervalDate()
    {
        $this->prepare();

        return $this->lastIntervalDate;
    }

    /**
     * @return bool
     */
    public function isIntervalReached()
    {
        $this->prepare();

        return $this->intervalDue;
    }

    /**
     * @return bool
     */
    public function hasNextIntervalDate()
    {
        $this->prepare();

        return ($this->nextIntervalDate instanceof DateTime);
    }

    /**
     * @return DateTime
     */
    public function getNextIntervalDate()
    {
        $this->prepare();

        return $this->nextIntervalDate;
    }

    /**
     * Method for retrieving all previous intervals up to a given date.
     * Don't forget to re-set your "Check Date" after using this method!
     *
     * @param DateTime $until
     * @param array    $dates
     *
     * @return null|DateTime[]
     */
    public function getAllPreviousIntervals(DateTime $until, array &$dates = [])
    {
        try {
            if ($this->isIntervalExpired() || $this->getCheckDate()
                    ->lessThan(Carbon::instance($until))) {
                return null;
            }

            if ($this->hasLastIntervalDate() && Carbon::instance($this->getLastIntervalDate())
                    ->greaterThanOrEqualTo(Carbon::instance($until))) {
                $dates[] = $this->getLastIntervalDate();

                $this->setCheckDate($this->getLastIntervalDate());

                if ($this->hasLastIntervalDate()) {
                    $this->getAllPreviousIntervals($until, $dates);
                }
            }

            return $dates;
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Method for retrieving all upcoming intervals up to a given date.
     * Don't forget to re-set your "Check Date" after using this method!
     *
     * @param DateTime $until
     * @param array    $dates
     *
     * @return null|DateTime[]
     */
    public function getAllUpcomingIntervals(DateTime $until, array &$dates = [])
    {
        try {
            if ($this->isIntervalExpired() || $this->getCheckDate()
                    ->greaterThan(Carbon::instance($until))) {
                return null;
            }

            if ($this->hasNextIntervalDate() && Carbon::instance($this->getNextIntervalDate())
                    ->lessThanOrEqualTo(Carbon::instance($until))) {
                $dates[] = $this->getNextIntervalDate();

                $this->setCheckDate($this->getNextIntervalDate());

                if ($this->hasNextIntervalDate()) {
                    $this->getAllUpcomingIntervals($until, $dates);
                }
            }

            return $dates;
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Prepare method for the given configuration and check date.
     */
    private function prepare()
    {
        if (!$this->prepared) {
            $this->lastIntervalDate = null;
            $this->intervalDue = false;
            $this->nextIntervalDate = null;
            $this->eventCounter = 0;

            // Now we prepare some things to reduce the workload later on...
            switch ($this->config->getRepeatEveryUnit()) {
                case Config::REPEAT_UNIT_DAY:
                    $this->iterateDays();
                    break;

                case Config::REPEAT_UNIT_WEEK:
                    $this->iterateWeeks();
                    break;

                case Config::REPEAT_UNIT_MONTH:
                    $this->iterateMonths();
                    break;

                case Config::REPEAT_UNIT_YEAR:
                    $this->iterateYears();
                    break;
            }

            $this->prepared = true;
        }
    }

    /**
     * Get the last and next iteration. Also check if the interval is due today!
     */
    private function iterateDays()
    {
        $datePeriod = new DatePeriod($this->getStartDate(), CarbonInterval::days($this->config->getRepeatEvery()), $this->getEndDate());

        $this->iteratePeriod($datePeriod);
    }

    /**
     * Get the last and next iteration. Also check if the interval is due today!
     */
    private function iterateWeeks()
    {
        $details = $this->config->getRepeatDetails();

        $datePeriod = new DatePeriod($this->getStartDate(), CarbonInterval::weeks($this->config->getRepeatEvery()), $this->getEndDate());

        $this->iteratePeriod($datePeriod, function (Carbon $carbon) use ($details) {
            $return = [];

            $monday = $carbon->startOfWeek();

            if (in_array(Date::MONDAY, $details, true)) {
                $return[] = $monday;
            }

            if (in_array(Date::TUESDAY, $details, true)) {
                $return[] = $monday->copy()
                    ->addDays(1);
            }

            if (in_array(Date::WEDNESDAY, $details, true)) {
                $return[] = $monday->copy()
                    ->addDays(2);
            }

            if (in_array(Date::THURSDAY, $details, true)) {
                $return[] = $monday->copy()
                    ->addDays(3);
            }

            if (in_array(Date::FRIDAY, $details, true)) {
                $return[] = $monday->copy()
                    ->addDays(4);
            }

            if (in_array(Date::SATURDAY, $details, true)) {
                $return[] = $monday->copy()
                    ->addDays(5);
            }

            if (in_array(Date::SUNDAY, $details, true)) {
                $return[] = $monday->copy()
                    ->addDays(6);
            }

            return $return;
        });
    }

    /**
     * Get the last and next iteration. Also check if the interval is due today!
     */
    private function iterateMonths()
    {
        $startDate = $this->getStartDate();
        $relative = ($this->config->getRepeatDetails() === 'relative');

        // For some reason relative intervals have a offset of a month.
        if ($relative) {
            $startDate->addMonth();
        }

        $datePeriod = new DatePeriod($startDate, CarbonInterval::months($this->config->getRepeatEvery()), $this->getEndDate());

        $this->iteratePeriod($datePeriod, function (Carbon $carbon) use ($relative) {
            if ($relative) {
                $carbon = $this->getRelativeDayInMonth($this->getStartDate(), $carbon);
            }

            return $carbon;
        });
    }

    /**
     * Get the last and next iteration. Also check if the interval is due today!
     */
    private function iterateYears()
    {
        $datePeriod = new DatePeriod($this->getStartDate(), CarbonInterval::years($this->config->getRepeatEvery()), $this->getEndDate());

        $this->iteratePeriod($datePeriod);
    }

    /**
     * Method for iterating the given periods. With the help of the formatter you can provide specific dates (see relative month or week).
     *
     * @param DatePeriod $period
     * @param callable   $formatter
     */
    private function iteratePeriod(DatePeriod $period, callable $formatter = null)
    {
        $hour = $this->getStartDate()->hour;
        $minute = $this->getStartDate()->minute;
        $second = $this->getStartDate()->second;

        foreach ($period as $dates) {
            /** @var Carbon $dates */
            if ($formatter !== null) {
                $dates = $formatter($dates);
            }

            // We need another array because of the "week" iterator which can return multiple entries.
            if (!is_array($dates)) {
                $dates = [$dates];
            }

            foreach ($dates as $date) {
                // Keep the time, when iterating!
                $date->setTime($hour, $minute, $second);

                /** @var Carbon $date */
                if ($date->diffInDays($this->checkDate, false) > 0) {
                    $this->eventCounter++;
                    $this->lastIntervalDate = $date;

                    if ($this->config->getEndAfter() === Config::END_AFTER_EVENTS && $this->eventCounter >= $this->config->getEndDetails()) {
                        break 2;
                    }
                }

                if ($date->diffInDays($this->checkDate, false) === 0) {
                    $this->eventCounter++;
                    $this->intervalDue = true;

                    if ($this->config->getEndAfter() === Config::END_AFTER_EVENTS && $this->eventCounter >= $this->config->getEndDetails()) {
                        break 2;
                    }
                }

                if ($date->diffInDays($this->checkDate, false) < 0) {
                    $this->nextIntervalDate = $date;

                    break 2;
                }

            }
        }
    }

    /**
     * @return Carbon
     */
    private function getStartDate()
    {
        return Carbon::instance($this->config->getInitialDate());
    }

    /**
     * @return Carbon|null
     * @throws DateException
     */
    private function getEndDate()
    {
        switch ($this->config->getEndAfter()) {
            default:
                // Default and "never" date is today in 100 years, because we NEED a end date for DatePeriod.
            case Config::END_AFTER_NEVER:
            case Config::END_AFTER_EVENTS:
                // The "end after events" date is unknown until AFTER $this->prepare(). Therefore we also work with "today in 100 years".
                return Carbon::create(date('Y') + 100);

            case Config::END_AFTER_DATE:
                return Carbon::instance($this->config->getEndDetails());
        }
    }

    /**
     * Helper method for retrieving the relative date of a given month in context of another given month.
     *
     * @param Carbon $originalDate This will be used to find out the relative date ("first monday", "third thursday", ...)
     * @param Carbon $searchDate   This will be used as context (year and month).
     *
     * @return Carbon
     */
    private function getRelativeDayInMonth(Carbon $originalDate, Carbon $searchDate)
    {
        $weekModifier = ['', 'first ', 'second ', 'third ', 'fourth ', 'fifth ', 'sixth '];
        $weekNumber = (int)Date::getRelativeWeekOfMonthByDay($originalDate);

        // Set the day to the first and add the weeks.
        $resultDate = $searchDate->copy()
            ->day(1);

        if ($resultDate->dayOfWeek === $originalDate->dayOfWeek) {
            // Because of the "modify" logic we need to remove a week if the first of the month is the desired weekday.
            $weekNumber--;
        }

        // Modify the date!
        $resultDate->modify($weekModifier[$weekNumber] . $originalDate->format('l'));

        // This can happen, when the relative date exceeds the month (fifth sunday of month => first sunday of next month).
        if ($searchDate->month === $resultDate->month) {
            return $resultDate;
        }

        return $searchDate->copy()
            ->day(1)
            ->modify('last ' . $originalDate->format('l'));
    }

    /**
     * Interval constructor.
     *
     * @param Config   $config
     * @param DateTime $checkForDate
     */
    public function __construct(Config $config, DateTime $checkForDate)
    {
        $this->config = $config;
        $this->checkDate = Carbon::instance($checkForDate);
    }
}
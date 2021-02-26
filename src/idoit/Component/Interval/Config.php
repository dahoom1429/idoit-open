<?php

namespace idoit\Component\Interval;

use DateTime;
use Exception;
use idoit\Component\Helper\Date;
use idoit\Exception\DateException;
use isys_application as App;
use isys_exception_general as GeneralException;
use isys_format_json as JSON;

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
class Config
{
    const REPEAT_UNIT_DAY   = 'day';
    const REPEAT_UNIT_WEEK  = 'week';
    const REPEAT_UNIT_MONTH = 'month';
    const REPEAT_UNIT_YEAR  = 'year';

    const END_AFTER_NEVER  = 'never';
    const END_AFTER_DATE   = 'date';
    const END_AFTER_EVENTS = 'events';

    /**
     * @var string
     */
    private $endAfter = self::END_AFTER_NEVER;

    /**
     * @var mixed
     */
    private $endDetails;

    /**
     * @var bool
     */
    private $expireIfCheckDateIsBeforeInitialDate = true;

    /**
     * @var DateTime
     */
    private $initialDate;

    /**
     * @var \isys_component_template_language_manager
     */
    private $lang;

    /**
     * @param string $configJson
     *
     * @return $this
     * @throws GeneralException
     * @throws Exception
     * @var array
     */
    private $repeatDetails;

    /**
     * @var int
     */
    private $repeatEvery = 1;

    /**
     * @var string
     */
    private $repeatEveryUnit = self::REPEAT_UNIT_DAY;

    /**
     * @param string $configJson
     *
     * @return $this
     * @throws GeneralException
     * @throws Exception
     */
    public static function byJSON($configJson)
    {
        if (!JSON::is_json_array($configJson)) {
            throw new GeneralException('The given data needs to be a JSON string!');
        }

        return self::byArray(JSON::decode($configJson));
    }

    /**
     * @param array $config
     *
     * @return $this
     * @throws Exception
     */
    public static function byArray(array $config)
    {
        $initialDate = (isset($config['initialDate']) && strtotime($config['initialDate']) !== false) ? new DateTime($config['initialDate']) : new DateTime();

        return (new self($initialDate))->setRepeatEvery($config['repeatEvery'] ?: 1)
            ->setRepeatEveryUnit($config['repeatEveryUnit'] ?: self::REPEAT_UNIT_DAY)
            ->setRepeatDetails($config['repeatDetails'] ?: null)
            ->setEndAfter($config['endAfter'] ?: self::END_AFTER_NEVER)
            ->setEndDetails($config['endDetails'] ?: null);
    }

    /**
     * @param mixed   $initialDate
     * @param integer $repeatEvery
     * @param string  $repeatEveryUnit
     * @param mixed   $repeatDetails
     * @param string  $endAfter
     * @param mixed   $endDetails
     *
     * @return $this
     * @throws Exception
     */
    public static function byParameters(
        \DateTime $initialDate = null,
        $repeatEvery = 1,
        $repeatEveryUnit = self::REPEAT_UNIT_DAY,
        $repeatDetails = null,
        $endAfter = self::END_AFTER_NEVER,
        $endDetails = null
    ) {
        return (new self($initialDate === null ? new DateTime() : $initialDate))->setRepeatEvery($repeatEvery)
            ->setRepeatEveryUnit($repeatEveryUnit)
            ->setRepeatDetails($repeatDetails)
            ->setEndAfter($endAfter)
            ->setEndDetails($endDetails);
    }

    /**
     * We return a DateTime object.
     *
     * @return DateTime
     */
    public function getInitialDate()
    {
        return $this->initialDate;
    }

    /**
     * @param DateTime $initialDate
     *
     * @return $this
     */
    public function setInitialDate(DateTime $initialDate)
    {
        $this->initialDate = $initialDate;

        return $this;
    }

    /**
     * @return int
     */
    public function getRepeatEvery()
    {
        return $this->repeatEvery;
    }

    /**
     * @param int $repeatEvery
     *
     * @return $this
     */
    public function setRepeatEvery($repeatEvery)
    {
        $this->repeatEvery = (int)$repeatEvery;

        return $this;
    }

    /**
     * @return string
     */
    public function getRepeatEveryUnit()
    {
        return $this->repeatEveryUnit;
    }

    /**
     * @param string $repeatEveryUnit
     *
     * @return $this
     */
    public function setRepeatEveryUnit($repeatEveryUnit)
    {
        $this->repeatEveryUnit = $repeatEveryUnit;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getRepeatDetails()
    {
        return $this->repeatDetails;
    }

    /**
     * Example, when "repeatEvery" is set to "week":
     *   ["monday", "tuesday"] // Every Monday and Tuesday.
     *
     * Or, when "repeatEvery" is set to "month":
     *   "relative"            // Every second monday (check the initialDate).
     *   "absolute"            // 14. of month.
     *
     * @param array|string $repeatDetails
     *
     * @return $this
     */
    public function setRepeatDetails($repeatDetails = null)
    {
        $this->repeatDetails = $repeatDetails;

        return $this;
    }

    /**
     * @return string
     */
    public function getEndAfter()
    {
        return $this->endAfter;
    }

    /**
     * @param string $endAfter
     *
     * @return $this
     */
    public function setEndAfter($endAfter)
    {
        $this->endAfter = $endAfter;

        return $this;
    }

    /**
     * @return null|int|DateTime
     * @throws DateException
     */
    public function getEndDetails()
    {
        if ($this->getEndAfter() === self::END_AFTER_EVENTS) {
            return (int)$this->endDetails;
        }

        if ($this->getEndAfter() === self::END_AFTER_DATE) {
            // Seems to be a timestamp.
            if (is_numeric($this->endDetails)) {
                return new DateTime(date('Y-m-d H:i:s', $this->endDetails));
            }

            // Seems to be a date string.
            if (is_string($this->endDetails) && strtotime($this->endDetails) !== false) {
                return new DateTime($this->endDetails);
            }

            // Simply return the DateTime object.
            if (is_object($this->endDetails) && $this->endDetails instanceof DateTime) {
                return $this->endDetails;
            }

            throw new DateException('The configuration needs a valid end date!');
        }

        return null;
    }

    /**
     * Example, when "endAfter" is set to "events":
     *   10           // After 10 events.
     *
     * Or, when "endAfter" is set to "date":
     *   "31.12.2017" // Must be compatible with "strtotime()".
     *   1514682000   // Timestamp.
     *   DateTime()   // Instance of DateTime.
     *
     * @param mixed $endDetails
     *
     * @return $this
     */
    public function setEndDetails($endDetails = null)
    {
        $this->endDetails = $endDetails;

        return $this;
    }

    /**
     * Get the information if a interval should expire, when the check date lies before the initial date.
     *
     * @return bool
     */
    public function getExpireIfCheckDateIsBeforeInitialDate()
    {
        return $this->expireIfCheckDateIsBeforeInitialDate;
    }

    /**
     * Set if the interval should expire, when the check date lies before the initial date.
     *
     * @param bool $expire
     *
     * @return $this
     */
    public function setExpireIfCheckDateIsBeforeInitialDate($expire = true)
    {
        $this->expireIfCheckDateIsBeforeInitialDate = $expire;

        return $this;
    }

    /**
     * Returns a human readable description of this interval configuration.
     *
     * @return string
     */
    public function __toString()
    {
        try {
            return $this->getHumanReadable();
        } catch (Exception $exception) {
            return $exception->getMessage();
        }
    }

    /**
     * Returns a human readable description of this interval configuration.
     *
     * @param bool $includingEnd
     *
     * @return string
     * @throws DateException
     * @throws GeneralException
     */
    public function getHumanReadable($includingEnd = false)
    {
        $intervalEnd = '';

        if ($includingEnd) {
            $intervalEnd = '. ' . $this->lang->get('LC__INTERVAL__ENDS') . ' ';

            switch ($this->endAfter) {
                case self::END_AFTER_NEVER:
                    $intervalEnd .= $this->lang->get('LC__INTERVAL__ENDS_NEVER');
                    break;

                case self::END_AFTER_DATE:
                    $date = App::instance()->container->get('locales')->fmt_datetime($this->getEndDetails()
                        ->format('Y-m-d H:i:s'));

                    $intervalEnd .= strtolower($this->lang->get('LC__INTERVAL__ENDS_ON')) . ' ' . $date;
                    break;

                case self::END_AFTER_EVENTS:
                    $intervalEnd .= strtolower($this->lang->get('LC__INTERVAL__ENDS_AFTER')) . ' ' . $this->endDetails . ' ' .
                        $this->lang->get('LC__INTERVAL__ENDS_AFTER_DATES');
                    break;

                default:
                    throw new GeneralException('Please provide a valid "endAfter" condition!');
            }
        }

        switch ($this->getRepeatEveryUnit()) {
            case self::REPEAT_UNIT_DAY:
                return ($this->repeatEvery === 1 ?
                        $this->lang->get_in_text('LC__UNIVERSAL__EVERY_M LC__UNIVERSAL__DAY') :
                        $this->lang->get_in_text('LC__UNIVERSAL__EVERY_INTVERAL ' . $this->repeatEvery . ' LC__UNIVERSAL__DAYS')) . $intervalEnd;

            case self::REPEAT_UNIT_WEEK:
                $weekDays = array_map(function ($val) {
                    return $this->lang->get('LC__UNIVERSAL__CALENDAR__DAYS_' . strtoupper($val));
                }, $this->repeatDetails);

                return ($this->repeatEvery === 1 ?
                        $this->lang->get_in_text('LC__UNIVERSAL__EVERY_F LC__UNIVERSAL__WEEK') :
                        $this->lang->get_in_text('LC__UNIVERSAL__EVERY_INTVERAL ' . $this->repeatEvery . ' LC__UNIVERSAL__WEEKS')) .
                        (is_array($weekDays) ? ' ' . \isys_helper_textformat::this_this_and_that($weekDays) : '') . $intervalEnd;

            case self::REPEAT_UNIT_MONTH:
                if ($this->repeatDetails === 'relative') {
                    $detail = strtolower($this->lang->get('LC__INTERVAL__ENDS_ON') . ' ' . Date::getRelativeWeekOfMonthByDay($this->initialDate, true)) . ' ' .
                        $this->lang->get('LC__UNIVERSAL__CALENDAR__DAYS_' . strtoupper($this->initialDate->format('l')));
                } else {
                    $detail = strtolower($this->lang->get('LC__INTERVAL__ENDS_ON')) . $this->initialDate->format(' d.');
                }

                return ($this->repeatEvery === 1 ?
                        $this->lang->get_in_text('LC__UNIVERSAL__EVERY_N LC__UNIVERSAL__MONTH') :
                        $this->lang->get_in_text('LC__UNIVERSAL__EVERY_INTVERAL ' . $this->repeatEvery . ' LC__UNIVERSAL__MONTHS')) . ' ' . $detail . $intervalEnd;

            case self::REPEAT_UNIT_YEAR:
                return ($this->repeatEvery === 1 ?
                        $this->lang->get_in_text('LC__UNIVERSAL__EVERY_N LC__UNIVERSAL__YEAR') :
                        $this->lang->get_in_text('LC__UNIVERSAL__EVERY_INTVERAL ' . $this->repeatEvery . ' LC__UNIVERSAL__YEARS')) . $intervalEnd;

            default:
                throw new GeneralException('Please provide a valid "repeatEvery" unit!');
        }
    }

    /**
     * @return array
     * @throws DateException
     */
    public function toArray()
    {
        $endDetails = $this->getEndDetails();

        return [
            'initialDate'     => $this->getInitialDate()->format('Y-m-d H:i:s'),
            'repeatEvery'     => $this->getRepeatEvery(),
            'repeatEveryUnit' => $this->getRepeatEveryUnit(),
            'repeatDetails'   => $this->getRepeatDetails(),
            'endAfter'        => $this->getEndAfter(),
            'endDetails'      => $endDetails instanceof DateTime ? $endDetails->format('Y-m-d H:i:s') : $endDetails
        ];
    }

    /**
     * Config constructor.
     *
     * @param  DateTime $dateTime
     *
     * @throws Exception
     */
    public function __construct(DateTime $dateTime)
    {
        $this->initialDate = $dateTime;
        $this->lang = App::instance()->container->get('language');
    }
}

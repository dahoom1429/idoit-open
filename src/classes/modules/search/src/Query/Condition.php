<?php

namespace idoit\Module\Search\Query;

/**
 * i-doit
 *
 * Search index condition
 *
 * @package     idoit\Module\Search\Index
 * @author      Dennis Stücken <dstuecken@i-doit.com>
 * @version     1.7
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class Condition
{

    /**
     * Default search mode
     */
    const MODE_DEFAULT = 1;

    /**
     * Deep and slow search mode with partial matching enabled
     */
    const MODE_DEEP = 3;

    /**
     * All modes with string representation
     *
     * @var array
     */
    public static $modes = [
        self::MODE_DEFAULT => 'Normal',
        self::MODE_DEEP    => 'Deep Search'
    ];

    /**
     * @var string
     */
    protected $condition;

    /**
     * Do a fuzzy search on this keyword?
     *
     * @var int
     */
    protected $mode;

    /**
     * Search keyword
     *
     * @var string
     */
    protected $keyword;

    /**
     * Negate this condition?
     *
     * @var bool
     */
    protected $negation;

    /**
     * Search search operation mode
     *
     * @param int $mode
     *
     * @return $this
     */
    public function setMode($mode)
    {
        $mode = (int)$mode;

        if ($mode >= 1 AND $mode <= 3) {
            $this->mode = $mode;
        }

        return $this;
    }

    /**
     * @return int
     */
    public function getMode()
    {
        return $this->mode;
    }

    /**
     * @return string
     */
    public function getKeyword()
    {
        return $this->keyword;
    }

    /**
     * @return string
     */
    public function getCondition()
    {
        return $this->condition;
    }

    /**
     * @return bool
     */
    public function isNegation()
    {
        return $this->negation;
    }

    /**
     * Condition constructor.
     *
     * @param        $keyword
     * @param string $condition
     * @param bool   $negation
     * @param bool   $fuzzySearch
     */
    public function __construct($keyword, $condition = 'AND', $negation = false, $mode = self::MODE_DEFAULT)
    {
        $this->keyword = $keyword;
        $this->condition = $condition;
        $this->negation = $negation;

        $this->setMode($mode);
    }
}

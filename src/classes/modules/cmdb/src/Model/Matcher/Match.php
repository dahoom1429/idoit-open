<?php

namespace idoit\Module\Cmdb\Model\Matcher;

use idoit\Component\Provider\Factory;

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
class Match
{
    use Factory;

    /**
     * The Matched id (e.g. isys_obj__id)
     *
     * @var int
     */
    private $id;

    /**
     * Optional title of the matched item
     *
     * Usually empty.
     *
     * @optional
     *
     * @var string
     */
    private $title = '';

    /**
     * Complete match result
     *
     * @var array
     */
    private $matchResult = [];

    /**
     * @var int
     */
    private $matchCount = 0;

    /**
     * @return int
     */
    public function getMatchCount()
    {
        return $this->matchCount;
    }

    /**
     * @param int $matchCount
     *
     * @return $this
     */
    public function setMatchCount($matchCount)
    {
        $this->matchCount = $matchCount;

        return $this;
    }

    /**
     * @return Match[]
     */
    public function getMatchResult()
    {
        return $this->matchResult;
    }

    /**
     * @param Match[] $matchResult
     *
     * @return $this
     */
    public function setMatchResult(array $matchResult)
    {
        $this->matchResult = $matchResult;

        return $this;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set id
     *
     * @param int $id
     *
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set title
     *
     * @param string $title
     *
     * @return $this
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

}
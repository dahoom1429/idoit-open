<?php

namespace idoit\Module\Report\SqlQuery\Structure;

/**
 * Select condition
 *
 * @package     idoit\Module\Report\SqlQuery\Structure
 * @subpackage  Core
 * @author      Van Quyen Hoang <qhoang@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 * @since       1.8
 */
class SelectCondition
{
    /**
     * @var array
     */
    private $condition = [];

    /**
     * @return array
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function getCondition()
    {
        return $this->condition;
    }

    /**
     * @param array $condition
     *
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function setCondition($condition)
    {
        $this->condition = $condition;

        return $this;
    }

    /**
     * @param string $condition
     *
     * @return $this
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function addCondition($condition)
    {
        $this->condition[] = $condition;

        return $this;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        if (count($this->getCondition())) {
            return ' WHERE ' . ltrim(ltrim(implode(' ', $this->getCondition()), ' AND'), ' OR') . ' ';
        }

        return '';
    }

    /**
     * @param $condition
     *
     * @return SelectCondition
     */
    public static function factory($condition)
    {
        $condition = new SelectCondition($condition);

        return $condition;
    }

    /**
     * JoinQuery constructor.
     *
     * @param $joinQuery
     */
    public function __construct(array $condition = [])
    {
        $this->setCondition($condition);
    }

    /**
     * @return SelectCondition
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function __clone()
    {
        return new SelectCondition($this->getCondition());
    }
}
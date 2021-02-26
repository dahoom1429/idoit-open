<?php

namespace idoit\Module\Report\SqlQuery\Structure;

/**
 * Select Group By
 *
 * @package     idoit\Module\Report\SqlQuery\Structure
 * @subpackage  Core
 * @author      Van Quyen Hoang <qhoang@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 * @since       1.8
 */
class SelectGroupBy
{
    /**
     * @var array
     */
    private $groupBy = [];

    /**
     * @var string
     */
    private $groupConcatSelection = '';

    /**
     * @return array
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function getGroupBy()
    {
        return $this->groupBy;
    }

    /**
     * @param array $groupBy
     *
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function setGroupBy($groupBy)
    {
        $this->groupBy = $groupBy;

        return $this;
    }

    /**
     * @param $groupBy
     *
     * @return $this
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function addGroupBy($groupBy)
    {
        $this->groupBy[] = $groupBy;

        return $this;
    }

    /**
     * @return string
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function getGroupConcatSelection()
    {
        return $this->groupConcatSelection;
    }

    /**
     * @param string $groupConcatSelection
     *
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function setGroupConcatSelection($groupConcatSelection)
    {
        $this->groupConcatSelection = $groupConcatSelection;

        return $this;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        if (count($this->getGroupBy())) {
            return ' GROUP BY ' . implode(', ', $this->getGroupBy()) . ' ';
        }

        return '';
    }

    /**
     * @param $groupBy
     *
     * @return SelectGroupBy
     */
    public static function factory($groupBy, $groupConcatSelection = '')
    {
        $selectGroupBy = new SelectGroupBy($groupBy);

        return $selectGroupBy->setGroupConcatSelection($groupConcatSelection);
    }

    /**
     * JoinQuery constructor.
     *
     * @param $joinQuery
     */
    public function __construct(array $groupBy = [])
    {
        $this->setGroupBy($groupBy);
    }

    /**
     * @return SelectGroupBy
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function __clone()
    {
        $selectGroupBy = new SelectGroupBy($this->getGroupBy());

        return $selectGroupBy->setGroupConcatSelection($this->getGroupConcatSelection());
    }
}
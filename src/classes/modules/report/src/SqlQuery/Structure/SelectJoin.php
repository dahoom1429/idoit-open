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
class SelectJoin
{
    /**
     * @var string
     */
    private $table = '';

    /**
     * @var string
     */
    private $refTable = '';

    /**
     * @var string
     */
    private $tableAlias = '';

    /**
     * @var string
     */
    private $joinType = '';

    /**
     * @var string
     */
    private $onLeft = '';

    /**
     * @var string
     */
    private $onRight = '';

    /**
     * @var string
     */
    private $onLeftAlias = '';

    /**
     * @var string
     */
    private $onRightAlias = '';

    /**
     * @var int
     */
    private $categoryStatus = C__RECORD_STATUS__NORMAL;

    /**
     * @return int
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function getCategoryStatus()
    {
        return $this->categoryStatus;
    }

    /**
     * @param int $categoryStatus
     *
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function setCategoryStatus($categoryStatus)
    {
        $this->categoryStatus = $categoryStatus;

        return $this;
    }

    /**
     * @return string
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     * @param string $table
     *
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function setTable($table)
    {
        $this->table = $table;

        return $this;
    }

    /**
     * @return string
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function getRefTable()
    {
        return $this->refTable;
    }

    /**
     * @param string $refTable
     *
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function setRefTable($refTable)
    {
        $this->refTable = $refTable;

        return $this;
    }

    /**
     * @return string
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function getTableAlias()
    {
        return $this->tableAlias;
    }

    /**
     * @param string $tableAlias
     *
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function setTableAlias($tableAlias)
    {
        $this->tableAlias = $tableAlias;

        return $this;
    }

    /**
     * @return string
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function getJoinType()
    {
        return $this->joinType;
    }

    /**
     * @param string $joinType
     *
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function setJoinType($joinType)
    {
        $this->joinType = $joinType;

        return $this;
    }

    /**
     * @return string
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function getOnLeft()
    {
        return $this->onLeft;
    }

    /**
     * @param string $onLeft
     *
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function setOnLeft($onLeft)
    {
        $this->onLeft = $onLeft;

        return $this;
    }

    /**
     * @return string
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function getOnRight()
    {
        return $this->onRight;
    }

    /**
     * @param string $onRight
     *
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function setOnRight($onRight)
    {
        $this->onRight = $onRight;

        return $this;
    }

    /**
     * @return string
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function getOnLeftAlias()
    {
        return $this->onLeftAlias;
    }

    /**
     * @param string $onLeftAlias
     *
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function setOnLeftAlias($onLeftAlias)
    {
        $this->onLeftAlias = $onLeftAlias;

        return $this;
    }

    /**
     * @return string
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function getOnRightAlias()
    {
        return $this->onRightAlias;
    }

    /**
     * @param string $onRightAlias
     *
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function setOnRightAlias($onRightAlias)
    {
        $this->onRightAlias = $onRightAlias;

        return $this;
    }

    /**
     * Retrieves Status check only if its a Category table with '_list'
     *
     * @return string
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    private function getCategoryStatusCheck()
    {
        if (substr_count($this->getTable(), '_list') === 1 && strpos($this->getTable(), '_2_') === false) {
            return ' AND ' . ($this->getTableAlias() ? $this->getTableAlias() . '.' : '') . $this->getTable() . '__status = ' . $this->getCategoryStatus();
        }
    }

    /**
     * @return string
     */
    public function __toString()
    {
        if ($this->getTable()) {
            return ' ' . $this->getJoinType() . ' JOIN ' . $this->getTable() . ' ' . $this->getTableAlias() . ' ON ' .
                ($this->getOnLeftAlias() ? $this->getOnLeftAlias() . '.' : '') . $this->getOnLeft() . ' = ' .
                ($this->getOnRightAlias() ? $this->getOnRightAlias() . '.' : '') . $this->getOnRight() . $this->getCategoryStatusCheck();
        }

        return '';
    }

    /**
     * @param string $table
     * @param string $joinType
     * @param string $onLeft
     * @param string $onRight
     * @param string $onAliasLeft
     * @param string $onAliasRight
     *
     * @return $this
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public static function factory($table = '', $joinType = '', $onLeft = '', $onRight = '', $onAliasLeft = '', $onAliasRight = '', $tableAlias = '', $refTable = '')
    {
        $selectJoin = new SelectJoin($table);

        return $selectJoin->setJoinType($joinType)
            ->setOnLeft($onLeft)
            ->setOnRight($onRight)
            ->setOnLeftAlias($onAliasLeft)
            ->setOnRightAlias($onAliasRight)
            ->setTableAlias($tableAlias)
            ->setRefTable($refTable);
    }

    /**
     * JoinQuery constructor.
     *
     * @param $joinQuery
     */
    public function __construct($table = '')
    {
        $this->setTable($table);
    }

    /**
     * @return $this
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function __clone()
    {
        $selectJoin = new SelectJoin($this->getTable());

        return $selectJoin->setJoinType($this->getJoinType())
            ->setOnLeft($this->getOnLeft())
            ->setOnRight($this->getOnRight())
            ->setOnLeftAlias($this->getOnLeftAlias())
            ->setOnRightAlias($this->getOnRightAlias())
            ->setTableAlias($this->getTableAlias())
            ->setRefTable($this->getRefTable());
    }
}

<?php

namespace idoit\Module\Report\SqlQuery\Structure;

/**
 * Report Join Query
 *
 * @package     idoit\Module\Report\SqlQuery\Structure
 * @subpackage  Core
 * @author      Dennis Stücken <dstuecken@synetics.de>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 * @since       1.7.1
 */
class JoinSubSelect
{

    /**
     * @var string
     */
    private $joinType = 'INNER';

    /**
     * @var string
     */
    private $selectQuery = '';

    /**
     * @var array
     */
    private $joinedTables = [];

    /**
     * @var string
     */
    private $primaryKey = '';

    /**
     * @var string
     */
    private $objectField = '';

    /**
     * @var string
     */
    private $joinAlias = '';

    /**
     * @return string
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function getJoinAlias()
    {
        return $this->joinAlias;
    }

    /**
     * @param string $joinAlias
     *
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function setJoinAlias($joinAlias)
    {
        $this->joinAlias = $joinAlias;

        return $this;
    }

    /**
     * @return string
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function getPrimaryKey()
    {
        return $this->primaryKey;
    }

    /**
     * @param string $primaryKey
     *
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function setPrimaryKey($primaryKey)
    {
        $this->primaryKey = $primaryKey;

        return $this;
    }

    /**
     * @return string
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function getObjectField()
    {
        return $this->objectField;
    }

    /**
     * @param string $objectField
     *
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function setObjectField($objectField)
    {
        $this->objectField = $objectField;

        return $this;
    }

    /**
     * @return array
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function getJoinedTables()
    {
        return $this->joinedTables;
    }

    /**
     * @param array $joinedTables
     *
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function setJoinedTables($joinedTables)
    {
        $this->joinedTables = $joinedTables;

        return $this;
    }

    /**
     * @return string
     */
    public function getJoinType()
    {
        return $this->joinType;
    }

    /**
     * @param $joinType
     */
    public function setJoinType($joinType)
    {
        $this->joinType = $joinType;

        return $this;
    }

    /**
     * @return string
     */
    public function getSelectQuery()
    {
        return $this->selectQuery;
    }

    /**
     * @param $selectQuery
     */
    public function setSelectQuery($selectQuery)
    {
        $this->selectQuery = $selectQuery;

        return $this;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->joinType . ' JOIN (' . $this->selectQuery . ') ' . ($this->joinAlias ?: '') . ' ';
    }

    /**
     * @param $joinQuery
     *
     * @return JoinSubSelect
     */
    public static function factory($select, $joinType = 'INNER', $joinedTables = [], $primaryKey = '', $objectField = '', $joinAlias = '')
    {
        $joinSubselect = new JoinSubSelect($select);

        return $joinSubselect->setJoinedTables($joinedTables)
            ->setJoinType($joinType)
            ->setPrimaryKey($primaryKey)
            ->setObjectField($objectField)
            ->setJoinAlias($joinAlias);
    }

    /**
     * JoinQuery constructor.
     *
     * @param $joinQuery
     */
    public function __construct($selectQuery, $joinType = 'INNER')
    {
        $this->selectQuery = $selectQuery;
        $this->joinType = $joinType;
    }
}
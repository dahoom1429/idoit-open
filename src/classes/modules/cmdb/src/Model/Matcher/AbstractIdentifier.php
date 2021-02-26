<?php

namespace idoit\Module\Cmdb\Model\Matcher;

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
abstract class AbstractIdentifier
{
    /**
     * Key for this identifier, has to be unique
     */
    const KEY = '';

    /**
     * Title, used for ui representation of this identifier
     *
     * @var string
     */
    protected $title;

    /**
     * Bitwise Flag
     *
     * @var int
     */
    protected static $bit;

    /**
     * Corresponding Sql statement to retrieve this identifier
     *
     * @var string
     */
    protected $sqlSelect;

    /**
     * Sql statement to retrieve the value of the identifier
     *
     * @var string
     */
    protected $dataSqlSelect;

    /**
     * Usage options for Match Identifier
     *
     * @var array
     */
    protected $usableIn = [];

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param mixed $title
     *
     * @return $this
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return string
     */
    public function getSqlSelect($value, $condition = '')
    {
        return str_replace([':value:', ':status:', ':condition:'],
            ['\'' . \isys_application::instance()->database->escape_string($value) . '\'', C__RECORD_STATUS__NORMAL, $condition], $this->sqlSelect);
    }

    /**
     * @param string $sqlSelect
     *
     * @return $this
     */
    public function setSqlSelect($sqlSelect)
    {
        $this->sqlSelect = $sqlSelect;

        return $this;
    }

    /**
     * @param $objID
     *
     * @return string
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function getDataSqlSelect($objID)
    {
        return str_replace([':objID:', ':status:'], ['\'' . \isys_application::instance()->database->escape_string($objID) . '\'', C__RECORD_STATUS__NORMAL],
            $this->dataSqlSelect);
    }

    /**
     * @param $dataSqlSelect
     *
     * @return $this
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function setDataSqlSelect($dataSqlSelect)
    {
        $this->dataSqlSelect = $dataSqlSelect;

        return $this;
    }

    /**
     * @return int
     */
    public static function getBit()
    {
        return static::$bit;
    }

    /**
     * @param int $bit
     *
     * @return $this
     */
    public function setBit($bit)
    {
        static::$bit = $bit;

        return $this;
    }

    /**
     * @return array
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function getUsableIn()
    {
        return $this->usableIn;
    }
}
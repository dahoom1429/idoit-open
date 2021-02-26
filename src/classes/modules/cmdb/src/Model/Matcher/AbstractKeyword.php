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
class AbstractKeyword
{
    /**
     * Key of this keyword. E.g. idoit\Module\Cmdb\Model\Matcher\Identifier\Hostname::KEY
     *
     * @var string
     */
    protected $key = '';

    /**
     * Value of this keyword
     *
     * @var string
     */
    protected $value = '';

    /**
     * sql condition addition
     *
     * @var string
     */
    protected $condition = '';

    /**
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @param string $key
     *
     * @return $this
     */
    public function setKey($key)
    {
        $this->key = $key;

        return $this;
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * @return string
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function getCondition()
    {
        return $this->condition;
    }

    /**
     * @param string $condition
     *
     * @author   Van Quyen Hoang <qhoang@i-doit.com>
     */
    public function setCondition($condition)
    {
        $this->condition = $condition;
    }

    /**
     * AbstractKeyword constructor.
     *
     * @param string $key
     * @param string $value
     */
    public function __construct($key, $value, $condition = '')
    {
        $this->key = $key;
        $this->value = $value;
        $this->condition = $condition;
    }

}
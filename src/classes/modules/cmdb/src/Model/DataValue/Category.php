<?php

namespace idoit\Module\Cmdb\Model\DataValue;

use idoit\Module\Cmdb\Model\Ci\Category as ReferencedCategory;

/**
 * i-doit
 *
 * Ci Models
 *
 * @package     i-doit
 * @subpackage  Cmdb
 * @author      Dennis Stücken <dstuecken@i-doit.com>
 * @version     1.7
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class Category extends BaseValue implements DataValueInterface
{
    /**
     * @var ReferencedCategory
     */
    protected $value = '';

    /**
     * @return ReferencedCategory
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param ReferencedCategory $value
     *
     * @return $this
     */
    public function setValue($value)
    {
        if (!is_a($value, 'idoit\Module\Cmdb\Model\Ci\Category')) {
            throw new \InvalidArgumentException('Value has to be of type "idoit\Module\Cmdb\Model\Ci\Category".');
        }

        $this->value = $value;

        return $this;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->value . '';
    }

    /**
     * Category constructor.
     *
     * @param ReferencedCategory $value
     */
    public function __construct(ReferencedCategory $value)
    {
        $this->setValue($value);
    }
}
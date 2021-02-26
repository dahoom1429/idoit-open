<?php
/**
 *
 *
 * @package     i-doit
 * @subpackage
 * @author      Pavel Abduramanov <pabduramanov@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */

namespace idoit\Component\Table\Filter\Formatter;

/**
 * Always returns the predefined value
 *
 * @package idoit\Component\Table\Filter\Formatter
 */
class ConstantFormatter implements FormatterInterface
{
    /**
     * @var string
     */
    private $value;

    public function __construct($value)
    {
        $this->value = $value;
    }

    public static function create($value)
    {
        return new static($value);
    }

    public function format($value)
    {
        return $this->value;
    }
}

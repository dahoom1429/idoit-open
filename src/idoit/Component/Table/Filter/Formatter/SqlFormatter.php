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
 * Format as sql string
 *
 * @package idoit\Component\Table\Filter\Formatter
 */
class SqlFormatter implements FormatterInterface
{
    public function format($value)
    {
        return "'" . addslashes(strval($value)) . "'";
    }
}

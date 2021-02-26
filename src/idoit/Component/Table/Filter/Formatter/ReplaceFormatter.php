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
 * Proceeds the str_replace for the value
 *
 * @package idoit\Component\Table\Filter\Formatter
 */
class ReplaceFormatter implements FormatterInterface
{
    /**
     * @var array
     */
    private $subject;

    /**
     * @var array
     */
    private $replacement;

    public function __construct(array $subject, array $replacement)
    {
        $this->subject = $subject;
        $this->replacement = $replacement;
    }

    public function format($value)
    {
        return str_replace($this->subject, $this->replacement, $value);
    }
}

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

namespace idoit\Module\Console\Steps\Message;

class Message
{
    /**
     * @var int ErrorLevel
     */
    protected $level = ErrorLevel::INFO;

    /**
     * @var string
     */
    protected $message;

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @return int
     */
    public function getLevel()
    {
        return $this->level;
    }

    public function __construct($message, $level)
    {
        $this->message = $message;
        $this->level = $level;
    }
}

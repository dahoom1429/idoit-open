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

class Messages
{
    /**
     * @var array
     */
    private $messages = [];

    private $handlers = [];

    public function addHandler(callable $handler)
    {
        $this->handlers[] = $handler;
    }

    /**
     * Get messages
     *
     * @return array
     */
    public function getMessages()
    {
        return $this->messages;
    }

    public function getMessagesOfLevel($level)
    {
        return array_filter($this->messages, function ($message) use ($level) {
            return $message->getLevel() & $level;
        });
    }

    /**
     * @param Message $message
     *
     * @return $this
     */
    public function addMessage(Message $message)
    {
        $this->messages[] = $message;
        foreach ($this->handlers as $handler) {
            $handler($message);
        }

        return $this;
    }
}

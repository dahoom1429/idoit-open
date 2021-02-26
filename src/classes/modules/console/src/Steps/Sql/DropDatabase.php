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

namespace idoit\Module\Console\Steps\Sql;

use idoit\Module\Console\Steps\Message\ErrorLevel;
use idoit\Module\Console\Steps\Message\Messages;
use idoit\Module\Console\Steps\Message\StepMessage;

class DropDatabase extends SqlStep
{
    private $done;

    private $name;

    public function __construct($host, $username, $password, $name, $port)
    {
        parent::__construct($host, $username, $password, '', $port);
        $this->name = $name;
    }

    /**
     * Get name of the step
     *
     * @return string
     */
    public function getName()
    {
        return 'Drop DB: ' . $this->name;
    }

    /**
     * Process the work
     *
     * @param Messages $messages
     *
     * @return mixed
     */
    public function process(Messages $messages)
    {
        $connection = $this->createConnection();
        if ($connection->connect_error || $connection->error) {
            $messages->addMessage(new StepMessage($this, 'Cannot connect to Sql', ErrorLevel::FATAL));
            return false;
        }
        $this->done = false;
        if ($connection->query("SHOW DATABASES LIKE '" . $this->name . "'")->num_rows === 0) {
            $messages->addMessage(new StepMessage($this, 'Database ' . $this->name . ' is already removed', ErrorLevel::NOTIFICATION));

            return true;
        }
        $query = "DROP DATABASE IF EXISTS {$this->name}";
        $messages->addMessage(new StepMessage($this, $query, ErrorLevel::DEBUG));
        if ($connection->query($query) === false) {
            $messages->addMessage(new StepMessage($this, "Cannot drop database {$this->name}", ErrorLevel::ERROR));
            return false;
        }
        if ($connection->query("SHOW DATABASES LIKE '" . $this->name . "'")->num_rows > 0) {
            $messages->addMessage(new StepMessage($this, 'Database ' . $this->name . ' is not removed', ErrorLevel::NOTIFICATION));

            return false;
        }
        $this->done = true;

        return true;
    }

    /**
     * Undo the work
     *
     * @param Messages $messages
     *
     * @return mixed
     */
    public function undo(Messages $messages)
    {
        return true;
    }
}

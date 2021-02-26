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

class CreateDatabase extends SqlStep
{
    private $created;

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
        return 'Create DB: ' . $this->name;
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
        $messages->addMessage(new StepMessage($this, "SHOW DATABASES LIKE '" . $this->name . "'", ErrorLevel::DEBUG));
        if ($connection->query("SHOW DATABASES LIKE '" . $this->name . "'")->num_rows) {
            $messages->addMessage(new StepMessage($this, 'Database ' . $this->name . ' is already exist', ErrorLevel::ERROR));

            return false;
        }
        $this->created = false;
        $messages->addMessage(new StepMessage($this, "CREATE DATABASE IF NOT EXISTS {$this->name} DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci", ErrorLevel::DEBUG));
        $result = $connection->query("CREATE DATABASE IF NOT EXISTS {$this->name} DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci");
        if ($result === false) {
            $messages->addMessage(new StepMessage($this, "Cannot create database {$this->name}", ErrorLevel::ERROR));
            return false;
        }
        $this->created = true;

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
        if (!$this->created) {
            return true;
        }
        $connection = $this->createConnection();
        if ($connection->error) {
            $messages->addMessage(new StepMessage($this, 'Cannot connect to Sql', ErrorLevel::FATAL));
            return false;
        }
        $messages->addMessage(new StepMessage($this, "DROP DATABASE IF EXISTS `{$this->name}`", ErrorLevel::DEBUG));

        if ($connection->query("DROP DATABASE IF EXISTS `{$this->name}`") === false) {
            $messages->addMessage("Cannot drop database {$this->name}");
            return false;
        }
        return true;
    }
}

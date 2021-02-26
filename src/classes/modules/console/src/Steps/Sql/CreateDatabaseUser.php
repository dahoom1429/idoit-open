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

class CreateDatabaseUser extends SqlStep
{
    private $created = false;

    private $password;

    private $username;

    public function __construct($username, $password, $host, $rootName, $rootPassword, $port)
    {
        parent::__construct($host, $rootName, $rootPassword, '', $port);
        $this->username = $username;
        $this->password = $password;
    }

    /**
     * Get name of the step
     *
     * @return string
     */
    public function getName()
    {
        return 'Create DB User: ' . $this->username;
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
            $messages->addMessage(new StepMessage($this, 'Cannot connect to DB', ErrorLevel::FATAL));

            return false;
        }
        $this->created = false;
        $password = addslashes($connection->escape_string($this->password));
        $exist = "SELECT * FROM mysql.user WHERE user = '{$this->username}'";
        if ($connection->query($exist)->num_rows === 0) {
            $createSql = "CREATE USER IF NOT EXISTS '{$this->username}'@'{$this->getHostForUser()}' IDENTIFIED BY '{$password}';";
            $messages->addMessage(new StepMessage($this, 'Create User: ' . $this->username, ErrorLevel::INFO));

            if ($connection->query($createSql) === false) {
                $messages->addMessage(new StepMessage($this, 'Cannot create user ' . $this->username, ErrorLevel::ERROR));

                return false;
            }
            $this->created = true;
        } else {
            $messages->addMessage(new StepMessage($this, 'User ' . $this->username . ' is already exist', ErrorLevel::NOTIFICATION));
            return true;
        }

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
        $messages->addMessage(new StepMessage($this, 'Removing User: ' . $this->username, ErrorLevel::INFO));
        $connection = $this->createConnection();
        if ($connection->error) {
            $messages->addMessage(new StepMessage($this, 'Cannot connect to DB', ErrorLevel::FATAL));

            return false;
        }

        if ($connection->query("DROP USER IF EXISTS `{$this->username}`@'localhost'") === false) {
            $messages->addMessage("Cannot drop user {$this->username}");

            return false;
        }

        return true;
    }
}

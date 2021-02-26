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

class GrantUserOnDatabase extends SqlStep
{
    private $database;

    private $username;

    private $userPassword;

    public function __construct($username, $userPassword, $database, $host, $rootName, $rootPassword, $port)
    {
        parent::__construct($host, $rootName, $rootPassword, '', $port);
        $this->username = $username;
        $this->database = $database;
        $this->userPassword = $userPassword;
    }

    /**
     * Get name of the step
     *
     * @return string
     */
    public function getName()
    {
        return 'Grant User privileges on ' . $this->database . ': ' . $this->username;
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

        $identified = '';
        if (!empty($this->userPassword)) {
            $password = addslashes($connection->escape_string($this->userPassword));
            $identifiedBy = " IDENTIFIED BY '{$password}' ";
        }

        $grant = "GRANT ALL ON `{$this->database}`.* TO '{$this->username}'@'{$this->getHostForUser()}' {$identifiedBy};";
        $messages->addMessage(new StepMessage($this, $grant, ErrorLevel::DEBUG));
        if ($connection->query($grant) === false) {
            $messages->addMessage(new StepMessage($this, "Cannot grant privileges on {$this->database} for user {$this->username}@%", ErrorLevel::ERROR));

            return false;
        }

        if ($connection->query('FLUSH PRIVILEGES;') === false) {
            $messages->addMessage(new StepMessage($this, 'Cannot flush privileges', ErrorLevel::ERROR));

            return false;
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
        $connection = $this->createConnection();
        if ($connection->error) {
            $messages->addMessage(new StepMessage($this, 'Cannot connect to DB', ErrorLevel::FATAL));

            return false;
        }
        $revoke = "REVOKE ALL ON `{$this->database}`.* FROM '{$this->username}'@'{$this->getHostForUser()}';";
        $connection->query($revoke);

        return true;
    }
}

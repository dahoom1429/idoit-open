<?php

namespace idoit\Module\Console\Steps\Sql;

use idoit\Module\Console\Steps\Message\ErrorLevel;
use idoit\Module\Console\Steps\Message\Messages;
use idoit\Module\Console\Steps\Message\StepMessage;

class ImportDatabaseFromDump extends SqlStep
{
    /**
     * @var string step's name
     */
    private $name;

    /**
     * @var string Path to the script
     */
    private $path;

    /**
     * ImportDatabaseFromDump constructor.
     *
     * @param string $path
     * @param string $host
     * @param string $username
     * @param string $password
     * @param string $name
     * @param string $port
     */
    public function __construct($path, $host, $username, $password, $name, $port)
    {
        parent::__construct($host, $username, $password, '', $port);

        $this->name = $name;
        $this->path = $path;
    }

    /**
     * Get name of the step
     *
     * @return string
     */
    public function getName()
    {
        return 'Import DB: ' . $this->name;
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

        if ($connection->select_db($this->name)) {
            $connection->query('SET names utf8;');

            $dump = file_get_contents($this->path);
            $queries = explode(";\r\n", $dump);

            if (count($queries) <= 1) {
                $queries = explode(";\n", $dump);
            }

            if (is_array($queries) && count($queries) > 1) {
                foreach ($queries as $line) {
                    $query = explode("\n", $line);
                    $sql = '';

                    foreach ($query as $l_value) {
                        if (!preg_match('/[\-]{2}(.*?)/', $l_value)) {
                            $sql .= $l_value;
                        }
                    }

                    if (!empty($sql)) {
                        $sql = trim($sql) . ';';

                        if ($sql !== ';' && strlen($sql) > 1 && !@$connection->query($sql)) {
                            $messages->addMessage(new StepMessage(
                                $this,
                                'Error during importing the data: #' . $connection->errno . ': ' . $connection->error,
                                ErrorLevel::FATAL
                            ));

                            return false;
                        }
                    }
                }
            } else {
                $messages->addMessage(new StepMessage(
                    $this,
                    "SQL-Dump ($this->path) does not exist or it is not well-formatted. \nMaybe an encoding or line feed problem - Try converting it to CRLF.",
                    ErrorLevel::FATAL
                ));

                return false;
            }
        } else {
            $messages->addMessage(new StepMessage($this, "Cannot connect to {$this->name} database", ErrorLevel::FATAL));

            return false;
        }

        $messages->addMessage(new StepMessage($this, '', ErrorLevel::INFO));

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

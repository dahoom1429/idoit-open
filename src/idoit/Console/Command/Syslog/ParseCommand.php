<?php

namespace idoit\Console\Command\Syslog;

use idoit\Component\Helper\Ip;
use idoit\Console\Command\AbstractCommand;
use isys_cmdb_dao_category_g_ip;
use isys_event_manager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ParseCommand extends AbstractCommand
{
    const NAME = 'import-syslog';

    /**
     * Get name for command
     *
     * @return string
     */
    public function getCommandName()
    {
        return self::NAME;
    }

    /**
     * Get description for command
     *
     * @return string
     */
    public function getCommandDescription()
    {
        return 'Imports data from a Syslog server textfile to the i-doit Logbook';
    }

    /**
     * Retrieve Command InputDefinition
     *
     * @return InputDefinition
     */
    public function getCommandDefinition()
    {
        return new InputDefinition();
    }

    /**
     * Checks if a command can have a config file via --config
     *
     * @return bool
     */
    public function isConfigurable()
    {
        return true;
    }

    /**
     * Returns an array of command usages
     *
     * @return string[]
     */
    public function getCommandUsages()
    {
        return [];
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln("Syslog-Handler initialized (" . date("Y-m-d H:i:s") . ")");

        if (empty($this->config)) {
            $output->writeln('<error>Please provide a configuration ini via --config!</error>');

            return Command::FAILURE;
        }

        // Check log and add to logbook.
        try {
            $this->parseLog($output);
        } catch (\Exception $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
            return Command::FAILURE;
        }
        return Command::SUCCESS;
    }

    private function parseLog(OutputInterface $output)
    {
        // Get daos, because now we are logged in.
        $m_eventManager = isys_event_manager::getInstance();
        $m_dao_ip = new isys_cmdb_dao_category_g_ip($this->container->database);
        $logsCount = is_countable($this->config["logfiles"]) ? count($this->config["logfiles"]) : 0;

        $output->writeln($logsCount . " Logfiles found.");

        for ($i = 0;$i < $logsCount;$i++) {
            if (!file_exists($this->config["logfiles"][$i])) {
                $output->writeln('Skipping non existing file: ' . $this->config["logfiles"][$i]);
                continue;
            }

            if (!is_readable($this->config["logfiles"][$i])) {
                throw new \Exception("CRON: syslog connector - cannot read syslog from file: \"" . $this->config["logfiles"][$i] . "\"");
            }

            $l_ambigiousIPs = [];
            $l_noHost = [];
            $l_ambigiousHosts = [];
            $l_noIPs = [];

            $l_syslog = fopen($this->config["logfiles"][$i], "r");

            $output->writeln("Processing " . $this->config["logfiles"][$i]);

            while (!feof($l_syslog)) {
                $l_line = fgets($l_syslog, 4096);

                $l_parts = [];

                if (preg_match($this->config['regexSplitSyslogLine'], $l_line, $l_parts) == 0) {
                    $output->writeln('No matches for regex, skipping file', OutputInterface::VERBOSITY_VERY_VERBOSE | OutputInterface::VERBOSITY_DEBUG);
                    continue;
                }

                $l_ip = trim($l_parts[4]);
                $l_host = trim($l_parts[3]);

                if (Ip::validate_ip($l_host)) {
                    $l_ip = $l_host;
                    unset($l_host);
                }

                if (!empty($l_host)) {
                    if (array_search($l_host, $l_ambigiousHosts) !== false || array_search($l_host, $l_noHost) !== false) {
                        continue;
                    }
                } elseif (!empty($l_ip)) {
                    if (array_search($l_ip, $l_ambigiousIPs) !== false || array_search($l_ip, $l_noIPs) !== false) {
                        continue;
                    }
                } else {
                    continue;
                }

                if (preg_match("/[\d{1,3}\.]{3}\d{1,3}/", $l_ip) == 0) {
                    $l_objIDs = $m_dao_ip->getObjIDsByHostName($l_host);
                    $l_key = 'id';
                } else {
                    $l_objIDs = $m_dao_ip->getObjIDsByIP($l_ip);
                    $l_key = 'isys_obj__id';
                }

                try {
                    $count = is_countable($l_objIDs) ? count($l_objIDs) : (empty($l_objIDs) ? 0 : 1);
                    if ($l_key == 'id') {
                        if ($count > 1) {
                            $l_ambigiousHosts[] = $l_host;
                            throw new \Exception($l_host . " is ambigious");
                        }

                        if ($count < 1) {
                            $l_noHost[] = $l_host;
                            throw new \Exception($l_host . " has no object");
                        }
                    } else {
                        if ($count > 1) {
                            $l_ambigiousIPs[] = $l_ip;
                            throw new \Exception($l_ip . " is ambigious");
                        }

                        if ($count < 1) {
                            $l_noIPs[] = $l_ip;
                            throw new \Exception($l_ip . " has no object");
                        }
                    }

                    preg_match("/([a-zA-Z]{3})[ ]+([\d]+)[ ]+([\d]+:[\d]+:[\d]+)/", $l_parts[1], $l_dateParts);

                    $dateTime = strtotime($l_dateParts[1]);
                    $l_month = $dateTime === false ? '' : date('m', $dateTime);

                    $l_date = date("Y") . "-" . $l_month . "-" . $l_dateParts[2] . " " . $l_dateParts[3];

                    $m_eventManager->triggerEvent(
                        "Syslog: " . $l_parts[5] . " " . $this->config["priorities"][$i],
                        $l_parts[6],
                        $l_date,
                        $this->config["alertlevels"][$i],
                        defined_or_default('C__LOGBOOK_SOURCE__EXTERNAL', 2),
                        $l_objIDs[0][$l_key]
                    );
                } catch (\Exception $e) {
                    $output->writeln($e->getMessage());

                    $m_eventManager->triggerEvent(
                        "Syslog: " . $e->getMessage(),
                        "Error processing " . $this->config["logfiles"][$i],
                        null,
                        defined_or_default('C__LOGBOOK__ALERT_LEVEL__2', 3),
                        defined_or_default('C__LOGBOOK_SOURCE__EXTERNAL', 2),
                        null
                    );
                }
            }

            fclose($l_syslog);
            unlink($this->config["logfiles"][$i]);
        }
    }
}

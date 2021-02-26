<?php

namespace idoit\Module\JDisc\Console\Command;

use Exception;
use idoit\Console\Command\AbstractCommand;
use idoit\Console\Command\IsysLogWrapper;
use isys_helper_crypt;
use isys_jdisc_dao_discovery;
use isys_log;
use isys_module_jdisc;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class JDiscDiscoveryCommand extends AbstractCommand
{
    const NAME = 'import-jdiscdiscovery';

    /**
     * Log instance for this handler.
     *
     * @var  isys_log
     */
    protected $m_log = null;

    /**
     * @var bool
     */
    private $m_show_log = false;

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $exitCode = Command::SUCCESS;
        /**
         * @param isys_jdisc_dao_discovery $p_discovery_obj
         */
        $handleOutput = function ($p_discovery_obj) use ($output) {
            if (defined('ISYS_VERBOSE') && $this->m_show_log === true) {
                // Get status logs only if verbose mode is set
                if (constant('ISYS_VERBOSE') === true) {
                    $l_running_data = $p_discovery_obj->get_running_discover_status();
                    $l_status = $l_running_data['status'];
                    $l_last_log = $l_running_data['log'];
                    $output->writeln($l_last_log, true);

                    while ($l_status === 'Running') {
                        $l_running_data = $p_discovery_obj->get_running_discover_status();
                        $l_status = $l_running_data['status'];
                        if ($l_last_log !== $l_running_data['log'] && $l_running_data['log'] !== '') {
                            $l_last_log = $l_running_data['log'];
                            $output->writeln($l_last_log, true);
                        }
                    }
                    $output->writeln('Finished scanning device.', true);
                }
            }
        };

        // Start logging.
        $this->m_log = IsysLogWrapper::instance();
        $this->m_log->setOutput($output);

        // Retrieving the jdisc server.
        $l_jdisc_server = $input->getOption('server');

        // Retrieving the discovery job.
        $l_jdisc_discovery_job = $input->getOption('discoveryJob');

        // Retrieving the device hostname.
        $l_device_hostname = $input->getOption('deviceHostAddress');

        // Retrieving the device hostname.
        $l_device_hostaddress = $input->getOption('deviceHostname');

        $this->m_show_log = (bool)$input->getOption('showLog');

        // JDisc module
        $l_module = isys_module_jdisc::factory();

        $l_jdisc_server = $l_module->get_jdisc_discovery_data($l_jdisc_server, true)
            ->get_row();

        $l_host = $l_jdisc_server['isys_jdisc_db__host'];
        $l_username = $l_jdisc_server['isys_jdisc_db__discovery_username'];
        $l_password = isys_helper_crypt::decrypt($l_jdisc_server['isys_jdisc_db__discovery_password']);
        $l_port = $l_jdisc_server['isys_jdisc_db__discovery_port'];
        $l_protocol = $l_jdisc_server['isys_jdisc_db__discovery_protocol'];

        try {
            // JDisc Discovery object
            $l_discovery_obj = isys_jdisc_dao_discovery::get_instance();

            $l_discovery_obj->connect($l_host, $l_username, $l_password, $l_port, $l_protocol);

            if ($l_device_hostname === null && $l_device_hostaddress === null) {
                $l_discovery_jobs = $l_discovery_obj->get_discovery_jobs();
                foreach ($l_discovery_jobs as $l_job) {
                    if (strtolower($l_job['name']) == strtolower($l_jdisc_discovery_job)) {
                        $l_discovery_obj->set_discovery_job($l_job);
                        break;
                    }
                }
                if ($l_discovery_obj->get_discovery_job() === null) {
                    $output->writeln('Discovery Job "' . $l_jdisc_discovery_job . '" not found.');

                    return;
                }
                if ($l_discovery_obj->start_discovery_job()) {
                    $output->writeln('Discovery Job "' . $l_jdisc_discovery_job . '" has been triggered.');
                    $handleOutput($l_discovery_obj);
                } else {
                    $output->writeln('Failed to trigger the Discovery Job "' . $l_jdisc_discovery_job . '".');
                }
            } else {
                if ($l_device_hostaddress) {
                    $l_discovery_obj->set_target($l_device_hostaddress);
                } elseif ($l_device_hostname) {
                    $l_discovery_obj->set_target($l_device_hostname);
                }
                if ($l_discovery_obj->discover_device()) {
                    $output->writeln('Discovery of device "' . $l_discovery_obj->get_target() . '" started.', true);
                    $handleOutput($l_discovery_obj);
                }
            }
        } catch (Exception $e) {
            $output->writeln('<error>Something went wrong with message: ' . $e->getMessage() . '</error>');
            $exitCode = Command::FAILURE;
        }

        return $exitCode;
    }

    /**
     * Retrieve Command InputDefinition
     *
     * @return InputDefinition
     */
    public function getCommandDefinition()
    {
        $definition = new InputDefinition();

        $definition->addOption(new InputOption('server', 's', InputOption::VALUE_REQUIRED, 'Selected "JDisc Server"'));

        $definition->addOption(new InputOption('discoveryJob', 'j', InputOption::VALUE_REQUIRED, 'Selected "Discovery Job"', 'Discover all'));

        $definition->addOption(new InputOption('deviceHostname', 'd', InputOption::VALUE_REQUIRED, 'Selected device by "hostname"'));

        $definition->addOption(new InputOption('deviceHostAddress', 'a', InputOption::VALUE_REQUIRED, 'Selected device by "hostaddress"'));

        $definition->addOption(new InputOption('showLog', 'l', InputOption::VALUE_NONE, 'Show log while discovery'));

        return $definition;
    }

    /**
     * Get description for command
     *
     * @return string
     */
    public function getCommandDescription()
    {
        return 'Triggers a JDisc discovery (API Access to the JDisc server is defined in the GUI)';
    }

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
     * Returns an array of command usages
     *
     * @return string[]
     */
    public function getCommandUsages()
    {
        return [];
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
}

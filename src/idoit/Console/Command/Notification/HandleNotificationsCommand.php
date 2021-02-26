<?php

namespace idoit\Console\Command\Notification;

use idoit\Console\Command\AbstractCommand;
use idoit\Console\Command\IsysLogWrapper;
use isys_application;
use isys_factory_log;
use isys_log;
use isys_notification;
use isys_notifications_dao;
use PHPMailer\PHPMailer\Exception as PHPMailerException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\StreamOutput;

class HandleNotificationsCommand extends AbstractCommand
{
    const NAME = 'notifications-send';

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
        return 'Sends out e-mails for notifications defined in the notification add-on';
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
        // Start logging:
        $logger = IsysLogWrapper::instance();
        $logger->setOutput(new StreamOutput(fopen('log/notifications_' . date('Y-m-d_H_i_s') . '.log', 'a')));
        $logger->set_log_level(isys_log::C__ALL & ~isys_log::C__DEBUG)
            ->set_verbose_level(isys_log::C__FATAL | isys_log::C__ERROR | isys_log::C__WARNING | isys_log::C__NOTICE);
        $output->writeln('Begin to notify...');

        $idoitUri = \isys_tenantsettings::get('system.base.uri');
        if (!$idoitUri) {
            $output->writeln('<error>Tenant Setting "i-doit address (URL)" not set!</error>');
        }

        // Get database component:
        $database = $this->container->database;

        try {
            $output->writeln('Iterating through each notification type...');

            $l_dao = new isys_notifications_dao($database, $logger);

            // Fetch all notification types:
            $l_types = $l_dao->get_type();

            // Iterate through each notification type:
            foreach ($l_types as $l_type) {
                $outputText = sprintf('Handling notification type "%s" [%s].', isys_application::instance()->container->get('language')
                    ->get($l_type['title']), $l_type['id']);

                $output->writeln($outputText);

                // Add info to the logger which type is being logged
                $logger->info($outputText);

                /**
                 * Use callback to notify:
                 *
                 * @var $l_callback isys_notification
                 */
                $l_callback = new $l_type['callback']($l_dao, $database, $logger);

                $l_callback->set_channels(isys_notification::C__CHANNEL__EMAIL);
                $l_callback->init($l_type);
                $l_callback->notify();

                unset($l_callback);
            }

            $l_dao->apply_update();

            $output->writeln('Everything done.');
            return Command::SUCCESS;
        } catch (PHPMailerException $e) {
            $output->writeln('<error>There was a problem while sending notification emails!</error>');
            return Command::FAILURE;
        } catch (\Exception $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
            return Command::FAILURE;
        } //try/catch
    }
}

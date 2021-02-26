<?php

namespace idoit\Console\Command\Idoit;

use idoit\Console\Command\AbstractCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use idoit\Component\Security\Hash\Password;
use Symfony\Component\Console\Question\Question;
use isys_update_config;

class AdminCenterResetPasswordCommand extends AbstractCommand
{
    const NAME = 'admin-center-password-reset';

    /**
     * @var OutputInterface
     */
    private $output;

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
        return 'With this command you can reset the Admin-Center password';
    }

    /**
     * Retrieve Command InputDefinition
     *
     * @return InputDefinition
     */
    public function getCommandDefinition()
    {
        $definition = new InputDefinition();

        return $definition;
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
        $this->output = $output;

        $helper = $this->getHelper('question');

        $question = new Question("<question>Type new password please</question>\n>>>");
        $question->setHidden(true);
        $question->setHiddenFallback(false);
        $newPlainPassword = $helper->ask($input, $output, $question);

        $question = new Question("<question>Retype the password please</question>\n>>>");
        $question->setHidden(true);
        $question->setHiddenFallback(false);
        $retypedPlainPassword = $helper->ask($input, $output, $question);

        if ($newPlainPassword !== $retypedPlainPassword) {
            $this->output->writeln('<error>Error: provided passwords do not match</error>');
            return Command::SUCCESS;
        }

        $newHashedPassword = Password::instance()
            ->setPassword($newPlainPassword)
            ->hash();

        $this->newPasswordSave($newHashedPassword);

        $this->output->writeln('New password hash has been successfully updated');
        return Command::SUCCESS;
    }

    /**
     * Method for updating password hash in the config file
     * @param string $newHashedPassword
     */
    private function newPasswordSave(string $newHashedPassword) : void
    {
        global $g_absdir;

        $configDir = $g_absdir . '/src';
        $updater = new isys_update_config();
        $updater->backup($configDir);

        $templateDir = $g_absdir . '/setup';
        $config = $updater->parseAndUpdateConfig(
            $templateDir,
            [
                'config.adminauth.password' => $newHashedPassword,
            ]
        );
        $updater->write('<' . substr($config, 1), $configDir);
    }
}

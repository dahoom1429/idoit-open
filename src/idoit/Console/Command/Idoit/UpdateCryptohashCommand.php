<?php

namespace idoit\Console\Command\Idoit;

use idoit\Console\Command\AbstractCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use isys_update_config;
use isys_helper_crypt;

class UpdateCryptohashCommand extends AbstractCommand
{
    const NAME = 'admin-center-cryptohash-reset';

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
        return 'With this command you can update the Crypto-hash and update encrypted passwords';
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
        global $g_absdir;
        $configDir = $g_absdir . '/src';
        $configPath = $configDir . '/config.inc.php';
        if (!is_writable($configPath)) {
            $output->writeln('<error>File ' . $configPath . ' is not writable. set write permissions!</error>');
            return;
        }

        global $g_crypto_hash;
        $cryptoHashOld = $g_crypto_hash ? $g_crypto_hash : '';
        $cryptoHashNew = sha1(uniqid('', true));

        $database = \isys_application::instance()->container->get('database');

        $rows = $database->query('select isys_ocs_db__id, isys_ocs_db__pass from isys_ocs_db');
        while ($row = $rows->fetch_assoc()) {
            $srcEncryptedPass = $row['isys_ocs_db__pass'];
            $newEncryptedPass = $this->reencryptPass($cryptoHashOld, $cryptoHashNew, $srcEncryptedPass);
            $database->query("update isys_ocs_db set isys_ocs_db__pass = '" . $newEncryptedPass . "' where isys_ocs_db__id = " . $row['isys_ocs_db__id']);
        }
        $output->writeln('Passwords in table isys_ocs_db have been successfully updated with new crypto hash');

        $rows = $database->query('select isys_ldap__id, isys_ldap__password from isys_ldap');
        while ($row = $rows->fetch_assoc()) {
            $srcEncryptedPass = $row['isys_ldap__password'];
            $newEncryptedPass = $this->reencryptPass($cryptoHashOld, $cryptoHashNew, $srcEncryptedPass);
            $database->query("update isys_ldap set isys_ldap__password = '" . $newEncryptedPass . "' where isys_ldap__id = " . $row['isys_ldap__id']);
        }
        $output->writeln('Passwords in table isys_ldap have been successfully updated with new crypto hash');

        $rows = $database->query('select isys_catg_password_list__id, isys_catg_password_list__password from isys_catg_password_list');
        while ($row = $rows->fetch_assoc()) {
            $srcEncryptedPass = $row['isys_catg_password_list__password'];
            $newEncryptedPass = $this->reencryptPass($cryptoHashOld, $cryptoHashNew, $srcEncryptedPass);
            $database->query("update isys_catg_password_list set isys_catg_password_list__password = '" . $newEncryptedPass . "' where isys_catg_password_list__id = " . $row['isys_catg_password_list__id']);
        }
        $output->writeln('Passwords in table isys_catg_password_list have been successfully updated with new crypto hash');

        $rows = $database->query('select isys_jdisc_db__id, isys_jdisc_db__password, isys_jdisc_db__discovery_password from isys_jdisc_db');
        while ($row = $rows->fetch_assoc()) {
            $srcEncryptedPass = $row['isys_jdisc_db__password'];
            $newEncryptedPass = $this->reencryptPass($cryptoHashOld, $cryptoHashNew, $srcEncryptedPass);
            $newEncryptedDiscoveryPass = $this->reencryptPass($cryptoHashOld, $cryptoHashNew, $row['isys_jdisc_db__discovery_password']);
            $database->query("update isys_jdisc_db set isys_jdisc_db__password = '" . $newEncryptedPass . "', isys_jdisc_db__discovery_password = '" . $newEncryptedDiscoveryPass . "' where isys_jdisc_db__id = " . $row['isys_jdisc_db__id']);
        }
        $output->writeln('Passwords in table isys_jdisc_db have been successfully updated with new crypto hash');

        $rows = $database->query('select isys_monitoring_hosts__id, isys_monitoring_hosts__password from isys_monitoring_hosts');
        while ($row = $rows->fetch_assoc()) {
            $srcEncryptedPass = $row['isys_monitoring_hosts__password'];
            $newEncryptedPass = $this->reencryptPass($cryptoHashOld, $cryptoHashNew, $srcEncryptedPass);
            $database->query("update isys_monitoring_hosts set isys_monitoring_hosts__password = '" . $newEncryptedPass . "' where isys_monitoring_hosts__id = " . $row['isys_monitoring_hosts__id']);
        }
        $output->writeln('Passwords in table isys_monitoring_hosts have been successfully updated with new crypto hash');

        $this->newCryptohashSave($cryptoHashNew);

        $output->writeln('New crypto hash has been successfully updated');
        return Command::SUCCESS;
    }

    /**
     * @param string $cryptoHashOld
     * @param string $cryptoHashNew
     * @param string $srcEncryptedPassword
     *
     * @return string
     */
    private function reencryptPass(string $cryptoHashOld, string $cryptoHashNew, string $srcEncryptedPassword) : string
    {
        global $g_crypto_hash;

        $g_crypto_hash = $cryptoHashOld;
        $plainPass = \isys_helper_crypt::decrypt($srcEncryptedPassword);
        $g_crypto_hash = $cryptoHashNew;
        $newEncryptedPass = \isys_helper_crypt::encrypt($plainPass);

        return $newEncryptedPass;
    }

    /**
     * Method for updating crypto hash in the config file
     * @param string $newCryptoHash
     */
    private function newCryptohashSave(string $newCryptoHash) : void
    {
        global $g_absdir;
        global $g_crypto_hash;

        $g_crypto_hash = $newCryptoHash;

        $configDir = $g_absdir . '/src';
        $updater = new isys_update_config();
        $updater->backup($configDir);

        $templateDir = $g_absdir . '/setup';
        $config = $updater->parseAndUpdateConfig(
            $templateDir,
            [
                'config.crypt.hash' => $newCryptoHash,
            ]
        );
        $updater->write('<' . substr($config, 1), $configDir);
    }
}

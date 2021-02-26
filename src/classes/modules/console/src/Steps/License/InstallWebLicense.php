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

namespace idoit\Module\Console\Steps\License;

use idoit\Module\Console\Steps\Message\ErrorLevel;
use idoit\Module\Console\Steps\Message\Messages;
use idoit\Module\Console\Steps\Message\StepMessage;
use idoit\Module\Console\Steps\Step;
use idoit\Module\License\Exception\LicenseExistsException;
use idoit\Module\License\Exception\LicenseInvalidException;
use idoit\Module\License\LicenseServiceFactory;
use isys_component_database;

class InstallWebLicense implements Step
{
    private $dbName;

    private $host;

    private $licenseServer;

    private $licenseToken;

    private $password;

    private $port;

    private $user;

    public function __construct(
        $host,
        $user,
        $password,
        $dbName,
        $port,
        $licenseServer,
        $licenseToken
    ) {
        $this->licenseServer = $licenseServer;
        $this->licenseToken = $licenseToken;

        $this->host = $host;
        $this->user = $user;
        $this->password = $password;
        $this->dbName = $dbName;
        $this->port = $port;
    }

    /**
     * Get name of the step
     *
     * @return string
     */
    public function getName()
    {
        return 'Install web license token';
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
        $db = isys_component_database::factory('mysql', $this->host, $this->port, $this->user, $this->password, $this->dbName);
        $licenseService = LicenseServiceFactory::createDefaultLicenseService($db, $this->licenseToken);
        if ($this->licenseServer) {
            $licenseService->setLicenseServer($this->licenseServer);
        }

        try {
            $licensesString = $licenseService->getLicensesFromServer();
            $messages->addMessage(new StepMessage($this, 'Loaded license string from the server', ErrorLevel::DEBUG));
            foreach ($licenseService->parseEncryptedLicenses($licensesString) as $license) {
                try {
                    $messages->addMessage(new StepMessage($this, 'Install license ' . $license->getProductIdentifier(), ErrorLevel::INFO));
                    $licenseService->installLicense($license);
                } catch (LicenseExistsException $e) {
                    $messages->addMessage(new StepMessage($this, 'License is already installed ' . $license->getProductIdentifier(), ErrorLevel::NOTIFICATION));
                } catch (LicenseInvalidException $e) {
                    $messages->addMessage(new StepMessage($this, 'Invalid license ' . $license->getProductIdentifier(), ErrorLevel::NOTIFICATION));
                }
            }
        } catch (\Exception $exception) {
            $messages->addMessage(new StepMessage($this, $exception->getMessage(), ErrorLevel::ERROR));

            return false;
        }

        return true;
    }
}

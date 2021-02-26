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
use idoit\Module\License\Event\License\LegacyLicenseRemovedEvent;
use idoit\Module\License\LicenseService;
use isys_component_database;
use isys_module_licence;

class RemoveLicense implements Step
{
    /**
     * @var isys_component_database
     */
    private $database;

    private $licenseId;

    /**
     * @var LicenseService
     */
    private $licenseService;

    public function __construct(isys_component_database $database, $licenseId, LicenseService $licenseService)
    {
        $this->database = $database;
        $this->licenseId = $licenseId;
        $this->licenseService = $licenseService;
    }

    /**
     * Get name of the step
     *
     * @return string
     */
    public function getName()
    {
        return 'Remove license id: ' . $this->licenseId;
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
        $licenseModule = new isys_module_licence();
        $license = $licenseModule->get_licence($this->database, $this->licenseId)->get_row();
        if ($license === null) {
            $messages->addMessage(new StepMessage($this, 'Cannot find license with id ' . $this->licenseId, ErrorLevel::ERROR));
            return false;
        }

        $removedChildren = $licenseModule->deleteLicenceByParentLicence($this->database, $this->licenseId);
        if ($removedChildren === false) {
            $messages->addMessage(new StepMessage($this, 'No child licenses of ' . $this->licenseId . ' were removed', ErrorLevel::DEBUG));
        } elseif (is_int($removedChildren) && $removedChildren > 0) {
            $messages->addMessage(new StepMessage($this, "Removed {$removedChildren} children licenses of {$this->licenseId}", ErrorLevel::INFO));
        }
        $removed = $licenseModule->delete_licence($this->database, $this->licenseId);
        if ($removed) {
            $messages->addMessage(new StepMessage($this, "License {$this->licenseId} is removed", ErrorLevel::INFO));

            try {
                $this->licenseService->getEventDispatcher()->dispatch(
                    new LegacyLicenseRemovedEvent(),
                    LegacyLicenseRemovedEvent::NAME
                );
            } catch (\Exception $exception) {
                $messages->addMessage(new StepMessage($this, $exception->getMessage(), ErrorLevel::NOTIFICATION));
            }
            return $removed;
        }
        $messages->addMessage(new StepMessage($this, "License {$this->licenseId} is not removed", ErrorLevel::ERROR));
        return false;
    }
}

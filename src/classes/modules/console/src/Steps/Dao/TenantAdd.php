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

namespace idoit\Module\Console\Steps\Dao;

use idoit\Module\Console\Steps\Message\ErrorLevel;
use idoit\Module\Console\Steps\Message\Messages;
use idoit\Module\Console\Steps\Message\StepMessage;
use idoit\Module\Console\Steps\Step;
use idoit\Module\Console\Steps\Undoable;
use idoit\Module\License\Event\Tenant\TenantBeforeAddedEvent;
use idoit\Module\License\LicenseService;
use isys_component_dao_mandator;
use isys_component_database;

class TenantAdd implements Step, Undoable
{
    /**
     * @var isys_component_dao_mandator
     */
    private $dao;

    private $description;

    private $host;

    private $dbName;

    /**
     * @var LicenseService
     */
    private $licenseService;

    private $password;

    private $port;

    private $title;

    private $user;

    private $id;

    public function __construct(
        isys_component_database $database,
        LicenseService $licenseService,
        $title,
        $description,
        $host,
        $port,
        $user,
        $password,
        $dbName
    ) {
        $this->dao = isys_component_dao_mandator::instance($database);
        $this->licenseService = $licenseService;
        $this->title = $title;
        $this->description = $description;
        $this->host = $host;
        $this->port = $port;
        $this->user = $user;
        $this->password = $password;
        $this->dbName = $dbName;
    }

    /**
     * Get name of the step
     *
     * @return string
     */
    public function getName()
    {
        return 'Add tenant ' . $this->title;
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
        $this->id = null;
        $sort = $this->dao->retrieve("SELECT MAX(isys_mandator__sort) AS sort FROM isys_mandator;")->get_row()['sort'] ?: 0;

        $result = $this->dao->add(
            $this->title,
            $this->description,
            null,
            null,
            $this->host,
            $this->port,
            $this->user,
            $this->password,
            $this->dbName,
            $sort + 1,
            0
        );
        $this->id = $this->dao->get_last_insert_id();
        $messages->addMessage(new StepMessage($this, $result ? "added with id {$this->id}" : 'failed', ErrorLevel::INFO));

        global $g_dirs, $g_absdir;

        if (!mkdir($concurrentDirectory = $g_dirs['fileman']['image_dir'] . $this->id) && !is_dir($concurrentDirectory)) {
            $messages->addMessage(new StepMessage($this, sprintf('Directory "%s" was not created', $concurrentDirectory)), ErrorLevel::NOTIFICATION);
        }

        if (!mkdir($concurrentDirectory = rtrim($g_absdir, '/') . '/imports/' . $this->id) && !is_dir($concurrentDirectory)) {
            $messages->addMessage(new StepMessage($this, sprintf('Directory "%s" was not created', $concurrentDirectory)), ErrorLevel::NOTIFICATION);
        }

        try {
            $this->licenseService->getEventDispatcher()
                ->dispatch(new TenantBeforeAddedEvent(), TenantBeforeAddedEvent::NAME);
        } catch (\Exception $ex) {
            $messages->addMessage(new StepMessage($this, $ex->getMessage(), ErrorLevel::NOTIFICATION));
        }

        return $result;
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
        if (!$this->id) {
            return true;
        }
        $result = $this->dao->delete($this->id);
        $messages->addMessage(new StepMessage($this, $result ? "removed {$this->id}" : 'removing failed', ErrorLevel::INFO));

        global $g_dirs, $g_absdir;

        unlink($g_dirs['fileman']['image_dir'] . $this->id);
        unlink(rtrim($g_absdir, '/') . '/imports/' . $this->id);

        $this->id = null;

        return $result;
    }
}

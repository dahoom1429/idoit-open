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

namespace idoit\Module\Console\Steps\Addon;

use idoit\Module\Console\Steps\FileSystem\FileExistsCheck;
use idoit\Module\Console\Steps\Message\ErrorLevel;
use idoit\Module\Console\Steps\Message\Messages;
use idoit\Module\Console\Steps\Step;
use idoit\Module\Console\Steps\Undoable;
use isys_component_database;
use isys_update_xml;

class Updater implements Step, Undoable
{
    /**
     * @var isys_component_database
     */
    private $db;

    private $path;

    public function __construct($path, isys_component_database $db)
    {
        $this->path = $path;
        $this->db = $db;
    }

    /**
     * Get name of the step
     *
     * @return string
     */
    public function getName()
    {
        return 'Update ' . $this->db->get_db_name() . ' from ' . $this->path;
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
        if (!(new FileExistsCheck($this->path, true, ErrorLevel::DEBUG))->process($messages)) {
            return true;
        }
        $updater = new isys_update_xml();
        $updater->update_database($this->path, $this->db);

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

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

namespace idoit\Module\Console\Steps;

use idoit\Module\Console\Steps\Message\ErrorLevel;
use idoit\Module\Console\Steps\Message\Messages;
use idoit\Module\Console\Steps\Message\StepMessage;

class TemplateFile implements Step, Undoable
{
    private $destination;

    private $name;

    /**
     * @var array
     */
    private $params;

    private $template;

    public function __construct($name, $template, $destination, array $params)
    {
        $this->name = $name;
        $this->template = $template;
        $this->destination = $destination;
        $this->params = $params;
    }

    /**
     * Get name of the step
     *
     * @return string
     */
    public function getName()
    {
        return 'Template: ' . $this->name;
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
        $configTemplate = @file_get_contents($this->template);
        foreach ($this->params as $k => $v) {
            $escapedValue = addslashes($v);
            $configTemplate = str_replace($k, $escapedValue, $configTemplate);
        }
        if (is_writable(dirname($this->destination))) {
            if (@isys_file_put_contents($this->destination, $configTemplate)) {
                return true;
            }
        }
        $messages->addMessage(new StepMessage($this, 'Cannot save ' . $this->name, ErrorLevel::FATAL));
        return false;
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
        if (file_exists($this->destination)) {
            unlink($this->destination);
        }
        return true;
    }
}

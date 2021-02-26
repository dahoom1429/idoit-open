<?php

namespace idoit\Module\Console\Steps\Addon;

use idoit\Module\Console\Steps\Message\ErrorLevel;
use idoit\Module\Console\Steps\Message\Messages;
use idoit\Module\Console\Steps\Message\StepMessage;
use idoit\Module\Console\Steps\Step;

class ExtractAddonIdentifierFromPackage implements Step
{
    /**
     * @var callable
     */
    private $callback;

    /**
     * @var string
     */
    private $path;

    /**
     * InstallAddonByPackage constructor.
     *
     * @param string   $path
     * @param callable $callback - callback to be called with the extracted identifier
     */
    public function __construct(string $path, callable $callback)
    {
        $this->path = $path;
        $this->callback = $callback;
    }

    /**
     * Get name of the step
     *
     * @return string
     */
    public function getName()
    {
        return 'Search add-on in ' . $this->path;
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
        $package = json_decode(file_get_contents($this->path), true);

        if (!is_array($package) || ($package['type'] ?? '') !== 'addon' || !isset($package['identifier'])) {
            $messages->addMessage(new StepMessage($this, 'package file does not contain the valid package information', ErrorLevel::ERROR));

            return false;
        }

        $identifier = $package['identifier'];

        return call_user_func($this->callback, $identifier, $messages);
    }
}

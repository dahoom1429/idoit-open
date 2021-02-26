<?php

namespace idoit\Module\Console\Steps\Update;

use idoit\Module\Console\Steps\Message\Messages;
use idoit\Module\Console\Steps\Step;
use isys_component_signalcollection;

/**
 * Class EmitSignalStep
 */
class EmitSignalStep implements Step
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $signal;

    /**
     * EmitSignalStep constructor.
     *
     * @param string $name
     * @param string $signal
     */
    public function __construct(string $name, string $signal)
    {
        $this->name = $name;
        $this->signal = $signal;
    }

    /**
     * Get name of the step
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
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
        isys_component_signalcollection::get_instance()
            ->emit($this->signal);

        return true;
    }
}

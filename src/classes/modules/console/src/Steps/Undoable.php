<?php

namespace idoit\Module\Console\Steps;

use idoit\Module\Console\Steps\Message\Messages;

interface Undoable
{
    /**
     * Undo the work
     *
     * @param Messages $messages
     *
     * @return mixed
     */
    public function undo(Messages $messages);
}

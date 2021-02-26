<?php

namespace idoit\AddOn;

/**
 * i-doit Basic modules interface
 *
 * @package     idoit\AddOn
 * @author      atsapko
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
interface AddonInterface
{
    /**
     * Signal Slot initialization.
     *
     * @return $this
     */
    public function initSlots();

    /**
     * Default start method.
     *
     * @return $this
     */
    public function start();
}

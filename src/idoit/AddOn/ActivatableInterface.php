<?php

namespace idoit\AddOn;

/**
 * i-doit Module interface for activation
 *
 * @package     idoit\AddOn
 * @author      atsapko
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
interface ActivatableInterface
{
    /**
     * Checks if a add-on is active.
     *
     * @return integer|bool
     */
    public static function isActive();

    /**
     * Method that is called after clicking "activate" in admin center for specific mandator.
     *
     * @param \isys_component_database $tenantDatabase
     *
     * @return boolean
     * @author atsapko
     */
    public static function activate($tenantDatabase);

    /**
     * Method that is called after clicking "deactivate" in admin center for specific mandator.
     *
     * @param \isys_component_database $tenantDatabase
     *
     * @return boolean
     * @author atsapko
     */
    public static function deactivate($tenantDatabase);
}

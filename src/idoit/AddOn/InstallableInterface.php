<?php

namespace idoit\AddOn;

/**
 * i-doit Module interface for installation
 *
 * @package     idoit\AddOn
 * @author      atsapko
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
interface InstallableInterface
{
    /**
     * Checks if a add-on is installed.
     *
     * @return int|bool
     */
    public static function isInstalled();

    /**
     * Basic installation process for all mandators.
     *
     * @param  \isys_component_database $tenantDatabase
     * @param  \isys_component_database $systemDatabase
     * @param  integer                  $moduleId
     * @param  string                   $type
     * @param  integer                  $tenantId
     *
     * @since  i-doit 1.12
     * @return boolean
     */
    public static function install($tenantDatabase, $systemDatabase, $moduleId, $type, $tenantId);

    /**
     * Uninstall add-on for all mandators.
     *
     * @param \isys_component_database $tenantDatabase
     *
     * @return boolean
     */
    public static function uninstall($tenantDatabase);
}

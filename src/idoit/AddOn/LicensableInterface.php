<?php

namespace idoit\AddOn;

/**
 * i-doit Module interface for licenses
 *
 * @package     idoit\AddOn
 * @author      atsapko
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
interface LicensableInterface
{
    /**
     * Checks if a module is licenced
     *
     * @return  boolean
     */
    public static function isLicensed();

    /**
     * Set licence status.
     *
     * @param  boolean $isLicensed
     */
    public static function setLicensed($isLicensed);
}

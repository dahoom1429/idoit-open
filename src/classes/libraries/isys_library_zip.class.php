<?php

/**
 * i-doit
 *
 * Wrapper for ZipArchive
 *
 * @package     i-doit
 * @subpackage  Libraries
 * @author      Benjamin Heisig <bheisig@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_library_zip extends ZipArchive
{
    /**
     * Gets the localized status error message, system and/or zip messages.
     *
     * @link    http://php.net/manual/en/ziparchive.getstatusstring.php
     * @return  string  A string with the status message on success or false on failure.
     */
    public function getStatusString()
    {
        return isys_application::instance()->container->get('language')
            ->get(parent::getStatusString());
    }
}

<?php

namespace idoit\AddOn;

/**
 * i-doit Module interface for authorization.
 *
 * @package     idoit\AddOn
 * @author      Leonard Fischer <lfischer@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
interface AuthableInterface
{
    /**
     * Get related auth class for module.
     *
     * @return \isys_auth
     */
    public static function getAuth();
}

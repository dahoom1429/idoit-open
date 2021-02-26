<?php

namespace idoit\Module\Search\Index\Data\Source;

use idoit\Module\Search\Index\Document;

/**
 * i-doit
 *
 * DynamicSource
 *
 * @package     i-doit
 * @subpackage  Search
 * @author      Kevin Mauel <kmauel@i-doit.com>
 * @version     1.12
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
interface DynamicSource
{
    /**
     *
     * @param string $identifier
     * @return void
     */
    public function setIdentifier($identifier);
}

<?php

namespace idoit\Module\Cmdb\Model\Matcher\Protocol;

use idoit\Module\Cmdb\Model\Matcher\Match;

/**
 * i-doit
 *
 * Ci Models
 *
 * @package     i-doit
 * @subpackage  Cmdb
 * @author      Van Quyen Hoang <qhoang@i-doit.com>
 * @version     1.8
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
interface Retrievable
{

    /**
     * @param int   $objID
     * @param array $matchKeywords
     *
     * @return Match|null
     */
    public function dataRetrieve($objID, array $matchKeywords);

}
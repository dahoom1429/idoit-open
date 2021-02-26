<?php

namespace idoit\Legacy;

use idoit\Component\Provider\DiFactory;

/**
 * i-doit
 *
 * Provides methods for enabling backward compatibility
 *
 * @package     i-doit
 * @subpackage  Core
 * @author      Dennis Stücken <dstuecken@synetics.de>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class BackwardCompatibility
{
    use DiFactory;

    /**
     * Preserve backward compatibility (e.g. for modules)
     */
    public function preserve()
    {
        global $g_comp_signals;
        $g_comp_signals = $this->getDi()->signals;
    }

}
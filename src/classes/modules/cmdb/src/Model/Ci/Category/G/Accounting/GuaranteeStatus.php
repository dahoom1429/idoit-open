<?php

namespace idoit\Module\Cmdb\Model\Ci\Category\G\Accounting;

use idoit\Module\Cmdb\Model\Ci\Category\DynamicCallbackInterface;

/**
 * i-doit
 *
 * Accounting Category "GuaranteeStatus" callback.
 *
 * @package     i-doit
 * @subpackage  Cmdb
 * @author      Kevin Mauel<kmauel@i-doit.com>
 * @version     1.9.2
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class GuaranteeStatus implements DynamicCallbackInterface
{
    /**
     * Render method.
     *
     * @param string $data
     * @param mixed  $extra
     *
     * @return mixed
     */
    public static function render($data, $extra = null)
    {
        if ($data === null) {
            return '';
        }

        [$relevantDate, $p_guarantee_period, $p_guarantee_period_unit] = explode(',', $data);

        $dao = \isys_cmdb_dao_category_g_accounting::instance(\isys_application::instance()->container->database);
        $result = $dao->calculate_guarantee_status(strtotime($relevantDate), $p_guarantee_period, $p_guarantee_period_unit);

        return $result;
    }
}

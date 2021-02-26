<?php

/**
 * i-doit
 *
 * CMDB global category for net zones.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Leonard Fischer <lfischer@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 * @since       1.8.1
 */
class isys_cmdb_dao_category_g_net_zone extends isys_cmdb_dao_category_g_net_zone_scopes
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'net_zone';

    /**
     * Category's template.
     *
     * @var  string
     */
    protected $m_tpl = 'catg__net_zone_scopes.tpl';
}

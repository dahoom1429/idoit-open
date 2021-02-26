<?php

/**
 * i-doit
 *
 * DAO: global category for JDisc custom attributes.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Van Quyen Hoang <qhoang@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_category_g_jdisc_discovery extends isys_cmdb_dao_category_g_virtual
{
    const C__JDISC_DISCOVERY__TARGET_TYPE__IP = 'ip';
    const C__JDISC_DISCOVERY__TARGET_TYPE__FQDN = 'fqdn';

    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'jdisc_discovery';

    /**
     * Category entry is purgable
     *
     * @var  boolean
     */
    protected $m_is_purgable = false;

    /**
     * Is category multi-valued or single-valued?
     *
     * @var  boolean
     */
    protected $m_multivalued = false;

    /**
     * Method for returning the properties.
     *
     * @return  array
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    protected function properties()
    {
        return [];
    }
}

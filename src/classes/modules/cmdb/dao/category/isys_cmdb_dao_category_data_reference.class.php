<?php

/**
 * i-doit category data reference
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Dennis Stücken <dstuecken@i-doit.de>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_category_data_reference extends isys_cmdb_dao_category_data_value
{
    /**
     * Category reference id
     *
     * @var int
     */
    public $m_id = null;

    /**
     * @param       $p_value
     * @param array $p_data
     */
    public function __construct($p_value, $p_id, $p_data = [])
    {
        $this->m_value = $p_value;
        $this->m_id = $p_id;

        if (self::$m_store_data) {
            if (isset($p_data['id'])) {
                unset($p_data['id']);
            }
            if (isset($p_data['title'])) {
                unset($p_data['title']);
            }

            $this->m_data = $p_data;
        }
    }
}

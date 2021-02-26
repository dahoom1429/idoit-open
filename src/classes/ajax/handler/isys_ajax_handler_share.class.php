<?php

/**
 * AJAX
 *
 * @package     i-doit
 * @subpackage  General
 * @author      Leonard Fischer <lfischer@i-doit.org>
 * @version     1.0
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 * @since       1.3.2
 */
class isys_ajax_handler_share extends isys_ajax_handler
{
    /**
     * Init method, which gets called from the framework.
     *
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function init()
    {
        // We set the header information because we don't accept anything than JSON.
        header('Content-Type: application/json');

        $l_return = [
            'success' => true,
            'data'    => null,
            'message' => null
        ];

        try {
            switch ($_GET['func']) {
                case 'get_shares_by_obj':
                    $l_return['data'] = $this->get_shares_by_obj($_POST[C__CMDB__GET__OBJECT]);
                    break;
            }
        } catch (Exception $e) {
            $l_return['success'] = false;
            $l_return['message'] = $e->getMessage();
        }

        echo isys_format_json::encode($l_return);

        $this->_die();
    }

    /**
     * This method defines, if the hypergate needs to be included for this request.
     *
     * @param   integer $p_obj_id
     *
     * @return  array
     */
    public function get_shares_by_obj($p_obj_id)
    {
        $l_arr = [];
        $l_res = isys_cmdb_dao_category_g_shares::instance($this->m_database_component)
            ->get_data(null, $p_obj_id);

        if (is_countable($l_res) && count($l_res) > 0) {
            while ($l_row = $l_res->get_row()) {
                $l_arr[] = [
                    'id'  => $l_row['isys_catg_shares_list__id'],
                    'val' => $l_row['isys_catg_shares_list__title']
                ];
            }
        }

        return $l_arr;
    }
}
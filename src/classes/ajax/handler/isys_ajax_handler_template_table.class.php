<?php

/**
 * AJAX
 *
 * @package     i-doit
 * @subpackage  General
 * @author      Dennis Stücken <dstuecken@synetics.de>
 * @version     1.0
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_ajax_handler_template_table extends isys_ajax_handler
{
    /**
     * Method for initializing the AJAX request.
     */
    public function init()
    {
        global $g_comp_database;
        $errors = [];
        if (isset($_POST[C__GET__ID]) && is_array($_POST[C__GET__ID])) {
            $l_dao_cmdb = new isys_cmdb_dao($g_comp_database);

            foreach ($_POST[C__GET__ID] as $l_object_id) {
                try {
                    $l_dao_cmdb->rank_records([$l_object_id], C__CMDB__RANK__DIRECTION_DELETE, 'isys_obj', null, true);
                } catch (\Exception $e) {
                    $errors[] = $e->getMessage();
                }
            }
        }

        $l_template = new isys_module_templates();
        $l_template->set_m_rec_status($_POST['type']);

        if (($l_tpl_list = $l_template->get_template_list())) {
            echo $l_tpl_list;
        } else {
            echo "<p class=\"p10\">" . isys_application::instance()->container->get('language')
                    ->get('LC__MODULE__TEMPLATES__NO_TEMPLATES') . ".</p>";
        }
        if (count($errors)) {
            echo '<script type="text/javascript">';
            foreach ($errors as $error) {
                echo 'idoit.Notify.error("' . $error . '");';
            }
            echo '</script>';
        }

        $this->_die();
    }
}

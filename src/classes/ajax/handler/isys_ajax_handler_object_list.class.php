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
class isys_ajax_handler_object_list extends isys_ajax_handler
{
    /**
     * Init method for this request.
     */
    public function init()
    {
        // For backwards compatibility we use this.
        if (!$_GET['func']) {
            // Display the template.
            isys_application::instance()->template->display("file:" . $this->m_smarty_dir . "templates/content/main_groups.tpl");

            // End the request.
            $this->_die();
        }

        switch ($_GET['func']) {
            default:
            case 'load_objtype_list':
                echo $this->load_objtype_list();
                break;

            case 'save_filter':
                $this->save_filter($_POST['field'], $_POST['value'], $_GET[C__CMDB__GET__OBJECTTYPE]);
                break;
        }

        $this->_die();
    }

    /**
     * Define, if this ajax request needs the hypergate logic.
     *
     * @static
     * @return  boolean
     */
    public static function needs_hypergate()
    {
        return true;
    }

    /**
     * Loads further pages for the new list component.
     *
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    protected function load_objtype_list()
    {
        header('Content-type: application/json');

        $l_dao = $_GET['dao'];

        if (class_exists($l_dao)) {
            return isys_factory::get_instance($l_dao, $this->m_database_component)
                ->set_object_type((int)$_GET['object_type'])
                ->get_list_data((int)$_POST['offset_block']);
        }

        return '';
    }

    /**
     * Method for saving a filter.
     *
     * @param   string  $p_field
     * @param   string  $p_value
     * @param   integer $p_obj_type_id
     *
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    protected function save_filter($p_field, $p_value = '', $p_obj_type_id = null)
    {
        if ($p_obj_type_id !== null) {
            $_SESSION['object-list-filter']['obj-type-' . $p_obj_type_id]['timestamp'] = time();
            $_SESSION['object-list-filter']['obj-type-' . $p_obj_type_id]['field'] = $p_field;
            $_SESSION['object-list-filter']['obj-type-' . $p_obj_type_id]['value'] = $p_value;

            if (empty($p_value) || empty($p_field)) {
                unset($_SESSION['object-list-filter']['obj-type-' . $p_obj_type_id]);
            }
        }
    }
}